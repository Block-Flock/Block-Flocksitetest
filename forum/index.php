<?php
$title = "Forum";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

// Allow guests but protect against abuse
$app->checkAuth(false);

// Bot protection for guests
if (!$AUTH && !$app->rateLimitGuest('forum_view')) {
    http_response_code(429);
    die('Too many requests. Please try again later.');
}

// Subforum metadata
$subforums = [
    1 => [
        'name' => 'Announcements',
        'desc' => 'Updates and announcements from staff',
        'icon' => 'fas fa-bullhorn'
    ],
    2 => [
        'name' => 'General Discussion',
        'desc' => 'General discussion about Bricktaria',
        'icon' => 'fas fa-comments'
    ],
    3 => [
        'name' => 'Off-Topic',
        'desc' => 'Non-Bricktaria related discussions',
        'icon' => 'fas fa-coffee'
    ],
    4 => [
        'name' => 'Marketplace',
        'desc' => 'Promote your creations here',
        'icon' => 'fas fa-store'
    ],
    5 => [
        'name' => 'Bug Reports',
        'desc' => 'Report technical issues here',
        'icon' => 'fas fa-bug'
    ],
    6 => [
        'name' => 'Games Creation',
        'desc' => 'Share your game creations',
        'icon' => 'fas fa-gamepad'
    ]
];

// Get thread counts for each subforum
function getThreadCount($db, $subId) {
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM forum_posts WHERE subforum = ?');
    $stmt->execute([$subId]);
    return $stmt->fetch(PDO::FETCH_OBJ)->count;
}

