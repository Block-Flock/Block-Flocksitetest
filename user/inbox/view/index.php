<?php
$title = "View Message";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    http_response_code(404);
    die;

}

if(!$_GET['id']){
	header('Location: ../received');
}
$message = $db->prepare('SELECT * FROM user_inboxes WHERE id = ?');
$message->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$message->execute();
$m = $message->fetch(PDO::FETCH_OBJ);
$userinfo = $db->prepare('SELECT user_inboxes.*, users.* FROM user_inboxes JOIN users WHERE user_inboxes.sender = users.id AND user_inboxes.id = ?');
$userinfo->bindValue(1, $m->id, PDO::PARAM_INT);
$userinfo->execute();
$uI = $userinfo->fetch(PDO::FETCH_OBJ);

if($_SESSION['id'] == $m->recipient){
$readMessage = $db->prepare('UPDATE user_inboxes SET hasread = 1 WHERE id = ?');
$readMessage->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$readMessage->execute();
}

if($m->recipient !== $_SESSION['id']){
	if($m->sender !== $_SESSION['id']){
		header('Location: ../received');
	}
}

?>
<section class="section">
<div class="container">
<div class="columns">
<div class="column is-3">
</div>
<div class="column is-10">
<nav class="breadcrumb is-centered has-succeeds-separator" aria-label="breadcrumbs">
<ul>
<li><a href="/user/inbox/received/">Inbox</a></li>
<li><a>View</a></li>
<li><a href="/user/<?php echo htmlspecialchars($uI->id); ?>"><?php echo htmlspecialchars($uI->username); ?></a></li>
</ul>
</nav>
<div class="box" style="word-break: break-all;">
<p style="color:gray; font-size: 10px; position: absolute; right: -29px;"><?php echo htmlspecialchars($m->timesent); ?></p>
<article class="media">
<figure class="media-left">
<a href="/user/<?=$uI->id ?>"><img width="170" onclick="location.href='/user/<?php echo htmlspecialchars($uI->id); ?>'" src="/assets/images/avatars/<?php echo htmlspecialchars($uI->avatar_hash);?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>" onerror="this.src='/assets/images/avatars/default.png?r=b2da1bda704104c93161b836e2ab5b64'" alt="Could Not Get Profile Image!"></a>
<p class="is-fullwidth has-text-centered">Join Date: <?php echo date('d/m/Y', $uI->joined); ?></p>
</figure>
<div class="media-content">
<div class="content">
<p class="has-text-white" style="font-size: 20px;"><?php echo htmlentities($m->subject); ?></p>
<hr>
<p><?php echo htmlentities($m->content); ?></p>
</div>
</div>
</article>
</div>
</div>
</div>
<div style="margin-bottom: 10px;"></div>
<div class="buttons" style="justify-content: center;"><a href="/user/inbox/send/?id=<?php echo htmlspecialchars($m->sender); ?>&context=Reply: <?php echo htmlspecialchars($m->subject); ?>" class="button is-info">Reply</a></div>
</div>
<div class="column"></div>
</div>
</div>
</section>
<?php
$app->footer();
?>








