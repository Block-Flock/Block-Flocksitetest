<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

$app->checkAuth(true);

// ============================================================================
// SECURITY & INITIALIZATION
// ============================================================================

// Validate and sanitize parameters
$limit = 8;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) 
    ? max(1, min(intval($_GET['page']), 100)) 
    : 1;

$errors = [];

// Ensure CSRF token exists (defensive)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================================
// STATUS POSTING LOGIC
// ============================================================================

if (!empty($AUTH) && isset($_POST['status'])) {
    // CSRF Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $check = new Check();
        $status = trim($_POST['status']);
        $status = $check->obfuscateIfProfane($status);
        
        // Validate status
        if (strlen(str_replace(' ', '', $status)) < 3) {
            $errors[] = 'Your status cannot be less than 3 characters long.';
        }
        
        if (strlen($status) > 255) {
            $errors[] = 'Your status cannot be longer than 255 characters.';
        } elseif ($my->flood >= time()) {
            $errors[] = 'Please wait another ' . ($my->flood - time()) . ' seconds before posting.';
        } elseif ($my->verified == 0) {
            $errors[] = 'Please verify your account to post a status.';
        }
        
        // Spam check
        $recentPosts = $db->prepare('SELECT COUNT(*) FROM user_statuses WHERE user = ? AND date > ?');
        $recentPosts->execute([$my->id, time() - 3600]);
        if ($recentPosts->fetchColumn() > 20) {
            $errors[] = 'You have posted too many status updates recently. Please wait a while.';
        }
        
        if (empty($errors)) {
            try {
                $db->beginTransaction();
                
                $add = $db->prepare('INSERT INTO user_statuses (`user`, `status`, `date`) VALUES (?, ?, ?)');
                $add->execute([$my->id, $status, time()]);
                $statusId = $db->lastInsertId();
                
                $update = $db->prepare('UPDATE users SET `status` = ? WHERE id = ?');
                $update->execute([$status, $my->id]);
                
                $flood = $db->prepare('UPDATE users SET flood = ? WHERE id = ?');
                $flood->execute([time() + 30, $my->id]);
                
                $db->commit();
                
                header('Location: /dashboard?page=1&posted=' . $statusId);
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Status post error: " . $e->getMessage());
                $errors[] = 'An error occurred while posting your status. Please try again.';
            }
        }
    }
}

// ============================================================================
// ADMIN SCRUB FUNCTIONALITY
// ============================================================================

if (isset($_POST['scrub']) && !empty($AUTH) && isset($my->administrator) && $my->administrator >= 1) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Invalid security token.';
    } else {
        $status_id = isset($_POST['status_id']) ? intval($_POST['status_id']) : 0;
        $target_user_id = isset($_POST['target_user_id']) ? intval($_POST['target_user_id']) : 0;
        $reason = substr(trim($_POST['scrub_reason'] ?? ''), 0, 255);
        
        if ($status_id > 0 && $target_user_id > 0) {
            try {
                $db->beginTransaction();
                
                $scrub = $db->prepare('UPDATE user_statuses SET `status` = ? WHERE id = ?');
                $scrub->execute(['( Content deleted )', $status_id]);
                
                $log = $db->prepare('INSERT INTO user_history (`admin`, `user`, `action`, `details`, `timemoderated`) VALUES (?, ?, ?, ?, ?)');
                $log->execute([
                    $my->id, 
                    $target_user_id, 
                    'Scrubbed Status', 
                    'Status ID: ' . $status_id . ($reason ? ' | Reason: ' . $reason : ''),
                    time()
                ]);
                
                $db->commit();
                
                header('Location: /dashboard?page=' . $page . '&scrubbed=' . $status_id);
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Scrub error: " . $e->getMessage());
                $errors[] = 'An error occurred while scrubbing the status.';
            }
        } else {
            $errors[] = 'Invalid status selected for scrubbing.';
        }
    }
}

// ============================================================================
// FETCH DATA
// ============================================================================

