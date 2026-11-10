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

<div class="container forum-container">
    <div class="card">
        <div class="top blue">
            <div class="col-7-12"><?= htmlspecialchars($info['name']) ?></div>
            <div class="col-1-12 center">Replies</div>
            <div class="col-4-12">Last Post</div>
        </div>
        <div class="content">
            <?php if ($total == 0): ?>
                <div class="board-info mb1">
                    <div class="col-12-12 center">No threads yet. Be the first to start a discussion!</div>
                </div>
            <?php else: ?>
                <?php while ($thread = $threads->fetch(PDO::FETCH_OBJ)): ?>
                    <div class="board-info mb1">
                        <div class="col-7-12">
                            <div class="board-title">
                                <a href="/forum/post/<?= $thread->thread_id ?>"><?= htmlspecialchars($thread->title) ?></a>
                            </div>
                            <div class="board-description">
                                by <a href="/user/<?= $thread->author ?>"><?= htmlspecialchars($thread->username) ?></a>
                                <?php if ($thread->administrator >= 1): ?>
                                    <span class="tag is-danger is-small">Admin</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-1-12 center mt2"><?= number_format($thread->reply_count) ?></div>
                        <div class="col-4-12">
                            <div class="board-last-post">
                                <div class="last-post-meta">
                                    <span class="time"><?= timeAgo($thread->lastposttime) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($AUTH): ?>
        <div class="mt2">
            <a href="/forum/create/<?= htmlspecialchars((string)$sub) ?>" class="button blue">Create New Thread</a>
        </div>
    <?php endif; ?>
</div>

<?php $app->footer(); ?>