// Get latest post for each subforum
function getLatestPost($db, $subId) {
    $stmt = $db->prepare('
        SELECT fp.*, u.username, u.avatar_hash 
        FROM forum_posts fp 
        JOIN users u ON u.id = fp.author 
        WHERE fp.subforum = ? 
        ORDER BY fp.lastposttime DESC 
        LIMIT 1
    ');
    $stmt->execute([$subId]);
    return $stmt->fetch(PDO::FETCH_OBJ);
}

// Get recent global activity
function getRecentGlobalActivity($db) {
    $stmt = $db->prepare('
        SELECT fp.id, fp.title, fp.subforum, fp.lastposttime, fp.author, u.username, u.avatar_hash 
        FROM forum_posts fp 
        JOIN users u ON u.id = fp.author 
        ORDER BY fp.lastposttime DESC 
        LIMIT 5
    ');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

// Get global stats
$totalPosts = $db->query('SELECT COUNT(*) as count FROM forum_posts')->fetch(PDO::FETCH_OBJ)->count;
$totalReplies = $db->query('SELECT COUNT(*) as count FROM forum_replies')->fetch(PDO::FETCH_OBJ)->count;

$recentActivity = getRecentGlobalActivity($db);
?>

<section class="hero is-small is-primary">
    <div class="hero-body">
        <div class="container has-text-centered">
            <h1 class="title">
                <i class="fas fa-comments"></i> Bricktaria Forums
            </h1>
            <h2 class="subtitle">Connect, discuss, and share with the community</h2>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns">
            <!-- Main Content (Categories) - Left Side -->
            <div class="column is-9">
                <div class="glass-box mb-4 p-5">
                    <h2 class="title is-4 has-text-weight-bold">
                        <i class="fas fa-layer-group mr-2"></i>Forum Categories
                    </h2>
                    <p class="subtitle is-6 has-text-grey-light">Browse discussions by topic</p>
                </div>

                <?php foreach ($subforums as $id => $subforum): ?>
                    <?php 
                    $threadCount = getThreadCount($db, $id);
                    $latestPost = getLatestPost($db, $id);
                    ?>
                    <div class="glass-box forum-category-card mb-4 p-5">
                        <article class="media">
                            <figure class="media-left">
                                <span class="icon is-large has-text-primary">
                                    <i class="<?= $subforum['icon'] ?> fa-2x"></i>
                                </span>
                            </figure>
                            <div class="media-content">
                                <div class="content">
                                    <div class="columns is-vcentered">
                                        <div class="column">
                                            <p>
                                                <strong class="is-size-5">
                                                    <a href="/forum/topic/<?= $id ?>" class="has-text-white"><?= htmlspecialchars($subforum['name']) ?></a>
                                                </strong>
                                                <br>
                                                <span class="has-text-grey-light"><?= htmlspecialchars($subforum['desc']) ?></span>
                                            </p>
                                        </div>
                                        <div class="column is-narrow has-text-right-tablet">
                                            <span class="tag is-primary is-light">
                                                <i class="fas fa-folder"></i>&nbsp;&nbsp;<?= number_format($threadCount) ?> thread<?= $threadCount != 1 ? 's' : '' ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($latestPost): ?>
                                        <div class="box is-shadowless has-background-dark mt-2 p-2" style="background: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.05);">
                                            <div class="media is-align-items-center">
                                                <div class="media-left mr-2">
                                                    <figure class="image is-24x24">
                                                        <img src="https://bricktaria.com/assets/images/avatars/<?= $latestPost->avatar_hash ?>.png" class="is-rounded" alt="Avatar">
                                                    </figure>
                                                </div>
                                                <div class="media-content">
                                                    <p class="is-size-7 has-text-grey-lighter">
                                                        <a href="/forum/post/<?= $latestPost->id ?>" class="has-text-weight-semibold has-text-primary">
                                                            <?= htmlspecialchars($latestPost->title) ?>
                                                        </a>
                                                        <span class="has-text-grey-light">by</span> 
                                                        <a href="/user/<?= $latestPost->author ?>" class="has-text-light"><?= htmlspecialchars($latestPost->username) ?></a>
                                                        <span class="has-text-grey">• <?= timeAgo($latestPost->lastposttime) ?></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Sidebar - Right Side -->
            <div class="column is-3">
                <aside class="menu">
                    <?php if ($AUTH): ?>
                        <a href="/forum/create" class="button is-primary is-fullwidth mb-4">
                            <span class="icon">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span>Create New Post</span>
                        </a>
                    <?php else: ?>
                        <div class="notification is-warning is-light mb-4">
                            <p class="has-text-centered">
                                <i class="fas fa-exclamation-triangle"></i><br>
                                <strong>Login Required</strong>
                            </p>
                            <p class="is-size-7 mt-2">
                                You need to be logged in to create new posts
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Recent Activity Sidebar -->
                    <p class="menu-label has-text-grey-light mt-5">
                        <i class="fas fa-history"></i> Recent Activity
                    </p>
                    <div class="glass-box p-0">
                        <?php if (count($recentActivity) > 0): ?>
                            <?php foreach ($recentActivity as $post): ?>
                                <div class="p-3 border-bottom" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <div class="is-flex is-align-items-start">
                                        <figure class="image is-32x32 mr-2 mt-1">
                                            <img src="https://bricktaria.com/assets/images/avatars/<?= $post->avatar_hash ?>.png" class="is-rounded" onerror="this.src='/assets/images/avatars/default.png'">
                                        </figure>
                                        <div style="width: 100%; overflow: hidden;">
                                            <p class="is-size-7 mb-1 is-truncated">
                                                <a href="/forum/post/<?= $post->id ?>" class="has-text-weight-bold has-text-white">
                                                    <?= htmlspecialchars($post->title) ?>
                                                </a>
                                            </p>
                                            <p class="is-size-7 has-text-grey-light">
                                                <span class="tag is-dark is-small" style="height: 1.5em; font-size: 0.65rem;">
                                                    <?= htmlspecialchars($subforums[$post->subforum]['name'] ?? 'Unknown') ?>
                                                </span>
                                            </p>
                                            <p class="is-size-7 mt-1">
                                                <a href="/user/<?= $post->author ?>" class="has-text-info"><?= htmlspecialchars($post->username) ?></a>
                                                <span class="has-text-grey">· <?= timeAgo($post->lastposttime) ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-3 has-text-centered has-text-grey">
                                No recent activity
                            </div>
                        <?php endif; ?>
                    </div>

                    <p class="menu-label has-text-grey-light mt-5">
                        <i class="fas fa-chart-bar"></i> Forum Statistics
                    </p>
                    <div class="glass-box p-4">
                        <div class="content">
                            <p class="mb-2">
                                <span class="icon-text">
                                    <span class="icon has-text-info">
                                        <i class="fas fa-file-alt"></i>
                                    </span>
                                    <span><strong><?= number_format($totalPosts) ?></strong> Posts</span>
                                </span>
                            </p>
                            <p class="mb-2">
                                <span class="icon-text">
                                    <span class="icon has-text-success">
                                        <i class="fas fa-reply"></i>
                                    </span>
                                    <span><strong><?= number_format($totalReplies) ?></strong> Replies</span>
                                </span>
                            </p>
                            <p class="mb-0">
                                <span class="icon-text">
                                    <span class="icon has-text-warning">
                                        <i class="fas fa-fire"></i>
                                    </span>
                                    <span><strong><?= number_format($totalPosts + $totalReplies) ?></strong> Total Activity</span>
                                </span>
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>

<?php $app->footer(); ?>