<?php
$title = "Forum";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

// Allow guests but protect against abuse
$app->checkAuth(false);

// Bot protection for guests
if (!$AUTH && !$app->rateLimitGuest('forum_view')) {
    http_response_code(429);
    die('Too many requests. Please try again later.');
}

// Validate subforum ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /forum');
    exit;
}
$sub = (int)$_GET['id'];

// Subforum metadata
$subforumData = [
    1 => ['name' => 'Announcements', 'desc' => 'Updates and announcements from staff', 'icon' => 'fas fa-bullhorn'],
    2 => ['name' => 'General Discussion', 'desc' => 'General discussion about Bricktaria', 'icon' => 'fas fa-comments'],
    3 => ['name' => 'Off-Topic', 'desc' => 'Non-Bricktaria related discussions', 'icon' => 'fas fa-coffee'],
    4 => ['name' => 'Marketplace', 'desc' => 'Promote your creations here', 'icon' => 'fas fa-store'],
    5 => ['name' => 'Bug Reports', 'desc' => 'Report technical issues here', 'icon' => 'fas fa-bug'],
    6 => ['name' => 'Games Creation', 'desc' => 'Share your game creations', 'icon' => 'fas fa-gamepad']
];

if (!isset($subforumData[$sub])) {
    header('Location: /forum');
    exit;
}

$info = $subforumData[$sub];

// Thread counter function
function countThreadsInSubforum($db, $subId): int {
    $stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM forum_posts WHERE subforum = ?');
    $stmt->execute([$subId]);
    return (int)$stmt->fetch(PDO::FETCH_OBJ)->cnt;
}

// Thread loader function
function getThreadsBySubforum($db, $subId) {
    $stmt = $db->prepare('
        SELECT fp.id as thread_id, fp.title, fp.author, fp.timemade, fp.lastposttime, fp.subforum, fp.pinned, fp.locked, fp.views,
               u.id as user_id, u.username, u.expire, u.administrator, u.premium, u.avatar_hash,
               (SELECT COUNT(*) FROM forum_replies WHERE thread = fp.id) as reply_count
        FROM forum_posts fp
        JOIN users u ON u.id = fp.author
        WHERE fp.subforum = ?
        ORDER BY fp.pinned DESC, fp.lastposttime DESC
    ');
    $stmt->execute([$subId]);
    return $stmt;
}

// Global Recent Activity function
function getGlobalRecentActivity($db, $limit = 5) {
    $stmt = $db->prepare('
        SELECT fp.id as thread_id, fp.title, fp.lastposttime, fp.author, u.username, u.avatar_hash, u.administrator, u.premium
        FROM forum_posts fp
        JOIN users u ON u.id = fp.author
        ORDER BY fp.lastposttime DESC
        LIMIT ' . (int)$limit
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

// Get thread data
$total = countThreadsInSubforum($db, $sub);
$threads = getThreadsBySubforum($db, $sub);
$recentActivity = getGlobalRecentActivity($db, 5);

// ============================================================================
// FETCH AVATAR DATA FOR ALL RELEVANT USERS
// ============================================================================

// Collect all user IDs from threads AND recent activity
$allUserIds = [];

// From Threads
$threads->execute([$sub]);
while ($thread = $threads->fetch(PDO::FETCH_OBJ)) {
    $allUserIds[] = (int)$thread->author;
}

// From Recent Activity
foreach ($recentActivity as $activity) {
    $allUserIds[] = (int)$activity->author;
}

$allUserIds = array_unique($allUserIds);
$allUserIds = array_map('intval', array_values($allUserIds));

// Fetch user colors (BATCH QUERY)
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
        error_log("Forum - Error fetching user colors: " . $e->getMessage());
    }
}

// Fetch user wearables (BATCH QUERY)
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
        error_log("Forum - Error fetching user wearables: " . $e->getMessage());
    }
}

