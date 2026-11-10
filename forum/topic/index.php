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

// Thread loader function - FIXED: Use aliases to avoid column name conflicts
function getThreadsBySubforum($db, $subId) {
    $stmt = $db->prepare('
        SELECT fp.id as thread_id, fp.title, fp.author, fp.timemade, fp.lastposttime, fp.subforum, fp.pinned, fp.locked, fp.views,
               u.id as user_id, u.username, u.expire, u.administrator, u.premium, u.avatar_hash,
               (SELECT COUNT(*) FROM forum_replies WHERE thread = fp.id) as reply_count
        FROM forum_posts fp
        JOIN users u ON u.id = fp.author
        WHERE fp.subforum = ?
        ORDER BY fp.lastposttime DESC
    ');
    $stmt->execute([$subId]);
    return $stmt;
}

// Get thread data
$total = countThreadsInSubforum($db, $sub);
$threads = getThreadsBySubforum($db, $sub);

// ============================================================================
// FETCH AVATAR DATA FOR ALL THREAD AUTHORS
// ============================================================================

// Collect all user IDs from threads
$allUserIds = [];

// Reset the pointer to fetch again
$threads->execute([$sub]);
while ($thread = $threads->fetch(PDO::FETCH_OBJ)) {
    $allUserIds[] = (int)$thread->author;
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

// Pre-fetch avatar data for all thread authors
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

/*
 * Extra head content: prefer local vendor assets if present,
 * else fall back to CDN.
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
    function log(s){ try{ console.info('[Forum loader] ' + s); } catch(e){} }
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
    
    .image.is-32x32 {
        width: 32px;
        height: 32px;
    }
    
    .image.is-32x32 canvas {
        width: 100%;
        height: 100%;
    }
</style>';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>

<section class="hero is-small is-bold">
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
            <div class="column is-3">
                <aside class="menu">
                    <?php if ($AUTH) { ?>
                        <a href="/forum/create/<?= htmlspecialchars((string)$sub) ?>" class="button is-info is-fullwidth is-rounded">
                            <i class="fas fa-plus"></i> Create New Thread
                        </a>
                    <?php } else { ?>
                        <div class="notification is-warning is-light">
                            <i class="fas fa-exclamation-triangle"></i> 
                            You need to be logged in to create new threads
                        </div>
                    <?php } ?>
                    
                    <p class="menu-label">Bricktaria Forums</p>
                    <ul class="menu-list">
                        <li><a href="/forum/topic/1"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                        <li><a href="/forum/topic/2"><i class="fas fa-comments"></i> General Discussion</a></li>
                        <li><a href="/forum/topic/4"><i class="fas fa-store"></i> Marketplace</a></li>
                    </ul>
                    
                    <p class="menu-label">Other</p>
                    <ul class="menu-list">
                        <li><a href="/forum/topic/3"><i class="fas fa-coffee"></i> Off-Topic</a></li>
                        <li><a href="/forum/topic/5"><i class="fas fa-bug"></i> Bug Reports</a></li>
                        <li><a href="/forum/topic/6"><i class="fas fa-gamepad"></i> Games Creation</a></li>
                    </ul>
                    
                    <p class="menu-label">Navigation</p>
                    <ul class="menu-list">
                        <li><a href="/forum"><i class="fas fa-home"></i> Forum Home</a></li>
                    </ul>
                </aside>
            </div>

            <div class="column is-9">
                <?php if ($total == 0): ?>
                    <div class="box has-text-centered">
                        <i class="fas fa-comments" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3 class="title is-4">No threads yet</h3>
                        <p class="subtitle is-6">Be the first to start a discussion in this forum!</p>
                        <?php if ($AUTH): ?>
                            <a href="/forum/create/<?= $sub ?>" class="button is-primary">
                                <i class="fas fa-plus"></i> Create First Thread
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="box">
                        <div class="table-container">
                            <table class="table is-fullwidth is-striped">
                                <thead>
                                    <tr>
                                        <th>Thread</th>
                                        <th>Author</th>
                                        <th>Replies</th>
                                        <th>Last Post</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($thread = $threads->fetch(PDO::FETCH_OBJ)): ?>
                                        <?php 
                                        $authorAvatarData = $threadsAvatarData[$thread->author] ?? [
                                            'colors' => [
                                                'head' => [1, 0.8, 0.6],
                                                'torso' => [0.2, 0.2, 0.8],
                                                'leftArm' => [1, 0.8, 0.6],
                                                'rightArm' => [1, 0.8, 0.6],
                                                'leftLeg' => [0.1, 0.1, 0.1],
                                                'rightLeg' => [0.1, 0.1, 0.1]
                                            ],
                                            'items' => [
                                                'hats' => [],
                                                'face' => 'default',
                                                'accessory' => 0,
                                                'tshirt' => 0,
                                                'shirt' => 0,
                                                'pants' => 0,
                                                'gear' => 0
                                            ]
                                        ];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="media">
                                                    <div class="media-left">
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
                                                        <p class="title is-6">
                                                            <a href="/forum/post/<?= $thread->thread_id ?>"><?= htmlspecialchars($thread->title) ?></a>
                                                        </p>
                                                        <p class="subtitle is-7">
                                                            <?php if ($thread->administrator >= 1): ?>
                                                                <span class="tag is-danger is-small">Admin</span>
                                                            <?php endif; ?>
                                                            <?php if ($thread->premium >= 1): ?>
                                                                <span class="tag is-warning is-small">Premium</span>
                                                            <?php endif; ?>
                                                            <?php if ($thread->expire + 300 > time()): ?>
                                                                <span class="tag is-success is-small">Online</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="/user/<?= $thread->author ?>"><?= htmlspecialchars($thread->username) ?></a>
                                            </td>
                                            <td>
                                                <span class="tag is-light"><?= $thread->reply_count ?></span>
                                            </td>
                                            <td>
                                                <small><?= timeAgo($thread->lastposttime) ?></small>
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