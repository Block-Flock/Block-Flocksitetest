<?php
/* ==========================================================================
   INITIALIZATION & CONFIGURATION
   ========================================================================== */
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/mail/extras/htmlfilter.php');

$title = "Profile";

// Define extra head content to include avatar scripts
$extra_head_content = '';

/*
 * Extra head content: prefer local vendor assets if present,
 * else fall back to CDN. This reduces loader race conditions.
 */
$localThree = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/three.min.js';
$localGLTF = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/GLTFLoader.js';
$localOBJ = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/OBJLoader.js';

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
    function log(s){ try{ console.info('[User Profile loader] ' + s); } catch(e){} }
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

// Helper function to sanitize colors
function validateColor($color, $default) {
    return preg_match('/^#[a-f0-9]{6}$/i', $color) ? $color : $default;
}

/* ==========================================================================
   DATA FETCHING & LOGIC
   ========================================================================== */

// 1. Validate User ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /');
    exit;
}
$id = (int)$_GET['id'];

// 2. Fetch User Profile
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$gP = $stmt->fetch(PDO::FETCH_OBJ);

if (!$gP) {
    header('Location: /');
    exit;
}

// 3. Access Controls
$isOwner = ($AUTH && $_SESSION['id'] == $id);
$isAdmin = ($AUTH && isset($my->administrator) && $my->administrator >= 1);

// 4. Lazy Load / Fetch Customization
$stmt = $db->prepare('SELECT * FROM user_profile_customization WHERE user_id = ?');
$stmt->execute([$id]);
$userCustomization = $stmt->fetch(PDO::FETCH_OBJ);