// Pre-fetch avatar data for all authors
$threadsAvatarData = [];
if (!empty($allUserIds)) {
    foreach ($allUserIds as $userId) {
        $avatarData = getAvatarData($userId, $userColors, $userWearables);
        if (!isset($avatarData['items'])) $avatarData['items'] = [];
        $avatarData['items'] = normalize_avatar_items_array($avatarData['items']);
        $threadsAvatarData[$userId] = $avatarData;
    }
}

// Reset the threads result for display
$threads->execute([$sub]);

// ============================================================================
// LOAD THREE.JS AND AVATAR RENDERER
// ============================================================================

$localThree = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/three.min.js';
$localGLTF = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/GLTFLoader.js';
$localOBJ = $_SERVER['DOCUMENT_ROOT'] . '/assets/vendor/OBJLoader.js';

$extra_head_content = '';

if (file_exists($localThree)) {
    $extra_head_content .= '<script src="/assets/vendor/three.min.js"></script>' . PHP_EOL;
} else {
    $extra_head_content .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>' . PHP_EOL;
}

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

// Add CSS for avatar canvases
$extra_head_content .= '<style>
    .avatar-forum {
        display: block;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #16213e;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .avatar-forum-large {
        width: 48px;
        height: 48px;
    }
    
    .image.is-32x32 {
        width: 32px;
        height: 32px;
    }
    
    .image.is-32x32 canvas {
        width: 100%;
        height: 100%;
    }
    
    .forum-topic-row {
        transition: background-color 0.2s ease;
    }
    
    .forum-topic-row:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
</style>';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>

