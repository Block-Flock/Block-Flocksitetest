<?php
$title = "Thread";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

// Debug: Check what parameters we're receiving
error_log("Thread page accessed - GET params: " . print_r($_GET, true));
error_log("Thread page accessed - REQUEST_URI: " . $_SERVER['REQUEST_URI']);

// Validate thread ID - check both 'id' and any path-based routing
$id = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    // Try to extract ID from URL path (e.g., /forum/post/123)
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $parts = explode('/', trim($path, '/'));
    
    // Look for numeric ID in URL parts
    foreach ($parts as $part) {
        if (is_numeric($part)) {
            $id = (int)$part;
            break;
        }
    }
}

if (!$id) {
    error_log("Thread page: No valid ID found");
    header('Location: /forum');
    die();
}

error_log("Thread page: Processing thread ID: " . $id);

// Get thread data
$selectThread = $db->prepare('SELECT fp.*, u.username, u.avatar_hash, u.administrator, u.premium, u.expire FROM forum_posts fp JOIN users u ON fp.author = u.id WHERE fp.id = ?');
$selectThread->bindValue(1, $id, PDO::PARAM_INT);
$selectThread->execute();

$rowCount = $selectThread->rowCount();
error_log("Thread query returned: " . $rowCount . " rows");

if ($rowCount == 0) {
    error_log("Thread not found: ID " . $id);
    // Show error instead of silent redirect
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Thread Not Found</title>
    </head>
    <body>
        <div class="notification is-danger">
            <strong>Error:</strong> Thread #<?= $id ?> not found. 
            <a href="/forum">Return to forum</a>
        </div>
    </body>
    </html>
    <?php
    die();
}

$thread = $selectThread->fetch(PDO::FETCH_OBJ);

// Track view if user is logged in
if ($AUTH) {
    try {
        $checkRead = $db->prepare('SELECT id FROM `read` WHERE `userId` = ? AND `postId` = ?');
        $checkRead->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $checkRead->bindValue(2, $id, PDO::PARAM_INT);
        $checkRead->execute();
        
        if ($checkRead->rowCount() == 0) {
            // Mark as read
            $insertRead = $db->prepare('INSERT INTO `read` (`userId`, `postId`) VALUES (?, ?)');
            $insertRead->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
            $insertRead->bindValue(2, $id, PDO::PARAM_INT);
            $insertRead->execute();
            
            // Try to increment views
            try {
                $updateViews = $db->prepare('UPDATE forum_posts SET views = views + 1 WHERE id = ?');
                $updateViews->bindValue(1, $id, PDO::PARAM_INT);
                $updateViews->execute();
            } catch (PDOException $e) {
                // Views column doesn't exist, log or ignore
                error_log("Views column not found: " . $e->getMessage());
            }
        }
    } catch (PDOException $e) {
        error_log("Read tracking error: " . $e->getMessage());
    }
}

// Subforum metadata
$subforumData = [
    1 => ['name' => 'Announcements', 'icon' => 'fas fa-bullhorn'],
    2 => ['name' => 'General Discussion', 'icon' => 'fas fa-comments'],
    3 => ['name' => 'Off-Topic', 'icon' => 'fas fa-coffee'],
    4 => ['name' => 'Marketplace', 'icon' => 'fas fa-store'],
    5 => ['name' => 'Bug Reports', 'icon' => 'fas fa-bug'],
    6 => ['name' => 'Games Creation', 'icon' => 'fas fa-gamepad']
];

$subforum = $subforumData[$thread->subforum] ?? $subforumData[2];

// Admin actions
if ($AUTH && $my->administrator >= 1) {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'lock':
            case 'unlock':
                $update = $db->prepare('UPDATE forum_posts SET locked = (CASE WHEN locked = 0 THEN 1 ELSE 0 END) WHERE id = ?');
                $update->bindValue(1, $id, PDO::PARAM_INT);
                $update->execute();
                break;
            case 'pin':
            case 'unpin':
                $update = $db->prepare('UPDATE forum_posts SET pinned = (CASE WHEN pinned = 0 THEN 1 ELSE 0 END) WHERE id = ?');
                $update->bindValue(1, $id, PDO::PARAM_INT);
                $update->execute();
                break;
            case 'delete':
                $delete = $db->prepare('DELETE FROM forum_posts WHERE id = ?');
                $delete->bindValue(1, $id, PDO::PARAM_INT);
                $delete->execute();
                header('Location: /forum/topic/' . $thread->subforum);
                die();
                break;
        }
        header('Location: /forum/post/' . $id);
        die();
    }
}