if (!$userCustomization) {
    // Initialize default defaults if row missing
    $defaults = [$id, 'default', '#1a1a2e', '#6c5ce7', '#ffffff', '', '', '', 'Inter'];
    $db->prepare('INSERT INTO user_profile_customization (user_id, theme, background_color, accent_color, text_color, background_image, background_music, custom_css, profile_font) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute($defaults);
    // Re-fetch
    $stmt->execute([$id]);
    $userCustomization = $stmt->fetch(PDO::FETCH_OBJ);
}

// ============================================================================
// FETCH AVATAR DATA (EXACT SAME PATTERN AS DASHBOARD)
// ============================================================================

// Get all user IDs for avatar data (profile user + friends)
$allUserIds = [(int)$id];

// Fetch Friends List
$friendsQuery = "
    (SELECT u.id, u.username, u.avatar_hash, u.expire 
     FROM user_friends f JOIN users u ON f.receiver = u.id 
     WHERE f.status = 1 AND f.sender = ?) 
    UNION ALL 
    (SELECT u.id, u.username, u.avatar_hash, u.expire 
     FROM user_friends f JOIN users u ON f.sender = u.id 
     WHERE f.status = 1 AND f.receiver = ?) 
    LIMIT 12";
$getFriends = $db->prepare($friendsQuery);
$getFriends->execute([$id, $id]);
$friendsList = $getFriends->fetchAll(PDO::FETCH_OBJ);

// Add friend IDs to allUserIds
foreach ($friendsList as $friend) {
    $allUserIds[] = (int)$friend->id;
}
$allUserIds = array_unique($allUserIds);
$allUserIds = array_map('intval', array_values($allUserIds));

// Fetch user colors (BATCH QUERY - SAME AS DASHBOARD)
$userColors = [];
if (!empty($allUserIds)) {
    $placeholders = str_repeat('?,', count($allUserIds) - 1) . '?';
    try {
        $colorStmt = $db->prepare("SELECT * FROM user_colors WHERE user IN ($placeholders)");
        $colorStmt->execute($allUserIds);
        while ($color = $colorStmt->fetch(PDO::FETCH_OBJ)) {
            $userColors[(int)$color->user] = $color;
        }
    } catch (Exception $e) {
        // Table might not exist yet
        error_log("User profile - Error fetching user colors: " . $e->getMessage());
    }
}

// Fetch user wearables (BATCH QUERY - SAME AS DASHBOARD)
$userWearables = [];
if (!empty($allUserIds)) {
    $placeholders = str_repeat('?,', count($allUserIds) - 1) . '?';
    try {
        $wearableStmt = $db->prepare("SELECT * FROM user_data WHERE user IN ($placeholders)");
        $wearableStmt->execute($allUserIds);
        while ($wearable = $wearableStmt->fetch(PDO::FETCH_OBJ)) {
            $userWearables[(int)$wearable->user] = $wearable;
        }
    } catch (Exception $e) {
        // Table might not exist yet
        error_log("User profile - Error fetching user wearables: " . $e->getMessage());
    }
}

// Get avatar data for profile user
$avatarData = getAvatarData($id, $userColors, $userWearables);
// Normalize the items array
if (!isset($avatarData['items'])) $avatarData['items'] = [];
$avatarData['items'] = normalize_avatar_items_array($avatarData['items']);

// Get avatar data for friends
$friendsAvatarData = [];
foreach ($friendsList as $friend) {
    $friendData = getAvatarData($friend->id, $userColors, $userWearables);
    if (!isset($friendData['items'])) $friendData['items'] = [];
    $friendData['items'] = normalize_avatar_items_array($friendData['items']);
    $friendsAvatarData[$friend->id] = $friendData;
}

// ============================================================================
// DEBUG: Log the avatar data to check what we're getting
// ============================================================================
error_log("=== USER.PH AVATAR DATA DEBUG ===");
error_log("User ID: " . $id);
error_log("User Colors for $id: " . print_r($userColors[$id] ?? 'NOT FOUND', true));
error_log("User Wearables for $id: " . print_r($userWearables[$id] ?? 'NOT FOUND', true));
error_log("Avatar Data for $id: " . print_r($avatarData, true));
error_log("Number of friends: " . count($friendsList));

/* ==========================================================================
   FORM HANDLING (POST)
   ========================================================================== */
$message = "";
$messageType = ""; // is-success or is-danger

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $AUTH) {
    
    // CSRF Check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid Security Token');
    }

    // A. Save Customization
    if ($isOwner && isset($_POST['save_customization'])) {
        $theme       = $_POST['theme'] ?? 'default';
        $bgColor     = validateColor($_POST['background_color'] ?? '', '#1a1a2e');
        $accentColor = validateColor($_POST['accent_color'] ?? '', '#6c5ce7');
        $textColor   = validateColor($_POST['text_color'] ?? '', '#ffffff');
        $bgImage     = filter_var($_POST['background_image'] ?? '', FILTER_SANITIZE_URL);
        $bgMusic     = filter_var($_POST['background_music'] ?? '', FILTER_SANITIZE_URL);
        $font        = $_POST['profile_font'] ?? 'Inter';
        
        // Basic CSS Sanitization (removes HTML tags, but keeps CSS)
        $raw_css = $_POST['custom_css'] ?? '';
        // Wrap in style tags so HTMLFilter recognizes it as a style block
        $wrapped_css = '<style>' . $raw_css . '</style>';
        // Run the filter (using a dummy path for the spacer image)
        $filtered_wrapped = HTMLFilter($wrapped_css, '/assets/images/loader.gif');
        // Extract the cleaned CSS from inside the tags
        if (preg_match('|<style[^>]*>(.*?)</style>|si', $filtered_wrapped, $matches)) {
            $customCSS = $matches[1];
        } else {
            $customCSS = '';
        }
        
        $updateStmt = $db->prepare('UPDATE user_profile_customization SET theme=?, background_color=?, accent_color=?, text_color=?, background_image=?, background_music=?, custom_css=?, profile_font=? WHERE user_id=?');
        $updateStmt->execute([$theme, $bgColor, $accentColor, $textColor, $bgImage, $bgMusic, $customCSS, $font, $id]);
        
        header("Location: /user/" . $id . "?msg=updated");
        exit;
    }

    // B. Friend Logic
    if (isset($_POST['friend_action']) && $_SESSION['id'] != $id) {
        $action = $_POST['friend_action'];
        
        if ($action === 'send') {
            $db->prepare("INSERT INTO user_friends (receiver, sender, date, status) VALUES (?, ?, ?, 0)")->execute([$id, $_SESSION['id'], time()]);
        } 
        elseif ($action === 'remove') {
            $db->prepare("DELETE FROM user_friends WHERE (sender = ? AND receiver = ?) OR (receiver = ? AND sender = ?)")->execute([$id, $_SESSION['id'], $id, $_SESSION['id']]);
        }
        elseif ($action === 'accept') {
            $db->prepare("UPDATE user_friends SET status = 1 WHERE sender = ? AND receiver = ? AND status = 0")->execute([$id, $_SESSION['id']]);
        }
        elseif ($action === 'decline') {
            $db->prepare("DELETE FROM user_friends WHERE sender = ? AND receiver = ? AND status = 0")->execute([$id, $_SESSION['id']]);
        }
        header("Location: /user/" . $id);
        exit;
    }

    // C. Admin Actions
    if (isset($_POST['reRenderAvatar']) && $isAdmin) {
        $db->prepare('UPDATE users SET avatar_hash = ? WHERE id = ?')->execute(['default', $id]);
        $db->prepare('UPDATE user_data SET hat1=0, hat2=0, hat3=0, face=0, shirt=0, pants=0, gear=0 WHERE user = ?')->execute([$id]);
        
        if (class_exists('context')) {
            context::sendDiscordAdminLogs($my->username . ' re-rendered avatar for ID **' . $id . '**');
        }
        header("Location: /user/" . $id . "?msg=rerendered");
        exit;
    }
}