<section class="hero is-small is-primary">
    <div class="hero-body">
        <div class="container has-text-centered">
            <h1 class="title">
                <i class="<?= $info['icon'] ?>"></i> <?= htmlspecialchars($info['name']) ?>
            </h1>
            <h2 class="subtitle push-10"><?= htmlspecialchars($info['desc']) ?></h2>
            <div class="push-5"></div>
            <p class="subtitle is-marginless-bottom is-size-5">
                <span class="tag is-dark push-10 is-medium">
                    <b>Threads</b>: <?= number_format($total) ?>
                </span>
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns">
            <!-- Sidebar -->
            <div class="column is-3">
                <aside class="menu">
                    <?php if ($AUTH) { ?>
                        <a href="/forum/create/<?= htmlspecialchars((string)$sub) ?>" class="button is-primary is-fullwidth is-rounded mb-5">
                            <i class="fas fa-plus"></i>&nbsp;Create New Thread
                        </a>
                    <?php } else { ?>
                        <div class="notification is-warning is-light mb-5">
                            <i class="fas fa-exclamation-triangle"></i> 
                            You need to be logged in to create new threads
                        </div>
                    <?php } ?>
                    
                    <p class="menu-label has-text-grey-light">
                        <i class="fas fa-bolt"></i> Recent Activity
                    </p>
                    <div class="glass-box p-3 mb-5">
                        <?php if (empty($recentActivity)): ?>
                            <p class="has-text-grey is-size-7">No recent activity.</p>
                        <?php else: ?>
                            <?php foreach ($recentActivity as $act): ?>
                                <?php 
                                    $actAvatar = $threadsAvatarData[$act->author] ?? ['colors' => [], 'items' => []]; 
                                ?>
                                <article class="media mb-3">
                                    <figure class="media-left">
                                        <p class="image is-32x32">
                                            <canvas class="avatar-forum avatar-canvas"
                                                    id="sidebar-avatar-<?= $act->thread_id ?>-<?= $act->author ?>"
                                                    width="32" height="32"
                                                    data-user-id="<?= $act->author ?>"
                                                    data-render-type="avatar"
                                                    data-colors='<?= htmlspecialchars(json_encode($actAvatar['colors']), ENT_QUOTES, 'UTF-8') ?>'
                                                    data-items='<?= htmlspecialchars(json_encode($actAvatar['items']), ENT_QUOTES, 'UTF-8') ?>'
                                                    data-hash="<?= htmlspecialchars($act->avatar_hash) ?>">
                                            </canvas>
                                        </p>
                                    </figure>
                                    <div class="media-content">
                                        <div class="content is-small">
                                            <p class="mb-0">
                                                <a href="/forum/post/<?= $act->thread_id ?>" class="has-text-weight-bold has-text-white" style="line-height: 1.2; display: block; margin-bottom: 2px;">
                                                    <?= htmlspecialchars($act->title) ?>
                                                </a>
                                                <span class="has-text-grey-light is-size-7">
                                                    by <a href="/user/<?= $act->author ?>" class="has-text-grey-light"><?= htmlspecialchars($act->username) ?></a>
                                                </span>
                                                <br>
                                                <span class="has-text-grey is-size-7"><?= timeAgo($act->lastposttime) ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <p class="menu-label has-text-grey-light">Navigation</p>
                    <ul class="menu-list">
                        <li><a href="/forum"><i class="fas fa-arrow-left"></i> Back to Forum Home</a></li>
                    </ul>
                </aside>
            </div>

            <!-- Main Content -->
            <div class="column is-9">
                <div class="glass-box mb-4 p-5">
                     <div class="level is-mobile">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <h3 class="title is-4 mb-1 has-text-weight-bold">
                                        <i class="<?= $info['icon'] ?> mr-2"></i><?= htmlspecialchars($info['name']) ?>
                                    </h3>
                                    <p class="subtitle is-6 has-text-grey-light">Browsing category</p>
                                </div>
                            </div>
                        </div>
                     </div>
                </div>

                <?php if ($total == 0): ?>
                    <div class="glass-box has-text-centered p-6">
                        <i class="fas fa-comments" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3 class="title is-4">No threads yet</h3>
                        <p class="subtitle is-6">Be the first to start a discussion in this forum!</p>
                        <?php if ($AUTH): ?>
                            <a href="/forum/create/<?= $sub ?>" class="button is-primary mt-3">
                                <i class="fas fa-plus"></i> Create First Thread
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="glass-box p-0 overflow-hidden">
                        <div class="table-container mb-0">
                            <table class="table is-fullwidth glass-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="p-4 has-text-grey-light" width="50%">Topic</th>
                                        <th class="p-4 has-text-grey-light">Author</th>
                                        <th class="p-4 has-text-centered has-text-grey-light">Replies</th>
                                        <th class="p-4 has-text-right has-text-grey-light">Last Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($thread = $threads->fetch(PDO::FETCH_OBJ)): ?>
                                        <?php 
                                        $authorAvatarData = $threadsAvatarData[$thread->author] ?? [
                                            'colors' => [],
                                            'items' => []
                                        ];
                                        ?>
                                        <tr class="forum-topic-row">
                                            <td class="p-4">
                                                <div class="is-flex is-align-items-center">
                                                    <?php if ($thread->pinned): ?>
                                                        <span class="icon has-text-warning mr-2" title="Pinned">
                                                            <i class="fas fa-thumbtack"></i>
                                                        </span>
                                                    <?php elseif ($thread->locked): ?>
                                                        <span class="icon has-text-danger mr-2" title="Locked">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="icon has-text-info mr-2">
                                                            <i class="far fa-file-alt"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <div>
                                                        <a href="/forum/post/<?= $thread->thread_id ?>" class="title is-6 has-text-white" style="text-decoration: none;">
                                                            <?= htmlspecialchars($thread->title) ?>
                                                        </a>
                                                        <div class="is-size-7 mt-1">
                                                            <span class="tag is-dark is-rounded is-light" style="font-size: 0.65rem; height: 1.5em;"><?= htmlspecialchars($info['name']) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="media is-align-items-center">
                                                    <div class="media-left mr-2">
                                                        <figure class="image is-32x32">
                                                            <canvas class="avatar-forum avatar-canvas"
                                                                    id="forum-avatar-<?php echo (int)$thread->thread_id; ?>-<?php echo (int)$thread->author; ?>"
                                                                    width="32" height="32"
                                                                    data-user-id="<?php echo (int)$thread->author; ?>"
                                                                    data-render-type="avatar"
                                                                    data-colors='<?php echo htmlspecialchars(json_encode($authorAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>'
                                                                    data-items='<?php echo htmlspecialchars(json_encode($authorAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>'
                                                                    data-hash="<?php echo htmlspecialchars($thread->avatar_hash); ?>">
                                                            </canvas>
                                                        </figure>
                                                    </div>
                                                    <div class="media-content">
                                                        <a href="/user/<?= $thread->author ?>" class="has-text-weight-bold has-text-light is-size-7">
                                                            <?= htmlspecialchars($thread->username) ?>
                                                        </a>
                                                        <div class="is-flex is-align-items-center">
                                                            <?php if ($thread->administrator >= 1): ?>
                                                                <img src="/assets/images/badges/admin.png" alt="Admin" title="Administrator" style="height: 14px; margin-right: 2px;">
                                                            <?php endif; ?>
                                                            <?php if ($thread->premium >= 1): ?>
                                                                <img src="/assets/images/badges/premium.png" alt="Premium" title="Premium Member" style="height: 14px; margin-right: 2px;">
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4 has-text-centered">
                                                <span class="tag is-primary is-light is-rounded"><?= number_format($thread->reply_count) ?></span>
                                                <p class="is-size-7 has-text-grey mt-1"><?= number_format($thread->views) ?> views</p>
                                            </td>
                                            <td class="p-4 has-text-right">
                                                <p class="is-size-7 has-text-grey-light"><?= timeAgo($thread->lastposttime) ?></p>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    // Initialize avatar rendering for forum
    document.addEventListener('DOMContentLoaded', function() {
        // Small helper to ensure loaders exist
        function ensureLoader(name, url) {
            try {
                if (!window.THREE) return;
                if (name === 'GLTFLoader' && !window.THREE.GLTFLoader) {
                    var s = document.createElement('script');
                    s.src = url;
                    s.onload = function(){ console.info('Forum: Loaded fallback GLTFLoader'); };
                    s.onerror = function(){ console.warn('Forum: Failed to load GLTFLoader fallback'); };
                    document.head.appendChild(s);
                }
                if (name === 'OBJLoader' && !window.THREE.OBJLoader) {
                    var s2 = document.createElement('script');
                    s2.src = url;
                    s2.onload = function(){ console.info('Forum: Loaded fallback OBJLoader'); };
                    s2.onerror = function(){ console.warn('Forum: Failed to load OBJLoader fallback'); };
                    document.head.appendChild(s);
                }
            } catch (e) { console.warn('ensureLoader error', e); }
        }
        
        setTimeout(function() {
            // use CDN fallbacks if header provided local files not present
            ensureLoader('GLTFLoader','https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js');
            ensureLoader('OBJLoader','https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js');
            
            // Load the avatar renderer script
            const script = document.createElement('script');
            script.src = '/assets/js/dashboard-avatars.js?v=<?php echo time(); ?>';
            script.onload = function() {
                console.log('Forum: Avatar script loaded');
                // Wait a bit for THREE to be fully ready
                setTimeout(() => {
                    if (typeof initializeDashboardAvatars3D === 'function') {
                        console.log('Forum: Initializing avatar renderer...');
                        initializeDashboardAvatars3D();
                    } else {
                        console.error('Forum: initializeDashboardAvatars3D function not found');
                    }
                }, 1000);
            };
            script.onerror = function() {
                console.error('Forum: Failed to load dashboard-avatars.js');
            };
            document.head.appendChild(script);
        }, 500);
    });
</script>

<?php $app->footer(); ?>
