<?php
$title = "Create Thread";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

/**
 * @var PDO $db
 * @var object $my
 * @var bool $AUTH
 * @var object $app
 */

if (!$AUTH) {
    header('Location: /log-in');
    die();
}

// Subforum metadata (Hardcoded as requested for now)
$subforumData = [
    1 => ['name' => 'Announcements', 'desc' => 'Updates and announcements from staff', 'admin_only' => true],
    2 => ['name' => 'General Discussion', 'desc' => 'General discussion about Bricktaria', 'admin_only' => false],
    3 => ['name' => 'Off-Topic', 'desc' => 'Non-Bricktaria related discussions', 'admin_only' => false],
    4 => ['name' => 'Marketplace', 'desc' => 'Promote your creations here', 'admin_only' => false],
    5 => ['name' => 'Bug Reports', 'desc' => 'Report technical issues here', 'admin_only' => false],
    6 => ['name' => 'Games Creation', 'desc' => 'Share your game creations', 'admin_only' => false]
];

$sub = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$showSelection = false;
$info = null;

if ($sub === 0 || !isset($subforumData[$sub])) {
    $showSelection = true;
} else {
    $info = $subforumData[$sub];
    // Check if user can post in this subforum
    if ($info['admin_only'] && $my->administrator < 1) {
        header('Location: /forum');
        die();
    }
}

$err = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['body'])) {
    try {
        // CSRF Protection
        if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['CSRF-TOKEN']) {
            throw new Exception('Invalid CSRF token. Please refresh the page and try again.');
        }

        $title = trim($_POST['title']);
        $body = trim($_POST['body']);
        $selectedSub = isset($_POST['subforum_id']) ? (int)$_POST['subforum_id'] : $sub;

        // Validation
        if (!isset($subforumData[$selectedSub])) {
            throw new Exception('Invalid subforum selected.');
        }

        $subInfo = $subforumData[$selectedSub];
        if ($subInfo['admin_only'] && $my->administrator < 1) {
            throw new Exception('You do not have permission to post in this subforum.');
        }

        if (strlen($title) < 3) {
            throw new Exception('Your thread title must be at least 3 characters long.');
        } elseif (strlen($title) > 100) {
            throw new Exception('Your thread title cannot be longer than 100 characters.');
        } elseif (strlen($body) < 10) {
            throw new Exception('Your thread content must be at least 10 characters long.');
        } elseif (strlen($body) > 5000) {
            throw new Exception('Your thread content cannot be longer than 5000 characters.');
        } elseif ($my->flood > time()) {
            throw new Exception('Please wait ' . ($my->flood - time()) . ' seconds before creating another thread.');
        } elseif ($my->verified == 0) {
            throw new Exception('Please verify your account to create a thread.');
        }

        // Database Operations in a Transaction
        $db->beginTransaction();

        // 1. Insert the thread (including lastpostid calculation via subquery or updating after)
        // Since lastpostid is the ID of the post itself for a new thread, we still need the ID first.
        // However, we can optimize by doing it in one transaction.
        $insert = $db->prepare('INSERT INTO forum_posts (title, content, author, subforum, lastpostauthor, lastposttime, timemade) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $now = time();
        $insert->execute([$title, $body, $_SESSION['id'], $selectedSub, $_SESSION['id'], $now, $now]);
        $threadId = $db->lastInsertId();

        // 2. Update lastpostid on the newly created thread
        $updateId = $db->prepare('UPDATE forum_posts SET lastpostid = ? WHERE id = ?');
        $updateId->execute([$threadId, $threadId]);

        // 3. Consolidated User Update (XP and Flood Protection)
        $xpGain = rand(5, 15);
        $newFlood = time() + 30;
        $updateUser = $db->prepare('UPDATE users SET forum_xp = forum_xp + ?, flood = ? WHERE id = ?');
        $updateUser->execute([$xpGain, $newFlood, $_SESSION['id']]);

        $db->commit();

        header('Location: /forum/post/' . $threadId);
        die();

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $err = $e->getMessage();
    }
}
?>

<section class="hero is-small is-bold">
    <div class="hero-body">
        <div class="container has-text-centered">
            <h1 class="title">Create New Thread</h1>
            <?php if (!$showSelection): ?>
                <h2 class="subtitle">Posting in: <?= htmlspecialchars($info['name']) ?></h2>
                <p class="subtitle is-6"><?= htmlspecialchars($info['desc']) ?></p>
            <?php else: ?>
                <h2 class="subtitle">Select a subforum to start a discussion</h2>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-8">
                <?php if ($err): ?>
                    <div class="notification is-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($err) ?>
                    </div>
                <?php endif; ?>

                <div class="box">
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        
                        <?php if ($showSelection): ?>
                            <div class="field">
                                <label class="label">Subforum</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="subforum_id" required>
                                            <option value="" disabled selected>Select a subforum...</option>
                                            <?php foreach ($subforumData as $id => $data): ?>
                                                <?php if (!$data['admin_only'] || $my->administrator >= 1): ?>
                                                    <option value="<?= $id ?>" <?= (isset($_POST['subforum_id']) && $_POST['subforum_id'] == $id) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($data['name']) ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="field">
                            <label class="label">Thread Title</label>
                            <div class="control">
                                <input class="input" type="text" name="title" placeholder="Enter your thread title..." 
                                       value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" 
                                       maxlength="100" required>
                            </div>
                            <p class="help">3-100 characters</p>
                        </div>
                        
                        <div class="field">
                            <label class="label">Content</label>
                            <div class="control">
                                <textarea class="textarea" name="body" placeholder="Write your thread content here..." 
                                          rows="10" maxlength="5000" required><?= isset($_POST['body']) ? htmlspecialchars($_POST['body']) : '' ?></textarea>
                            </div>
                            <p class="help">10-5000 characters. You can use basic formatting.</p>
                        </div>
                        
                        <div class="field">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-paper-plane"></i> Create Thread
                                </button>
                                <?php if (!$showSelection): ?>
                                    <a href="/forum/topic/<?= $sub ?>" class="button is-light">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </a>
                                <?php else: ?>
                                    <a href="/forum" class="button is-light">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="box">
                    <h3 class="title is-5">Thread Guidelines</h3>
                    <div class="content">
                        <ul>
                            <li>Keep discussions respectful and on-topic</li>
                            <li>Use clear, descriptive titles</li>
                            <li>Check if your topic has already been discussed</li>
                            <li>No spam, advertising, or inappropriate content</li>
                            <li>Threads are moderated by staff</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $app->footer(); ?>