// Get replies
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$replyCount = $db->prepare('SELECT COUNT(*) as count FROM forum_replies WHERE thread = ?');
$replyCount->bindValue(1, $id, PDO::PARAM_INT);
$replyCount->execute();
$totalReplies = $replyCount->fetch(PDO::FETCH_OBJ)->count;
$totalPages = ceil($totalReplies / $limit);

$selectReplies = $db->prepare('
    SELECT fr.*, u.username, u.avatar_hash, u.administrator, u.premium, u.expire 
    FROM forum_replies fr 
    JOIN users u ON fr.author = u.id 
    WHERE fr.thread = ? 
    ORDER BY fr.timemade ASC 
    LIMIT ? OFFSET ?
');
$selectReplies->bindValue(1, $id, PDO::PARAM_INT);
$selectReplies->bindValue(2, $limit, PDO::PARAM_INT);
$selectReplies->bindValue(3, $offset, PDO::PARAM_INT);
$selectReplies->execute();

// ============================================================================
// FETCH AVATAR DATA FOR THREAD AUTHOR AND REPLY AUTHORS
// ============================================================================

// Collect all user IDs (thread author + reply authors)
$allUserIds = [(int)$thread->author];

// Store replies for later display
$repliesData = [];
while ($reply = $selectReplies->fetch(PDO::FETCH_OBJ)) {
    $repliesData[] = $reply;
    $allUserIds[] = (int)$reply->author;
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
        error_log("Forum Post - Error fetching user colors: " . $e->getMessage());
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
        error_log("Forum Post - Error fetching user wearables: " . $e->getMessage());
    }
}

// Get avatar data for thread author
$threadAuthorAvatarData = getAvatarData($thread->author, $userColors, $userWearables);
if (!isset($threadAuthorAvatarData['items'])) $threadAuthorAvatarData['items'] = [];
$threadAuthorAvatarData['items'] = normalize_avatar_items_array($threadAuthorAvatarData['items']);