/* ==========================================================================
   READ-ONLY DATA FETCHING
   ========================================================================== */

// Check Friendship Status
$friendStatus = null; // null = strangers, 0 = pending, 1 = friends
if ($AUTH && $_SESSION['id'] != $id) {
    $chkFr = $db->prepare('SELECT status, sender FROM user_friends WHERE (receiver = ? AND sender = ?) OR (sender = ? AND receiver = ?)');
    $chkFr->execute([$_SESSION['id'], $id, $_SESSION['id'], $id]);
    $frData = $chkFr->fetch(PDO::FETCH_OBJ);
    
    if ($frData) {
        $friendStatus = $frData->status; // 0 or 1
        $isRequester  = ($frData->sender == $_SESSION['id']);
    }
}

// Statistics
$postCount = $db->prepare('SELECT COUNT(*) as count FROM forum_posts WHERE author = ?');
$postCount->execute([$id]);
$replyCount = $db->prepare('SELECT COUNT(*) as count FROM forum_replies WHERE author = ?');
$replyCount->execute([$id]);
$totalPosts = $postCount->fetchColumn() + $replyCount->fetchColumn();

// Profile Views (Safe Fallback)
$profileViews = 0;
try {
    $stmt = $db->prepare('SELECT COUNT(*) FROM UserProfileView WHERE TargetID = ?');
    $stmt->execute([$id]);
    $profileViews = $stmt->fetchColumn();
} catch (Exception $e) { /* Table might not exist */ }

// Increment View Count
if ($AUTH && $_SESSION['id'] != $id) {
    try {
        $db->prepare("INSERT INTO UserProfileView (UserID, TargetID, Recurring) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE Recurring = Recurring + 1")->execute([$_SESSION['id'], $id]);
    } catch (Exception $e) { /* Ignore */ }
}

// Check Ban Status
$isBanned = $db->prepare('SELECT id FROM user_bans WHERE user_id = ? AND expired = 0')->execute([$id]) && $db->prepare('SELECT id FROM user_bans WHERE user_id = ? AND expired = 0')->rowCount() > 0;

// Activity status for main user
$current_time = time();
$online_threshold = 300;
$isOnline = isset($gP->expire) && $gP->expire + $online_threshold > $current_time;

