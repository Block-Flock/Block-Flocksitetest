<?php
$title = "Forum";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

// Allow guests but protect against abuse
$app->checkAuth(false);

if (!$AUTH && !$app->rateLimitGuest('forum_view')) {
    http_response_code(429);
    die('Too many requests. Please try again later.');
}

/* ==========================================================================
   SUBFORUM DEFINITIONS
   ========================================================================== */
$subforums = [
    1 => ['name' => 'Announcements', 'desc' => 'Updates and announcements from staff', 'icon' => 'fas fa-bullhorn'],
    2 => ['name' => 'General Discussion', 'desc' => 'General discussion about Bricktaria', 'icon' => 'fas fa-comments'],
    3 => ['name' => 'Off-Topic', 'desc' => 'Non-Bricktaria related discussions', 'icon' => 'fas fa-coffee'],
    4 => ['name' => 'Marketplace', 'desc' => 'Promote your creations here', 'icon' => 'fas fa-store'],
    5 => ['name' => 'Bug Reports', 'desc' => 'Report technical issues here', 'icon' => 'fas fa-bug'],
    6 => ['name' => 'Games Creation', 'desc' => 'Share your game creations', 'icon' => 'fas fa-gamepad']
];

$subIds = array_keys($subforums);

/* ==========================================================================
   THREAD COUNTS (BATCHED)
   ========================================================================== */
$threadCounts = array_fill_keys($subIds, 0);

$stmt = $db->query("
    SELECT subforum, COUNT(*) AS cnt
    FROM forum_posts
    GROUP BY subforum
");
while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    if (isset($threadCounts[$row->subforum])) {
        $threadCounts[$row->subforum] = (int)$row->cnt;
    }
}

/* ==========================================================================
   LATEST POSTS PER SUBFORUM (BATCHED)
   ========================================================================== */
$latestPosts = [];

$stmt = $db->query("
    SELECT fp.id, fp.title, fp.author, fp.subforum, fp.lastposttime,
           u.username
    FROM forum_posts fp
    JOIN users u ON u.id = fp.author
    INNER JOIN (
        SELECT subforum, MAX(lastposttime) AS max_time
        FROM forum_posts
        GROUP BY subforum
    ) x ON x.subforum = fp.subforum AND x.max_time = fp.lastposttime
");

while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    $latestPosts[$row->subforum] = $row;
}
?>

<div class="container forum-container">

    <div class="card forum-card">
        <!-- HEADER -->
        <div class="top blue forum-header">
            <div class="col-7-12">Bricktaria Forums</div>
            <div class="col-1-12 center">Threads</div>
            <div class="col-4-12">Last Post</div>
        </div>

        <!-- BODY -->
        <div class="content forum-list">

            <?php foreach ($subforums as $id => $forum): ?>
                <?php
                    $threadCount = $threadCounts[$id] ?? 0;
                    $last = $latestPosts[$id] ?? null;
                ?>

                <div class="board-row">
                    <!-- LEFT -->
                    <div class="col-7-12">
                        <div class="board-title">
                            <i class="<?= $forum['icon'] ?>"></i>
                            <a href="/forum/topic/<?= $id ?>">
                                <?= htmlspecialchars($forum['name']) ?>
                            </a>
                        </div>
                        <div class="board-description">
                            <?= htmlspecialchars($forum['desc']) ?>
                        </div>
                    </div>

                    <!-- THREAD COUNT -->
                    <div class="col-1-12 center board-count">
                        <?= number_format($threadCount) ?>
                    </div>

                    <!-- LAST POST -->
                    <div class="col-4-12">
                        <?php if ($last): ?>
                            <div class="board-last-post">
                                <a class="last-post-title" href="/forum/post/<?= $last->id ?>">
                                    <?= htmlspecialchars($last->title) ?>
                                </a>
                                <div class="last-post-meta">
                                    by <a href="/user/<?= $last->author ?>">
                                        <?= htmlspecialchars($last->username) ?>
                                    </a>
                                    <span class="time"><?= timeAgo($last->lastposttime) ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="board-last-post muted">
                                No posts yet
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>
    </div>
</div>

<?php $app->footer(); ?>