// Get avatar data for reply authors
$replyAuthorsAvatarData = [];
foreach ($repliesData as $reply) {
    $avatarData = getAvatarData($reply->author, $userColors, $userWearables);
    if (!isset($avatarData['items'])) $avatarData['items'] = [];
    $avatarData['items'] = normalize_avatar_items_array($avatarData['items']);
    $replyAuthorsAvatarData[$reply->author] = $avatarData;
}

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
    function log(s){ try{ console.info('[Forum Post loader] ' + s); } catch(e){} }
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
    .avatar-post {
        display: block;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #16213e;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }
    
    .avatar-reply {
        display: block;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #16213e;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .image.is-64x64, .image.is-48x48 {
        width: 100%;
        height: 100%;
    }
    
    .image.is-64x64 canvas, .image.is-48x48 canvas {
        width: 100%;
        height: 100%;
    }
</style>';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>

<section class="hero is-small is-primary">
    <div class="hero-body">
        <div class="container">
            <nav class="breadcrumb has-text-white" aria-label="breadcrumbs">
                <ul>
                    <li><a href="/forum" class="has-text-white">Forum</a></li>
                    <li><a href="/forum/topic/<?= $thread->subforum ?>" class="has-text-white"><?= htmlspecialchars($subforum['name']) ?></a></li>
                    <li class="is-active"><a href="#" class="has-text-white" aria-current="page"><?= htmlspecialchars($thread->title) ?></a></li>
                </ul>
            </nav>
            
            <div class="columns is-centered">
                <div class="column is-8">
                    <h1 class="title has-text-white"><?= htmlspecialchars($thread->title) ?></h1>
                    <p class="subtitle is-6 has-text-white">
                        Posted by <a href="/user/<?= $thread->author ?>" class="has-text-white has-text-weight-bold"><?= htmlspecialchars($thread->username) ?></a> 
                        <?= timeAgo($thread->timemade) ?>
                        <?php if ($thread->pinned): ?>
                            <span class="tag is-warning is-small">Pinned</span>
                        <?php endif; ?>
                        <?php if ($thread->locked): ?>
                            <span class="tag is-danger is-small">Locked</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
                <aside class="menu">
                    <p class="menu-label has-text-grey-light">Thread Info</p>
                    <div class="glass-box p-4">
                        <p class="mb-2"><strong>Views:</strong> <?= number_format($thread->views) ?></p>
                        <p class="mb-2"><strong>Replies:</strong> <?= number_format($totalReplies) ?></p>
                        <p class="mb-0"><strong>Created:</strong> <?= date('M j, Y', $thread->timemade) ?></p>
                    </div>
                    
                    <?php if ($AUTH && $my->administrator >= 1): ?>
                        <p class="menu-label mt-4 has-text-grey-light">Admin Actions</p>
                        <div class="buttons">
                            <a href="/forum/post/<?= $id ?>?action=<?= $thread->locked ? 'unlock' : 'lock' ?>" 
                               class="button is-small <?= $thread->locked ? 'is-success' : 'is-warning' ?>">
                                <i class="fas fa-<?= $thread->locked ? 'unlock' : 'lock' ?>"></i>
                                <?= $thread->locked ? 'Unlock' : 'Lock' ?>
                            </a>
                            <a href="/forum/post/<?= $id ?>?action=<?= $thread->pinned ? 'unpin' : 'pin' ?>" 
                               class="button is-small <?= $thread->pinned ? 'is-success' : 'is-info' ?>">
                                <i class="fas fa-thumbtack"></i>
                                <?= $thread->pinned ? 'Unpin' : 'Pin' ?>
                            </a>
                            <a href="/forum/post/<?= $id ?>?action=delete" 
                               class="button is-small is-danger" 
                               onclick="return confirm('Are you sure you want to delete this thread?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
            
            <div class="column is-9">
                <!-- Original Post -->
                <div class="glass-box p-5 mb-5">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-64x64">
                                <canvas class="avatar-post avatar-canvas"
                                        id="post-avatar-<?php echo (int)$thread->author; ?>-<?php echo (int)$id; ?>"
                                        width="64" height="64"
                                        data-user-id="<?php echo (int)$thread->author; ?>"
                                        data-render-type="avatar"
                                        data-colors='<?php echo htmlspecialchars(json_encode($threadAuthorAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>'
                                        data-items='<?php echo htmlspecialchars(json_encode($threadAuthorAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>'
                                        data-hash="<?php echo htmlspecialchars($thread->avatar_hash); ?>">
                                </canvas>
                            </figure>
                        </div>
                        <div class="media-content">
                            <div class="content">
                                <p class="title is-5">
                                    <a href="/user/<?= $thread->author ?>" class="has-text-white"><?= htmlspecialchars($thread->username) ?></a>
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
                                <p class="subtitle is-6 has-text-grey-light"><?= timeAgo($thread->timemade) ?></p>
                                <div class="content has-text-light">
                                    <?= nl2br(htmlspecialchars($thread->content)) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Replies -->
                <?php if ($totalReplies > 0): ?>
                    <h3 class="title is-4 has-text-white mb-4">Replies (<?= number_format($totalReplies) ?>)</h3>
                    
                    <?php foreach ($repliesData as $reply): ?>
                        <?php 
                        $replyAvatarData = $replyAuthorsAvatarData[$reply->author] ?? [
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
                        <div class="glass-box p-4 mb-4">
                            <div class="media">
                                <div class="media-left">
                                    <figure class="image is-48x48">
                                        <canvas class="avatar-reply avatar-canvas"
                                                id="reply-avatar-<?php echo (int)$reply->author; ?>-<?php echo (int)$reply->id; ?>"
                                                width="48" height="48"
                                                data-user-id="<?php echo (int)$reply->author; ?>"
                                                data-render-type="avatar"
                                                data-colors='<?php echo htmlspecialchars(json_encode($replyAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>'
                                                data-items='<?php echo htmlspecialchars(json_encode($replyAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>'
                                                data-hash="<?php echo htmlspecialchars($reply->avatar_hash); ?>">
                                        </canvas>
                                    </figure>
                                </div>
                                <div class="media-content">
                                    <div class="content">
                                        <p class="title is-6">
                                            <a href="/user/<?= $reply->author ?>" class="has-text-white"><?= htmlspecialchars($reply->username) ?></a>
                                            <?php if ($reply->administrator >= 1): ?>
                                                <span class="tag is-danger is-small">Admin</span>
                                            <?php endif; ?>
                                            <?php if ($reply->premium >= 1): ?>
                                                <span class="tag is-warning is-small">Premium</span>
                                            <?php endif; ?>
                                            <?php if ($reply->expire + 300 > time()): ?>
                                                <span class="tag is-success is-small">Online</span>
                                            <?php endif; ?>
                                        </p>
                                        <p class="subtitle is-7 has-text-grey-light"><?= timeAgo($reply->timemade) ?></p>
                                        <div class="content has-text-light">
                                            <?= nl2br(htmlspecialchars($reply->content)) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="pagination is-centered mb-5" role="navigation" aria-label="pagination">
                            <?php if ($page > 1): ?>
                                <a href="/forum/post/<?= $id ?>?page=<?= $page - 1 ?>" class="pagination-previous has-text-white glass-box">Previous</a>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="/forum/post/<?= $id ?>?page=<?= $page + 1 ?>" class="pagination-next has-text-white glass-box">Next page</a>
                            <?php endif; ?>
                            
                            <ul class="pagination-list">
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li>
                                        <a href="/forum/post/<?= $id ?>?page=<?= $i ?>" 
                                           class="pagination-link glass-box has-text-white <?= $i == $page ? 'is-current has-background-primary' : '' ?>" style="<?= $i == $page ? 'border-color: var(--primary);' : '' ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="glass-box has-text-centered mb-5">
                        <p class="subtitle has-text-grey-light">No replies yet. Be the first to respond!</p>
                    </div>
                <?php endif; ?>
                
                <!-- Reply Form -->
                <?php if ($AUTH && !$thread->locked): ?>
                    <div class="glass-box p-5">
                        <h3 class="title is-5 has-text-white">Post Reply</h3>
                        <form method="post" action="/forum/reply/<?= $id ?>">
                            <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                            <div class="field">
                                <div class="control">
                                    <textarea class="textarea has-background-dark has-text-white" name="content" placeholder="Write your reply..." rows="5" maxlength="2000" required style="border: 1px solid rgba(255,255,255,0.1);"></textarea>
                                </div>
                                <p class="help has-text-grey-light">2000 characters maximum</p>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <button type="submit" class="button is-primary">
                                        <i class="fas fa-reply"></i> Post Reply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php elseif ($thread->locked): ?>
                    <div class="notification is-warning">
                        <i class="fas fa-lock"></i> This thread is locked. No new replies can be posted.
                    </div>
                <?php else: ?>
                    <div class="notification is-info">
                        <i class="fas fa-info-circle"></i> You need to be logged in to post replies.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    // Initialize avatar rendering for forum post page
    document.addEventListener('DOMContentLoaded', function() {
        // Small helper to ensure loaders exist
        function ensureLoader(name, url) {
            try {
                if (!window.THREE) return;
                if (name === 'GLTFLoader' && !window.THREE.GLTFLoader) {
                    var s = document.createElement('script');
                    s.src = url;
                    s.onload = function(){ console.info('Forum Post: Loaded fallback GLTFLoader'); };
                    s.onerror = function(){ console.warn('Forum Post: Failed to load GLTFLoader fallback'); };
                    document.head.appendChild(s);
                }
                if (name === 'OBJLoader' && !window.THREE.OBJLoader) {
                    var s2 = document.createElement('script');
                    s2.src = url;
                    s2.onload = function(){ console.info('Forum Post: Loaded fallback OBJLoader'); };
                    s2.onerror = function(){ console.warn('Forum Post: Failed to load OBJLoader fallback'); };
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
                console.log('Forum Post: Avatar script loaded');
                // Wait a bit for THREE to be fully ready
                setTimeout(() => {
                    if (typeof initializeDashboardAvatars3D === 'function') {
                        console.log('Forum Post: Initializing avatar renderer...');
                        initializeDashboardAvatars3D();
                    } else {
                        console.error('Forum Post: initializeDashboardAvatars3D function not found');
                    }
                }, 1000);
            };
            script.onerror = function() {
                console.error('Forum Post: Failed to load dashboard-avatars.js');
            };
            document.head.appendChild(script);
        }, 500);
    });
</script>

<?php $app->footer(); ?>