// Prepare Custom CSS
$extra_head_content .= '<style>
    :root {
        --bg-color: ' . htmlspecialchars($userCustomization->background_color) . ';
        --accent-color: ' . htmlspecialchars($userCustomization->accent_color) . ';
        --text-color: ' . htmlspecialchars($userCustomization->text_color) . ';
        --font-family: \'' . htmlspecialchars($userCustomization->profile_font) . '\', sans-serif;
    }

    body {
        background-color: var(--bg-color) !important;
        ' . (!empty($userCustomization->background_image) ? "background-image: url('" . htmlspecialchars($userCustomization->background_image) . "'); background-size: cover; background-attachment: fixed;" : "") . '
        font-family: var(--font-family);
        color: var(--text-color);
        min-height: 100vh;
    }
    
    /* Override global glass-box border for profile to merge with banner */
    .glass-box.profile-container {
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.1);
        overflow: hidden;
    }

    /* Customization Modal */
    .modal-card {
        max-width: 600px;
        width: 100%;
    }

    /* User Custom CSS Injection */
    ' . strip_tags($userCustomization->custom_css) . '
</style>';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>

    <?php if (!empty($userCustomization->background_music)): ?>
        <audio id="bg-music" loop>
            <source src="<?= htmlspecialchars($userCustomization->background_music); ?>" type="audio/mpeg">
        </audio>
        <button onclick="toggleMusic()" class="button is-small is-rounded" style="position: fixed; bottom: 20px; left: 20px; z-index: 99;">
            <i class="fas fa-music"></i>
        </button>
    <?php endif; ?>

    <div class="container mt-5 mb-6">
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="notification is-success">Action completed successfully!</div>
        <?php endif; ?>

        <?php if ($isBanned): ?>
            <div class="notification is-danger has-text-centered">
                <i class="fas fa-gavel"></i> <strong>This user has been suspended.</strong>
            </div>
        <?php endif; ?>

        <div class="glass-box profile-container">
            <div class="profile-banner">
                <?php if ($isOwner): ?>
                    <button class="button is-white is-small is-outlined" style="position: absolute; top: 20px; right: 20px;" onclick="openModal('customizeModal')">
                        <i class="fas fa-paint-brush mr-1"></i> Customize
                    </button>
                <?php endif; ?>
                
                <div class="avatar-wrapper">
                    <canvas id="profile-avatar-<?php echo (int)$id; ?>"
                            width="140" height="140"
                            class="avatar-circle avatar-canvas"
                            data-user-id="<?php echo (int)$id; ?>"
                            data-render-type="avatar"
                            data-colors='<?php echo htmlspecialchars(json_encode($avatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>'
                            data-items='<?php echo htmlspecialchars(json_encode($avatarData['items']), ENT_QUOTES, 'UTF-8'); ?>'
                            data-hash="<?php echo htmlspecialchars($gP->avatar_hash); ?>">
                    </canvas>
                    
                    <div class="user-status-dot <?= $isOnline ? 'is-online' : 'is-offline'; ?>" 
                         title="<?= $isOnline ? 'Online' : 'Offline'; ?>"></div>
                </div>
            </div>

            <div class="profile-nav">
                <div class="nav-item active" onclick="switchTab('about')">About</div>
                <div class="nav-item" onclick="switchTab('friends')">Friends <span class="tag is-dark is-rounded ml-1"><?= count($friendsList) ?></span></div>
                <div class="nav-item" onclick="switchTab('stats')">Statistics</div>
            </div>

            <div class="content-area">
                <div class="columns">
                    <div class="column is-4">
                        <h1 class="title is-3 has-text-white mb-2">
                            <?= htmlspecialchars($gP->username); ?>
                            <?php if (isset($gP->administrator) && $gP->administrator >= 1): ?>
                                <img src="/assets/images/badges/admin.png" alt="Admin" title="Administrator" style="height: 24px; vertical-align: middle; margin-left: 5px;">
                            <?php endif; ?>
                            <?php if (isset($gP->premium) && $gP->premium >= 1): ?>
                                <img src="/assets/images/badges/premium.png" alt="Premium" title="Premium Member" style="height: 24px; vertical-align: middle; margin-left: 5px;">
                            <?php endif; ?>
                            <?php if (isset($gP->prestige) && $gP->prestige >= 1): ?>
                                <img src="/assets/images/badges/pres.png" alt="Prestige" title="Prestige Member" style="height: 24px; vertical-align: middle; margin-left: 5px;">
                            <?php endif; ?>
                        </h1>
                        <p class="subtitle is-6 has-text-grey-lighter mb-4">
                            "<?= !empty($gP->status) ? htmlspecialchars($gP->status) : 'No status set.'; ?>"
                        </p>

                        <p class="mb-4">
                            <span class="activity-status">
                                <span class="status-dot <?= $isOnline ? 'status-online' : 'status-offline'; ?>"></span>
                                <?php 
                                if ($isOnline) {
                                    echo 'Online now';
                                } else {
                                    if (isset($gP->expire)) {
                                        $diff = $current_time - $gP->expire;
                                        if ($diff < 60) echo 'Just now';
                                        elseif ($diff < 3600) echo floor($diff / 60) . ' min ago';
                                        elseif ($diff < 86400) echo floor($diff / 3600) . ' hour' . (floor($diff / 3600) > 1 ? 's' : '') . ' ago';
                                        elseif ($diff < 604800) echo floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';
                                        else echo date('M j, Y', $gP->expire);
                                    } else {
                                        echo 'A long time ago';
                                    }
                                }
                                ?>
                            </span>
                        </p>

                        <?php if ($AUTH && !$isOwner && !$isBanned): ?>
                            <div class="buttons are-small">
                                <a href="/user/inbox/send/<?= $id; ?>" class="button is-primary is-fullwidth">
                                    <i class="fas fa-envelope mr-1"></i> Message
                                </a>

                                <form method="post" class="is-fullwidth" style="width: 100%;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <?php if ($friendStatus === null): ?>
                                        <button type="submit" name="friend_action" value="send" class="button is-primary is-outlined is-fullwidth">
                                            <i class="fas fa-user-plus mr-1"></i> Add Friend
                                        </button>
                                    <?php elseif ($friendStatus === 0): ?>
                                        <?php if ($isRequester): ?>
                                            <button class="button is-warning is-fullwidth" disabled>Request Sent</button>
                                        <?php else: ?>
                                            <div class="field has-addons">
                                                <p class="control is-expanded">
                                                    <button type="submit" name="friend_action" value="accept" class="button is-primary is-fullwidth">Accept</button>
                                                </p>
                                                <p class="control">
                                                    <button type="submit" name="friend_action" value="decline" class="button is-danger">Decline</button>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($friendStatus === 1): ?>
                                        <button type="submit" name="friend_action" value="remove" class="button is-danger is-outlined is-fullwidth">
                                            <i class="fas fa-user-minus mr-1"></i> Unfriend
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if ($isAdmin): ?>
                            <hr class="dropdown-divider">
                            <h6 class="title is-6 has-text-danger">Admin Tools</h6>
                            <div class="buttons are-small">
                                <a href="/admin/modify/<?= $id; ?>" class="button is-dark">Edit User</a>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <button type="submit" name="reRenderAvatar" class="button is-warning">Redraw Avatar</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="column is-8">
                        
                        <div id="tab-about" class="tab-content">
                            <div class="box has-background-black-ter has-text-white">
                                <h4 class="title is-5 has-text-white">About Me</h4>
                                <div class="content">
                                    <?= HTMLFilter($gP->blurb ?? 'This user has not written a bio yet.', '/assets/images/loader.gif'); ?>
                                </div>
                            </div>
                        </div>

                        <div id="tab-friends" class="tab-content" style="display:none;">
                            <h4 class="title is-5 has-text-white">Friends (<?= count($friendsList); ?>)</h4>
                            <?php if (empty($friendsList)): ?>
                                <p class="has-text-grey">No friends to show yet.</p>
                            <?php else: ?>
                                <div class="friend-grid">
                                    <?php foreach ($friendsList as $fr): ?>
                                        <?php 
                                        $friendIsOnline = isset($fr->expire) && $fr->expire + 300 > time();
                                        $friendAvatarData = $friendsAvatarData[$fr->id] ?? $avatarData;
                                        ?>
                                        <div class="friend-item">
                                            <a href="/user/<?= $fr->id; ?>" class="has-text-white" style="position: relative; display: inline-block;">
                                                <canvas class="friend-avatar avatar-canvas"
                                                        id="friend-avatar-<?php echo (int)$fr->id; ?>"
                                                        width="80" height="80"
                                                        data-user-id="<?php echo (int)$fr->id; ?>"
                                                        data-render-type="avatar"
                                                        data-colors='<?php echo htmlspecialchars(json_encode($friendAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>'
                                                        data-items='<?php echo htmlspecialchars(json_encode($friendAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>'
                                                        data-hash="<?php echo htmlspecialchars($fr->avatar_hash); ?>">
                                                </canvas>
                                                <div class="friend-status-dot <?= $friendIsOnline ? 'friend-online' : 'friend-offline'; ?>"
                                                     title="<?= $friendIsOnline ? 'Online' : 'Offline'; ?>">
                                                </div>
                                                <div class="is-size-7 text-truncate" style="max-width: 80px;"><?= htmlspecialchars($fr->username); ?></div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="tab-stats" class="tab-content" style="display:none;">
                            <div class="columns is-multiline">
                                <div class="column is-6">
                                    <div class="box has-background-black-ter">
                                        <p class="heading has-text-grey">Join Date</p>
                                        <p class="title is-5 has-text-white"><?= date('M d, Y', $gP->joined); ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="box has-background-black-ter">
                                        <p class="heading has-text-grey">Last Seen</p>
                                        <p class="title is-5 has-text-white"><?= date('M d, Y @ h:i A', $gP->expire); ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="box has-background-black-ter">
                                        <p class="heading has-text-grey">Forum Posts</p>
                                        <p class="title is-5 has-text-white"><?= number_format($totalPosts); ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="box has-background-black-ter">
                                        <p class="heading has-text-grey">Profile Views</p>
                                        <p class="title is-5 has-text-white"><?= number_format($profileViews); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isOwner): ?>
    <div class="modal" id="customizeModal">
        <div class="modal-background" onclick="closeModal('customizeModal')"></div>
        <div class="modal-card">
            <header class="modal-card-head has-background-dark">
                <p class="modal-card-title has-text-white">Customize Profile</p>
                <button class="delete" aria-label="close" onclick="closeModal('customizeModal')"></button>
            </header>
            <section class="modal-card-body has-background-grey-darker has-text-white">
                <form method="post" id="customForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="save_customization" value="1">
                    <input type="hidden" name="theme" value="custom">

                    <div class="columns">
                        <div class="column">
                            <label class="label has-text-light">Background</label>
                            <input class="input has-background-grey-dark has-text-white" type="color" name="background_color" value="<?= htmlspecialchars($userCustomization->background_color); ?>">
                        </div>
                        <div class="column">
                            <label class="label has-text-light">Accent</label>
                            <input class="input has-background-grey-dark has-text-white" type="color" name="accent_color" value="<?= htmlspecialchars($userCustomization->accent_color); ?>">
                        </div>
                        <div class="column">
                            <label class="label has-text-light">Text</label>
                            <input class="input has-background-grey-dark has-text-white" type="color" name="text_color" value="<?= htmlspecialchars($userCustomization->text_color); ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-light">Font</label>
                        <div class="select is-fullwidth">
                            <select name="profile_font">
                                <?php $fonts = ['Inter', 'Roboto', 'Poppins', 'Orbitron', 'Courier New']; ?>
                                <?php foreach($fonts as $f): ?>
                                    <option value="<?= $f ?>" <?= ($userCustomization->profile_font == $f) ? 'selected' : '' ?>><?= $f ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-light">Background Image URL</label>
                        <input class="input" type="url" name="background_image" value="<?= htmlspecialchars($userCustomization->background_image); ?>" placeholder="https://imgur.com/...">
                    </div>

                    <div class="field">
                        <label class="label has-text-light">Background Music (.mp3)</label>
                        <input class="input" type="url" name="background_music" value="<?= htmlspecialchars($userCustomization->background_music); ?>" placeholder="https://...file.mp3">
                    </div>

                    <div class="field">
                        <label class="label has-text-light">Custom CSS</label>
                        <textarea class="textarea" name="custom_css" placeholder="body { ... }"><?= htmlspecialchars($userCustomization->custom_css); ?></textarea>
                    </div>
                </form>
            </section>
            <footer class="modal-card-foot has-background-dark">
                <button class="button is-success" onclick="document.getElementById('customForm').submit()">Save Changes</button>
                <button class="button" onclick="closeModal('customizeModal')">Cancel</button>
            </footer>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function switchTab(tabName) {
            // Hide all contents
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            // Remove active class
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            
            // Show specific
            document.getElementById('tab-' + tabName).style.display = 'block';
            // Highlight specific
            event.currentTarget.classList.add('active');
        }

        function openModal(id) {
            document.getElementById(id).classList.add('is-active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('is-active');
        }

        function toggleMusic() {
            var audio = document.getElementById("bg-music");
            if (audio.paused) audio.play(); else audio.pause();
        }

        // Autoplay music helper
        document.body.addEventListener('click', function() {
            var audio = document.getElementById("bg-music");
            if(audio && audio.paused && audio.currentTime === 0) {
                audio.volume = 0.2;
                audio.play().catch(e => console.log("Autoplay blocked until interaction"));
            }
        }, {once:true});

        // Initialize avatar rendering
        document.addEventListener('DOMContentLoaded', function() {
            // Small helper to ensure loaders exist
            function ensureLoader(name, url) {
                try {
                    if (!window.THREE) return;
                    if (name === 'GLTFLoader' && !window.THREE.GLTFLoader) {
                        var s = document.createElement('script');
                        s.src = url;
                        s.onload = function(){ console.info('User Profile: Loaded fallback GLTFLoader'); };
                        s.onerror = function(){ console.warn('User Profile: Failed to load GLTFLoader fallback'); };
                        document.head.appendChild(s);
                    }
                    if (name === 'OBJLoader' && !window.THREE.OBJLoader) {
                        var s2 = document.createElement('script');
                        s2.src = url;
                        s2.onload = function(){ console.info('User Profile: Loaded fallback OBJLoader'); };
                        s2.onerror = function(){ console.warn('User Profile: Failed to load OBJLoader fallback'); };
                        document.head.appendChild(s);
                    }
                } catch (e) { console.warn('ensureLoader error', e); }
            }
            
            setTimeout(function(){
                // use CDN fallbacks if header provided local files not present
                ensureLoader('GLTFLoader','https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js');
                ensureLoader('OBJLoader','https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js');
                
                // Load the avatar renderer script
                const script = document.createElement('script');
                script.src = '/assets/js/dashboard-avatars.js?v=<?php echo time(); ?>';
                script.onload = function() {
                    console.log('User Profile: Avatar script loaded');
                    // Wait a bit for THREE to be fully ready
                    setTimeout(() => {
                        if (typeof initializeDashboardAvatars3D === 'function') {
                            console.log('User Profile: Initializing avatar renderer...');
                            initializeDashboardAvatars3D();
                        }
                    }, 500);
                };
                script.onerror = function() {
                    console.error('User Profile: Failed to load dashboard-avatars.js');
                };
                document.head.appendChild(script);
            }, 1000);
        });
    </script>

<?php
$app->footer();
?>