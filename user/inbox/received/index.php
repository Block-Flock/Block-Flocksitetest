<?php
$title = "Received";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    http_response_code(404);
    die;

}

$elimit = 5;

if(!isset($_GET['page'])) {
    $page = 1;
} else if(isset($_GET['page'])) {
    $page = $_GET['page'];
}

$offset = ($page-1) * $elimit;

if($offset < 0) { $offset = 0; }

$receivedMessages = $db->prepare('SELECT * FROM user_inboxes WHERE recipient = ? AND hasread = 0 AND archived = 0 ORDER BY timesent DESC LIMIT ? OFFSET ?');
$receivedMessages->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$receivedMessages->bindValue(2, $elimit, PDO::PARAM_INT);
$receivedMessages->bindValue(3, $offset, PDO::PARAM_INT);
$receivedMessages->execute();

$receivedMessages2 = $db->prepare('SELECT * FROM user_inboxes WHERE recipient = ? AND hasread = 0 AND archived = 0 ORDER BY timesent LIMIT ?');
$receivedMessages2->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$receivedMessages2->bindValue(2, $elimit, PDO::PARAM_INT);
$receivedMessages2->execute();

$pages = ceil($receivedMessages2->rowCount() / ($elimit-3) );
if($pages == 2 && $receivedMessages2->rowCount() < $elimit + 1){
	$pages = 1;
}

$totalMessages = $db->prepare('SELECT * FROM user_inboxes WHERE recipient = ? OR sender = ?');
$totalMessages->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$totalMessages->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
$totalMessages->execute();
$unreadMessages = $db->prepare('SELECT * FROM user_inboxes WHERE recipient = ? AND hasread = 0');
$unreadMessages->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$unreadMessages->execute();
$archivedMessages = $db->prepare('SELECT * FROM user_inboxes WHERE recipient = ? OR sender = ? AND archived = 1');
$archivedMessages->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$archivedMessages->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
$sentMessages = $db->prepare('SELECT * FROM user_inboxes WHERE sender = ?');
$sentMessages->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$sentMessages->execute();

if(isset($_POST['readall'])) {
  $readAllreceivedMessages = $db->prepare('UPDATE user_inboxes SET hasread = 1 WHERE recipient = ?');
  $readAllreceivedMessages->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
  $readAllreceivedMessages->execute();

  //$readAllreceivedMessages = $db->prepare('UPDATE user_inboxes SET hasread = 1 WHERE recipient = ? AND id BETWEEN ? AND ?');
  //$readAllreceivedMessages->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
  //$readAllreceivedMessages->bindValue(2, $offset, PDO::PARAM_INT);
  //$readAllreceivedMessages->bindValue(3, $offset + $elimit, PDO::PARAM_INT);
  //$readAllreceivedMessages->execute();

  header('Location: ../received/?page='.$_GET['page']);
}

?>
<section class="section">
<div class="container">
<div class="columns">
<div class="column is-2">
</div>
<div class="column is-2">
<aside class="menu">
<form method="post">
<button name="readall" type="submit" class="button is-info" style="width: 100%;">Read All</button>
</form>
<div class="push-5"></div>
<p class="menu-label">Messages</p>
<ul class="menu-list">
<li><a  class="nav-link is-active" href="../received/?page=1">Received</a></li>
<li><a class="nav-link" href="../sent/?page=1">Sent</a></li>
<li><a class="nav-link" href="../read/?page=1">Read</a></li>
</ul>
</div>
</aside>
<div class="column is-7">
<?php while($rM = $receivedMessages->fetch(PDO::FETCH_OBJ)){ 
	$userinfo = $db->prepare('SELECT user_inboxes.*, users.* FROM user_inboxes JOIN users WHERE user_inboxes.sender = users.id AND user_inboxes.id = ?');
    $userinfo->bindValue(1, $rM->id, PDO::PARAM_INT);
    $userinfo->execute();
    $uI = $userinfo->fetch(PDO::FETCH_OBJ);
    ?>
<div class="box wall_post" style="word-break: break-all;">
<article class="media">
<figure class="media-left">
<div class="avatar_dashboard_friend_container">
                                    <div class="avatar_user avatar_dashboard_friend">
<img  width="45" height="45" src="/assets/images/avatars/headshots/<?php echo htmlspecialchars($uI->avatar_hash); ?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>">
</figure>
<div style="cursor: pointer;" onclick="location.href='../view/<?php echo htmlspecialchars($rM->id); ?>'"
<div class="media-content wall_post_content">
<div class="content">
<strong><a  class="username" href="/user/<?php echo htmlspecialchars($uI->id); ?>"><?php echo htmlspecialchars($uI->username); ?></a></strong>&nbsp;<span class="has-text-grey-light">&nbsp; <i class="fas fa-clock"></i>&nbsp;<?php echo htmlspecialchars($rM->timesent); ?></small></span>
<br>
<p class="usercontent"><?php echo htmlspecialchars($rM->subject); ?></p>
</div>
</div>
</div>
<?php } ?>
<?php if($pages > 1) { ?>
<nav class="pagination is-centered" role="navigation" aria-label="pagination">
   <a class="pagination-previous" <?php if($page - 1 == 0) { echo ' style="visibility: hidden;"'; } else { echo 'href="../receieved?page='.($page - 1).'"'; } ?>>Previous</a>
   <a class="pagination-next" <?php if($page + 1 > $pages) { echo 'style="visibility: hidden;"'; } else { echo 'href="../received?page='.($page + 1).'"'; } ?>>Next page</a>
   <ul class="pagination-list">
      <?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {
         if ($i <= $pages) { echo '
         <li><a ';if($i != $page){echo'href="../received?page='.$i.'"';}echo'class="pagination-link'; if($i == $page){echo' is-current';}echo'" aria-label="Goto page '.$i.'">'.$i.'</a></li>'; } } ?>
   </ul>
</nav>
<?php } ?>
</div>
</section>
<?php
$app->footer();
?>