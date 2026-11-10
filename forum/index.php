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

// Get global stats
$totalPosts = $db->query('SELECT COUNT(*) as count FROM forum_posts')->fetch(PDO::FETCH_OBJ)->count;
$totalReplies = $db->query('SELECT COUNT(*) as count FROM forum_replies')->fetch(PDO::FETCH_OBJ)->count;
?>

<section class="hero is-small is-info">
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
            <!-- Sidebar -->
            <div class="column is-3">
                <aside class="menu">
                    <?php if ($AUTH): ?>
                        <a href="/forum/create" class="button is-info is-fullwidth mb-4">
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
                    
                    <p class="menu-label">
                        <i class="fas fa-chart-bar"></i> Forum Statistics
                    </p>
                    <div class="box">
                        <div class="content">
                            <p>
                                <span class="icon-text">
                                    <span class="icon has-text-info">
                                        <i class="fas fa-file-alt"></i>
                                    </span>
                                    <span><strong><?= number_format($totalPosts) ?></strong> Posts</span>
                                </span>
                            </p>
                            <p>
                                <span class="icon-text">
                                    <span class="icon has-text-success">
                                        <i class="fas fa-reply"></i>
                                    </span>
                                    <span><strong><?= number_format($totalReplies) ?></strong> Replies</span>
                                </span>
                            </p>
                            <p>
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

            <!-- Main Content -->
            <div class="column is-9">
                <div class="box mb-4">
                    <h2 class="title is-4">
                        <i class="fas fa-layer-group"></i> Forum Categories
                    </h2>
                    <p class="subtitle is-6">Browse discussions by topic</p>
                </div>

                <?php foreach ($subforums as $id => $subforum): ?>
                    <?php 
                    $threadCount = getThreadCount($db, $id);
                    $latestPost = getLatestPost($db, $id);
                    ?>
                    <div class="box mb-4">
                        <article class="media">
                            <figure class="media-left">
                                <span class="icon is-large has-text-info">
                                    <i class="<?= $subforum['icon'] ?> fa-2x"></i>
                                </span>
                            </figure>
                            <div class="media-content">
                                <div class="content">
                                    <p>
                                        <strong class="is-size-5">
                                            <a href="/forum/topic/<?= $id ?>"><?= htmlspecialchars($subforum['name']) ?></a>
                                        </strong>
                                        <br>
                                        <span class="has-text-grey"><?= htmlspecialchars($subforum['desc']) ?></span>
                                        <br>
                                        <span class="tag is-info is-light mt-2">
                                            <i class="fas fa-folder"></i>&nbsp;&nbsp;<?= number_format($threadCount) ?> thread<?= $threadCount != 1 ? 's' : '' ?>
                                        </span>
                                    </p>
                                    
                                    <?php if ($latestPost): ?>
                                        <div class="box is-shadowless has-background-light mt-3">
                                            <p class="is-size-7">
                                                <strong><i class="fas fa-clock"></i> Latest Activity:</strong>
                                                <br>
                                                <a href="/forum/post/<?= $latestPost->id ?>" class="has-text-weight-semibold">
                                                    <?= htmlspecialchars($latestPost->title) ?>
                                                </a>
                                                <br>
                                                by <a href="/user/<?= $latestPost->author ?>"><?= htmlspecialchars($latestPost->username) ?></a>
                                                <span class="has-text-grey">• <?= timeAgo($latestPost->lastposttime) ?></span>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <div class="box is-shadowless has-background-light mt-3">
                                            <p class="is-size-7 has-text-grey-light">
                                                <i class="fas fa-inbox"></i> No posts yet. Be the first to start a discussion!
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php $app->footer(); ?>