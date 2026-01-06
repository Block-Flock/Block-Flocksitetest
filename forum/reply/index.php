<?php
$title = "Reply to Thread";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if (!$AUTH) {
    header('Location: /log-in');
    die();
}

// Validate thread ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /forum');
    die();
}

$threadId = (int)$_GET['id'];

// Get thread data
$getThread = $db->prepare('SELECT fp.*, u.username FROM forum_posts fp JOIN users u ON fp.author = u.id WHERE fp.id = ?');
$getThread->bindValue(1, $threadId, PDO::PARAM_INT);
$getThread->execute();

if ($getThread->rowCount() == 0) {
    header('Location: /forum');
    die();
}

$thread = $getThread->fetch(PDO::FETCH_OBJ);

// Check if thread is locked
if ($thread->locked == 1 && $my->administrator < 1) {
    header('Location: /forum/post/' . $threadId);
    die();
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

$err = '';
$success = false;

// Handle form submission
if ($AUTH && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    
    // Validation
    if (strlen($content) < 3) {
        $err = 'Your reply must be at least 3 characters long.';
    } elseif (strlen($content) > 2000) {
        $err = 'Your reply cannot be longer than 2000 characters.';
    } elseif ($my->flood > time()) {
        $err = 'Please wait ' . ($my->flood - time()) . ' seconds before posting again.';
    } elseif ($my->verified == 0) {
        $err = 'Please verify your account to reply to this thread.';
    } else {
        // Insert the reply
        $insert = $db->prepare('INSERT INTO forum_replies (content, author, thread, post_id, timemade) VALUES (?, ?, ?, ?, NOW())');
        $insert->bindValue(1, $content, PDO::PARAM_STR);
        $insert->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $insert->bindValue(3, $threadId, PDO::PARAM_INT);
        $insert->bindValue(4, $threadId, PDO::PARAM_INT);
        $insert->execute();
        
        // Update thread's last post info - using Unix timestamp
        $updateThread = $db->prepare('UPDATE forum_posts SET lastposttime = ?, lastpostauthor = ? WHERE id = ?');
        $updateThread->bindValue(1, time(), PDO::PARAM_INT);
        $updateThread->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $updateThread->bindValue(3, $threadId, PDO::PARAM_INT);
        $updateThread->execute();
        
        // Add forum XP
        $updateXP = $db->prepare('UPDATE users SET forum_xp = forum_xp + ? WHERE id = ?');
        $updateXP->bindValue(1, rand(2, 8), PDO::PARAM_INT);
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

// Handle quote functionality
$quoteText = '';
if (isset($_GET['quote']) && is_numeric($_GET['quote'])) {
    $quoteId = (int)$_GET['quote'];
    $getQuote = $db->prepare('SELECT fr.*, u.username FROM forum_replies fr JOIN users u ON fr.author = u.id WHERE fr.id = ?');
    $getQuote->bindValue(1, $quoteId, PDO::PARAM_INT);
    $getQuote->execute();
    
    if ($getQuote->rowCount() > 0) {
        $quote = $getQuote->fetch(PDO::FETCH_OBJ);
        $quoteText = "[quote=" . htmlspecialchars($quote->username) . "]" . htmlspecialchars($quote->content) . "[/quote]\n\n";
    }
}
?>

<section class="hero is-small is-bold">
    <div class="hero-body">
        <div class="container">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="/forum">Forum</a></li>
                    <li><a href="/forum/topic/<?= $thread->subforum ?>"><?= htmlspecialchars($subforum['name']) ?></a></li>
                    <li><a href="/forum/post/<?= $threadId ?>"><?= htmlspecialchars($thread->title) ?></a></li>
                    <li class="is-active"><a href="#" aria-current="page">Reply</a></li>
                </ul>
            </nav>
            
            <div class="columns is-centered">
                <div class="column is-8">
                    <h1 class="title">Reply to Thread</h1>
                    <h2 class="subtitle"><?= htmlspecialchars($thread->title) ?></h2>
                    <p class="subtitle is-6">Started by <?= htmlspecialchars($thread->username) ?></p>
                </div>
            </div>
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
                            <label class="label">Your Reply</label>
                            <div class="control">
                                <textarea class="textarea" name="content" placeholder="Write your reply here..." 
                                          rows="8" maxlength="2000" required><?= htmlspecialchars($quoteText . (isset($_POST['content']) ? $_POST['content'] : '')) ?></textarea>
                            </div>
                            <p class="help">3-2000 characters. You can use basic formatting.</p>
                        </div>
                        
                        <div class="field">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-reply"></i> Post Reply
                                </button>
                                <a href="/forum/post/<?= $threadId ?>" class="button is-light">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="box">
                    <h3 class="title is-5">Reply Guidelines</h3>
                    <div class="content">
                        <ul>
                            <li>Keep your reply relevant to the thread topic</li>
                            <li>Be respectful to other users</li>
                            <li>Use clear, constructive language</li>
                            <li>No spam, advertising, or inappropriate content</li>
                            <li>Replies are moderated by staff</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $app->footer(); ?>