// Pagination
$offset = max(0, ($page - 1) * $limit);

$countStatuses = $db->prepare('SELECT COUNT(*) as total FROM user_statuses WHERE status != ?');
$countStatuses->execute(['( Content deleted )']);
$totalStatuses = (int)$countStatuses->fetch(PDO::FETCH_OBJ)->total;
$pages = max(1, ceil($totalStatuses / $limit));

// Fetch statuses
$selectStatuses = $db->prepare('
    SELECT 
        us.*, 
        u.id as user_id, 
        u.username, 
        u.expire, 
        u.avatar_hash,
        u.verified
    FROM user_statuses us 
    JOIN users u ON us.user = u.id 
    WHERE us.status != ?
    ORDER BY us.id DESC 
    LIMIT ? OFFSET ?
');
$selectStatuses->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
$selectStatuses->bindValue(2, $limit, PDO::PARAM_INT);
$selectStatuses->bindValue(3, $offset, PDO::PARAM_INT);
$selectStatuses->execute();

$statusesData = $selectStatuses->fetchAll(PDO::FETCH_OBJ);

// Fetch friends
$selectFriends = $db->prepare('
    SELECT 
        u.id as userid, 
        u.username, 
        u.expire, 
        u.avatar_hash, 
        u.verified
    FROM (
        SELECT receiver as friend_id FROM user_friends WHERE status = 1 AND sender = ?
        UNION
        SELECT sender as friend_id FROM user_friends WHERE status = 1 AND receiver = ?
    ) AS combined
    JOIN users u ON combined.friend_id = u.id
    ORDER BY u.username
    LIMIT 8
');
$selectFriends->execute([$my->id, $my->id]);
$friends = $selectFriends->fetchAll(PDO::FETCH_OBJ);

// Fetch notifications
$unreadNotifications = 0;
try {
    $notifStmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $notifStmt->execute([$my->id]);
    $unreadNotifications = $notifStmt->fetchColumn();
} catch (Exception $e) {}

// ============================================================================
// AVATAR DATA BATCH FETCH - ENHANCED FOR FACE RENDERING
// ============================================================================

$allUserIds = [(int)$my->id];
foreach ($friends as $friend) {
    $allUserIds[] = (int)$friend->userid;
}
foreach ($statusesData as $status) {
    $allUserIds[] = (int)$status->user_id;
}
$allUserIds = array_unique($allUserIds);
$allUserIds = array_map('intval', array_values($allUserIds));

$userColors = [];
$userWearables = [];

if (!empty($allUserIds)) {
    $placeholders = str_repeat('?,', count($allUserIds) - 1) . '?';
    
    // Fetch colors
    $colorStmt = $db->prepare("SELECT * FROM user_colors WHERE user IN ($placeholders)");
    $colorStmt->execute($allUserIds);
    while ($color = $colorStmt->fetch(PDO::FETCH_OBJ)) {
        $userColors[(int)$color->user] = $color;
    }

    // Fetch wearables including face
    $wearableStmt = $db->prepare("SELECT * FROM user_data WHERE user IN ($placeholders)");
    $wearableStmt->execute($allUserIds);
    while ($wearable = $wearableStmt->fetch(PDO::FETCH_OBJ)) {
        $userWearables[(int)$wearable->user] = $wearable;
    }
}

// HEADER INCLUSION
$title = "Dashboard";

/*
 * Extra head content: prefer local vendor assets if present,
 * else fall back to CDN. This reduces loader race conditions.
 */
$localThree = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/three.min.js';
$localGLTF = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/GLTFLoader.js';
$localOBJ = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/OBJLoader.js';

$extra_head_content = '';

// Prefer local three.min.js when it exists
if (file_exists($localThree)) {
    $extra_head_content .= '<script src="/assets/vendor/three.min.js"></script>' . PHP_EOL;
} else {
    // fallback to CDN
    $extra_head_content .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>' . PHP_EOL;
}

// Load loaders if available locally; otherwise use CDN fallbacks.
if (file_exists($localGLTF)) {
    $extra_head_content .= '<script src="/assets/vendor/GLTFLoader.js"></script>' . PHP_EOL;
} else {
    $extra_head_content .= '<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>' . PHP_EOL;
}

if (file_exists($localOBJ)) {
    $extra_head_content .= '<script src="/assets/vendor/OBJLoader.js"></script>' . PHP_EOL;
} else {
    $extra_head_content .= '<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js"></script>' . PHP_EOL;
}

// Small inline diagnostics/fallback to help detect loader issues quickly.
$extra_head_content .= <<<JS
<script>
  (function(){
    function log(s){ try{ console.info('[Dashboard loader] ' + s); } catch(e){} }
    if (window.THREE) {
      log('THREE present, r' + (THREE.REVISION || 'unknown'));
    } else {
      log('THREE not present yet.');
    }
    // Check for loaders - warn if not found so you can debug quickly
    setTimeout(function(){
      log('GLTFLoader ' + (window.THREE && window.THREE.GLTFLoader ? 'OK' : 'MISSING'));
      log('OBJLoader ' + (window.THREE && window.THREE.OBJLoader ? 'OK' : 'MISSING'));
    }, 2000);
  })();
</script>
JS;

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<section class="section dashboard-wrapper">
    <div class="container">
        <?php if (isset($_GET['posted'])): ?>
            <div class="notification is-success is-light"><button class="delete"></button>Status posted successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['scrubbed'])): ?>
            <div class="notification is-warning is-light"><button class="delete"></button>Status has been moderated.</div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="notification is-danger is-light"><button class="delete"></button><?php foreach ($errors as $error): ?><p><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?></div>
        <?php endif; ?>
        
        <div class="columns is-centered">
            <div class="column is-3">
                <div class="novello-title is-size-5 mb-4">Dashboard</div>
                <div class="box has-text-centered">
                    <div class="avatar-container">
                        <?php 
                            $myAvatarData = getAvatarData($my->id, $userColors, $userWearables); 
                            // DEBUG: Log avatar data for troubleshooting
                            error_log("Dashboard DEBUG - User {$my->id} avatar data: " . print_r($myAvatarData, true));
                            
                            // normalize items to ensure JS gets consistent shape (face as string '0' if applicable)
                            if (!isset($myAvatarData['items'])) $myAvatarData['items'] = [];
                            $myAvatarData['items'] = normalize_avatar_items_array($myAvatarData['items']);
                        ?>
                        <!-- Explicitly mark renderer intent: avatar -->
                        <canvas id="profile-avatar-<?php echo (int)$my->id; ?>" 
                                width="280" height="280" 
                                class="avatar-main avatar-canvas" 
                                data-user-id="<?php echo (int)$my->id; ?>" 
                                data-render-type="avatar" 
                                data-colors="<?php echo htmlspecialchars(json_encode($myAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>" 
                                data-items="<?php echo htmlspecialchars(json_encode($myAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>" 
                                data-hash="<?php echo htmlspecialchars($my->avatar_hash ?? 'default'); ?>">
                        </canvas>
                        <div class="avatar-status-indicator <?php echo getActivityStatus($my) === 'online' ? 'avatar-status-online' : 'avatar-status-offline'; ?>"></div>
                    </div>
                    <h3 class="title is-5 mt-4 mb-2"><?php echo htmlspecialchars($my->username); ?></h3>
                    <p class="subtitle is-6 mb-2">
                        <span class="activity-status">
                            <span class="status-dot <?php echo getActivityStatus($my) === 'online' ? 'status-online' : 'status-offline'; ?>"></span>
                            <?php echo getActivityStatus($my) === 'online' ? 'Online now' : getLastOnlineDisplay($my); ?>
                        </span>
                    </p>
                    <p class="subtitle is-6 has-text-grey">Member since <?php echo date('M Y', $my->joined); ?></p>
                </div>
                <button class="button is-info is-fullwidth mb-5" onclick="copyInviteLink()">
                    <span class="icon"><i class="fas fa-user-plus"></i></span>
                    <span>Invite Friends</span>
                </button>
                <div class="novello-title is-size-5 mb-4">Friends (<?php echo count($friends); ?>)</div>
                <div class="box">
                    <?php if (count($friends) > 0): ?>
                        <div class="friends-list">
                            <?php foreach ($friends as $friend): ?>
                                <?php 
                                    $friendAvatarData = getAvatarData($friend->userid, $userColors, $userWearables); 
                                    // DEBUG: Log friend avatar data
                                    error_log("Dashboard DEBUG - Friend {$friend->userid} avatar data: " . print_r($friendAvatarData, true));
                                    
                                    if (!isset($friendAvatarData['items'])) $friendAvatarData['items'] = [];
                                    $friendAvatarData['items'] = normalize_avatar_items_array($friendAvatarData['items']);
                                ?>
                                <div class="media friend-item">
                                    <figure class="media-left">
                                        <div class="avatar-friend-container">
                                            <canvas class="avatar-user avatar-friend" 
                                                    id="profile-avatar-<?php echo (int)$friend->userid; ?>" 
                                                    width="50" height="50" 
                                                    data-user-id="<?php echo (int)$friend->userid; ?>" 
                                                    data-render-type="avatar" 
                                                    data-colors="<?php echo htmlspecialchars(json_encode($friendAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                    data-items="<?php echo htmlspecialchars(json_encode($friendAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                    data-hash="<?php echo htmlspecialchars($friend->avatar_hash ?? 'default'); ?>">
                                            </canvas>
                                            <div class="avatar-status-indicator <?php echo getActivityStatus($friend) === 'online' ? 'avatar-status-online' : 'avatar-status-offline'; ?>"></div>
                                        </div>
                                    </figure>
                                    <div class="media-content">
                                        <p class="is-size-6 mb-1">
                                            <a href="/user/<?php echo (int)$friend->userid; ?>" class="friend-username">
                                                <?php echo htmlspecialchars($friend->username); ?>
                                            </a>
                                        </p>
                                        <p class="friend-status">
                                            <span class="activity-status">
                                                <span class="status-dot <?php echo getActivityStatus($friend) === 'online' ? 'status-online' : 'status-offline'; ?>"></span>
                                                <?php echo getActivityStatus($friend) === 'online' ? 'Online now' : getLastOnlineDisplay($friend); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="has-text-grey has-text-centered">No friends yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="column is-6">
                <div class="novello-title is-size-5 mb-4">Status</div>
                <div class="box">
                    <form method="post" id="statusForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="field">
                            <div class="control">
                                <textarea class="textarea" name="status" rows="3" placeholder="What's up, <?php echo htmlspecialchars($my->username); ?>?" maxlength="255"><?php echo htmlspecialchars($my->status ?? ''); ?></textarea>
                            </div>
                            <p class="help is-pulled-right is-size-7 has-text-grey" id="charCount">0/255</p>
                        </div>
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <button type="submit" class="button is-info">
                                    <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                    <span>Update Status</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="novello-title is-size-5 mb-4">Feed</div>
                <?php if (!empty($statusesData)): ?>
                    <?php foreach ($statusesData as $st): ?>
                        <?php 
                            $statusAvatarData = getAvatarData($st->user_id, $userColors, $userWearables); 
                            // DEBUG: Log status avatar data
                            error_log("Dashboard DEBUG - Status {$st->user_id} avatar data: " . print_r($statusAvatarData, true));
                            
                            if (!isset($statusAvatarData['items'])) $statusAvatarData['items'] = [];
                            $statusAvatarData['items'] = normalize_avatar_items_array($statusAvatarData['items']);
                        ?>
                        <div class="box status-item">
                            <article class="media">
                                <figure class="media-left">
                                    <div class="avatar-status-container">
                                        <canvas class="avatar-user avatar-status" 
                                                id="status-avatar-<?php echo (int)$st->id; ?>-<?php echo (int)$st->user_id; ?>" 
                                                width="50" height="50" 
                                                data-user-id="<?php echo (int)$st->user_id; ?>" 
                                                data-render-type="avatar" 
                                                data-colors="<?php echo htmlspecialchars(json_encode($statusAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                data-items="<?php echo htmlspecialchars(json_encode($statusAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                data-hash="<?php echo htmlspecialchars($st->avatar_hash ?? 'default'); ?>">
                                        </canvas>
                                        <div class="avatar-status-indicator <?php echo getActivityStatus($st) === 'online' ? 'avatar-status-online' : 'avatar-status-offline'; ?>"></div>
                                    </div>
                                </figure>
                                <div class="media-content">
                                    <div class="content">
                                        <p class="mb-1">
                                            <strong>
                                                <a class="username" href="/user/<?php echo (int)$st->user_id; ?>">
                                                    <?php echo htmlspecialchars($st->username); ?>
                                                </a>
                                            </strong>
                                            <span class="friend-status ml-2">
                                                <span class="activity-status">
                                                    <span class="status-dot <?php echo getActivityStatus($st) === 'online' ? 'status-online' : 'status-offline'; ?>"></span>
                                                    <?php echo getActivityStatus($st) === 'online' ? 'Online now' : getLastOnlineDisplay($st); ?>
                                                </span>
                                            </span>
                                        </p>
                                        <p class="has-text-grey-light is-size-7 mb-2"><?php echo timeAgo($st->date); ?></p>
                                        <p class="usercontent"><?php echo nl2br(htmlspecialchars($st->status)); ?></p>
                                    </div>
                                </div>
                                <?php if (!empty($my->administrator) && $my->administrator >= 1): ?>
                                    <div class="media-right">
                                        <div class="dropdown is-hoverable">
                                            <div class="dropdown-trigger">
                                                <button class="button is-small is-white">
                                                    <span class="icon"><i class="fas fa-ellipsis-v"></i></span>
                                                </button>
                                            </div>
                                            <div class="dropdown-menu">
                                                <div class="dropdown-content">
                                                    <form method="post" class="scrub-form">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="target_user_id" value="<?php echo (int)$st->user_id; ?>">
                                                        <input type="hidden" name="status_id" value="<?php echo (int)$st->id; ?>">
                                                        <div class="field">
                                                            <label class="label is-size-7">Scrub Reason</label>
                                                            <div class="control">
                                                                <input class="input is-small" type="text" name="scrub_reason">
                                                            </div>
                                                        </div>
                                                        <button type="submit" name="scrub" class="button is-small is-danger is-fullwidth">Scrub</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </article>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="column is-3">
                <div class="box">
                    <h4 class="title is-6">Quick Stats</h4>
                    <div class="content">
                        <p><strong>Friends:</strong> <?php echo count($friends); ?></p>
                        <p><strong>Statuses:</strong> <?php echo $totalStatuses; ?></p>
                        <p><strong>Member since:</strong> <?php echo date('M Y', $my->joined); ?></p>
                    </div>
                </div>
                <div class="box">
                    <h4 class="title is-6">Quick Actions</h4>
                    <div class="buttons are-small is-flex-direction-column">
                        <a href="/avatar" class="button is-info is-fullwidth">Edit Avatar</a>
                        <a href="/settings" class="button is-light is-fullwidth">Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyInviteLink() {
    const inviteLink = window.location.origin + '/register';
    navigator.clipboard.writeText(inviteLink).then(function() {
        alert('Invite link copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}

// Character counter for status textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="status"]');
    const charCount = document.getElementById('charCount');
    
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length + '/255';
        });
        // Initialize counter
        charCount.textContent = textarea.value.length + '/255';
    }
});
</script>

<!-- The main dashboard avatars script (cache-busted) -->
<script src="/assets/js/dashboard-avatars.js?v=<?php echo time(); ?>" defer></script>

<?php $app->footer(); ?>