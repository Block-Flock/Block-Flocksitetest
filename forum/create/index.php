<?php
$title = "Create Thread";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if (!$AUTH) {
    header('Location: /log-in');
    die();
}

// Validate subforum ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /forum');
    die();
}

$sub = (int)$_GET['id'];

// Subforum metadata
$subforumData = [
    1 => ['name' => 'Announcements', 'desc' => 'Updates and announcements from staff', 'admin_only' => true],
    2 => ['name' => 'General Discussion', 'desc' => 'General discussion about Bricktaria', 'admin_only' => false],
    3 => ['name' => 'Off-Topic', 'desc' => 'Non-Bricktaria related discussions', 'admin_only' => false],
    4 => ['name' => 'Marketplace', 'desc' => 'Promote your creations here', 'admin_only' => false],
    5 => ['name' => 'Bug Reports', 'desc' => 'Report technical issues here', 'admin_only' => false],
    6 => ['name' => 'Games Creation', 'desc' => 'Share your game creations', 'admin_only' => false]
];

if (!isset($subforumData[$sub])) {
    header('Location: /forum');
    die();
}

$info = $subforumData[$sub];

// Check if user can post in this subforum
if ($info['admin_only'] && $my->administrator < 1) {
    header('Location: /forum');
    die();
}

$err = '';
$success = false;

// Handle form submission
if ($AUTH && isset($_POST['title']) && isset($_POST['body'])) {
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);
    
    // Validation
    if (strlen($title) < 3) {
        $err = 'Your thread title must be at least 3 characters long.';
    } elseif (strlen($title) > 100) {
        $err = 'Your thread title cannot be longer than 100 characters.';
    } elseif (strlen($body) < 10) {
        $err = 'Your thread content must be at least 10 characters long.';
    } elseif (strlen($body) > 5000) {
        $err = 'Your thread content cannot be longer than 5000 characters.';
    } elseif ($my->flood > time()) {
        $err = 'Please wait ' . ($my->flood - time()) . ' seconds before creating another thread.';
    } elseif ($my->verified == 0) {
        $err = 'Please verify your account to create a thread.';
    } else {
        // Insert the thread
        $insert = $db->prepare('INSERT INTO forum_posts (title, content, author, subforum, lastpostauthor, lastposttime, timemade) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insert->bindValue(1, $title, PDO::PARAM_STR);
        $insert->bindValue(2, $body, PDO::PARAM_STR);
        $insert->bindValue(3, $_SESSION['id'], PDO::PARAM_INT);
        $insert->bindValue(4, $sub, PDO::PARAM_INT);
        $insert->bindValue(5, $_SESSION['id'], PDO::PARAM_INT);
        $insert->bindValue(6, time(), PDO::PARAM_INT);
        $insert->bindValue(7, time(), PDO::PARAM_INT);
        $insert->execute();

        $threadId = $db->lastInsertId();

        // Update lastpostid
        $updateId = $db->prepare('UPDATE forum_posts SET lastpostid = ? WHERE id = ?');
        $updateId->bindValue(1, $threadId, PDO::PARAM_INT);
        $updateId->bindValue(2, $threadId, PDO::PARAM_INT);
        $updateId->execute();

        // Add forum XP
        $updateXP = $db->prepare('UPDATE users SET forum_xp = forum_xp + ? WHERE id = ?');
        $updateXP->bindValue(1, rand(5, 15), PDO::PARAM_INT);
        $updateXP->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $updateXP->execute();

        // Set flood protection
        $updateFlood = $db->prepare('UPDATE users SET flood = ? WHERE id = ?');
        $updateFlood->bindValue(1, time() + 30, PDO::PARAM_INT);
        $updateFlood->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $updateFlood->execute();

        header('Location: /forum/post/' . $threadId);
        die();
    }
}
?>

<section class="hero is-small is-bold">
    <div class="hero-body">
        <div class="container has-text-centered">
            <h1 class="title">Create New Thread</h1>
            <h2 class="subtitle">Posting in: <?= htmlspecialchars($info['name']) ?></h2>
            <p class="subtitle is-6"><?= htmlspecialchars($info['desc']) ?></p>
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
                                <a href="/forum/topic/<?= $sub ?>" class="button is-light">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
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








