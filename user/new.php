<?php
$title = "Profile";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['p'])) $_GET['p'] = 1;
if (!isset($_GET["id"]) || !is_numeric($_GET['id']) || empty($_GET['id']) || empty($_GET['p'])) {
	require("/errors/404.php");
	die();
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!is_numeric($_GET['id']) || !is_numeric($_GET['p']))
{
	die();
}

$id = (int) $_GET['id'];

$pastUsernames = $db->prepare('SELECT * FROM user_usernames WHERE owner = ?');
$pastUsernames->bindValue(1, $id, PDO::PARAM_INT);
$pastUsernames->execute();

$selectProfile = $db->prepare('SELECT * FROM users WHERE id = ?');
$selectProfile->bindValue(1, $id, PDO::PARAM_INT);
$selectProfile->execute();

if($selectProfile->rowCount() == 0) {

    header('Location: /');
    die;

}

$gP = $selectProfile->fetch(PDO::FETCH_OBJ); 

if($AUTH) {

 $selectfr = $db->prepare('SELECT * FROM user_friends WHERE receiver = ? AND sender = ? AND `status` = 0 OR sender = ? AND receiver = ? AND `status` = 0');
 $selectfr->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
 $selectfr->bindValue(2, $_GET['id'], PDO::PARAM_INT);
 $selectfr->bindValue(3, $_SESSION['id'], PDO::PARAM_INT);
 $selectfr->bindValue(4, $_GET['id'], PDO::PARAM_INT);
 $selectfr->execute();

 $selectBan = $db->prepare('SELECT * FROM user_bans WHERE user = ? AND expired  = 0');
 $selectBan->bindValue(1, $_GET['id'], PDO::PARAM_INT);
 $selectBan->execute();
  
 

 $selectFriends3 = $db->prepare("(SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.receiver = users.id WHERE user_friends.status = 0 AND user_friends.sender = ? ORDER BY user_friends.`date` DESC) UNION ALL (SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.sender = users.id WHERE user_friends.receiver = ? AND user_friends.status = 1 ORDER BY user_friends.`date` DESC) LIMIT 4");
 $selectFriends3->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
 $selectFriends3->bindValue(2, $_GET['id'], PDO::PARAM_INT);
 $selectFriends3->execute();
 
 $selectFriends4 = $db->prepare("(SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.receiver = users.id WHERE user_friends.status = 2 AND user_friends.sender = ? ORDER BY user_friends.`date` DESC) UNION ALL (SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.sender = users.id WHERE user_friends.receiver = ? AND user_friends.status = 1 ORDER BY user_friends.`date` DESC) LIMIT 4");
 $selectFriends4->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
 $selectFriends4->bindValue(1, $_GET['id'], PDO::PARAM_INT);
 $selectFriends4->execute();

 if($selectfr->rowCount() >= 1) {

     $friends = 0;

 } else if($selectfr->rowCount() == 0) {


     $selectfr2 = $db->prepare('SELECT * FROM user_friends WHERE sender = ? AND `status` = 1 AND receiver = ? OR receiver = ? AND sender = ? AND `status` = 1');
     $selectfr2->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
     $selectfr2->bindValue(2, $_GET['id'], PDO::PARAM_INT);
     $selectfr2->bindValue(3, $_SESSION['id'], PDO::PARAM_INT);
     $selectfr2->bindValue(4, $_GET['id'], PDO::PARAM_INT);
     $selectfr2->execute();

     if($selectfr2->rowCount() == 1) {

         $friends = 1;

     }

 }
}

$postCount = $db->prepare('SELECT * FROM forum_posts WHERE author = ?');
$postCount->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$postCount->execute();

$postCountReply = $db->prepare('SELECT * FROM forum_replies WHERE author = ?');
$postCountReply->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$postCountReply->execute();

$numPosts = $postCount->rowCount() + $postCountReply->rowCount();

$selectBan = $db->prepare('SELECT * FROM user_bans WHERE user = ? AND expired  = 0');
$selectBan->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$selectBan->execute();

if(isset($_POST['send']) && $AUTH && $selectProfile->rowCount() >= 1 && !isset($friends)) {

 if($_SESSION['id'] == $_GET['id']) { die("Cannot friend yourself"); }
 $insert = $db->prepare("INSERT INTO user_friends (receiver, sender, `date`) VALUES (?, ?, ?)");
 $insert->bindValue(1, $_GET['id'],PDO::PARAM_INT);
 $insert->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
 $insert->bindValue(3, time(), PDO::PARAM_INT);
 $insert->execute();

 header("Location: /user/profile/?id=".$_GET['id']);
 die;

}

if(isset($_POST['remove']) && $AUTH && $selectProfile->rowCount() >= 1 && $friends == 1) {

 if($_SESSION['id'] == $_GET['id']) { die("Cannot unfriend yourself"); }

 $del = $db->prepare("DELETE FROM user_friends WHERE sender = ? AND receiver = ? OR receiver = ? AND sender = ?");
 $del->bindValue(1, $_GET['id'], PDO::PARAM_INT);
 $del->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
 $del->bindValue(3, $_GET['id'], PDO::PARAM_INT);
 $del->bindValue(4, $_SESSION['id'], PDO::PARAM_INT);
 $del->execute();

 header("Location: /user/profile/?id=".$_GET['id']);
 die;

}

if (isset($_POST['reRenderAvatar']) && $AUTH) {
  $update = $db->prepare('UPDATE users SET avatar_hash = "default" WHERE id = ?'); 
  $update->bindValue(1, $_GET['id'], PDO::PARAM_INT);
  $update->execute();
  
$update = $db->prepare('UPDATE user_data SET hat1 = "0",hat2 = "0", hat3 = "0", face = "0", shirt = "0", pants = "0", gear = "0" WHERE id = ?'); 
  $update->bindValue(1, $_GET['id'], PDO::PARAM_INT);
  $update->execute();

  context::sendDiscordAdminLogs($my->username.' has re rendered the avatar for user ID **'.$_GET['id'].'**');
}

if(isset($_POST['accept']) && isset($_GET['id']) && $AUTH) {
    $getprofile = $db->prepare("SELECT * FROM users WHERE id = ?");
    $getprofile->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $getprofile->execute();

    if($getprofile->rowCount() == 0) {
        header("Location: /user/dashboard");
        die();
    }

    $accept = $db->prepare("UPDATE user_friends SET `status` = 1  WHERE sender = ? AND receiver = ? AND `status` = 0");
    $accept->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $accept->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $accept->execute();
	
	header("Location: /user/profile/?id=".$_GET['id']);
    die;
}

if(isset($_POST['decline']) && isset($_GET['id']) && $AUTH) {
    $getprofile = $db->prepare("SELECT * FROM users WHERE id = ?");
    $getprofile->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $getprofile->execute();

    if($getprofile->rowCount() == 0) {
        header("Location: /user/dashboard");
        die();
    }

    $decline = $db->prepare("DELETE FROM user_friends WHERE sender = ? AND receiver = ? AND `status` = 0");
    $decline->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $decline->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $decline->execute();
	
	header("Location: /user/profile/?id=".$_GET['id']);
    die;
}

$selectFriends = $db->prepare("(SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.receiver = users.id WHERE user_friends.status = 1 AND user_friends.sender = ? ORDER BY user_friends.`date` DESC) UNION ALL (SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.sender = users.id WHERE user_friends.receiver = ? AND user_friends.status = 1 ORDER BY user_friends.`date` DESC) LIMIT 4");
$selectFriends->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$selectFriends->bindValue(2, $_GET['id'], PDO::PARAM_INT);
$selectFriends->execute();
?>

<!DOCTYPE html>
<html>
    <body>

<div class="columns">

<div class="column">

<div class="card">
  <header class="card-header">
    <p class="card-header-title has-text-white">
      <?=$gP->username;?>'s Profile
    </p>
    <a class="card-header-icon" href="/user/report/?id=<?=$_GET['id'];?>" aria-label="Report user">
      <span class="icon has-text-danger">
      <i class="fa-solid fa-flag" aria-hidden="true"></i>
      </span>
    </a>
  </header>
  <div class="card-content">
    <div class="content">
    <img src="/assets/images/avatars/<?php echo $gP->avatar_hash; ?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>" onerror="this.src='/assets/images/avatars/default.png?r=<?php echo bin2hex(random_bytes(16)); ?>'" alt="">
    </div>
  </div>

  <?php if($selectBan->rowCount() >= 1): ?>
<span class="tag is-danger is-fullwidth" style="border-radius: 0px;">This user has been suspended</span>
<?php endif; ?>

  <?php if($AUTH  && $_GET['id'] != $_SESSION['id']): ?>
                  <?php endif; ?>
    <?php if(!isset($friends) && $AUTH && $_GET['id'] != $_SESSION['id'] && $selectBan->rowCount() < 1): ?>
  <form method="post">

  <button type="submit" name="send" class="button is-fullwidth is-success" style="border-radius: 0px;"><i class="fas fa-user-plus"></i></button>
  <?php endif; ?>

  <?php if(isset($friends) && $AUTH && $selectfr->rowCount() == 1 && $friends == 0 && $_GET['id'] != $_SESSION['id']): ?>
  <button name="send" class="button is-fullwidth is-dark" style="border-radius: 0px;"><i class="fas fa-clock"></i>&nbsp;</button>
  <?php endif; ?>

  <?php if(isset($friends) && $AUTH && $selectFriends4->rowCount() >= 1 && $friends == 1 && $_GET['id'] != $_SESSION['id'] OR isset($friends) && $AUTH && $selectFriends3->rowCount() == 1 && $friends == 1 && $_GET['id'] != $_SESSION['id']): ?>
  <button type="submit" name="remove" class="button is-fullwidth is-danger" style="border-radius: 0px;"><i class="fas fa-user-times"></i>></button>
  <?php endif; ?>

  <?php if($AUTH && $_GET['id'] != $_SESSION['id']): ?>
  <a type="submit" href="/user/inbox/send/?id=<?php echo $_GET['id']; ?>" class="button is-info is-info is-fullwidth" style="border-radius: 0px;" <?php if($selectBan->rowCount() >= 1): ?>disabled<?php endif; ?>><i class="fas fa-comments"></i>&nbsp;<b>Message</b></a>
  <a href="#" class="button is-info is-link is-fullwidth" style="border-radius: 0px;" <?php if($selectBan->rowCount() >= 1): ?>disabled<?php endif; ?>><i class="fa-solid fa-arrow-right-arrow-left"></i>&nbsp;<b>Trade</b></a>
  <?php endif; ?>

</form>
</div>
<br>

<article class="message is-dark">
  <div class="message-body">
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. <strong>Pellentesque risus mi</strong>, tempus quis placerat ut, porta nec nulla. Vestibulum rhoncus ac ex sit amet fringilla. Nullam gravida purus diam, et dictum <a>felis venenatis</a> efficitur. Aenean ac <em>eleifend lacus</em>, in mollis lectus. Donec sodales, arcu et sollicitudin porttitor, tortor urna tempor ligula, id porttitor mi magna a neque. Donec dui urna, vehicula et sem eget, facilisis sodales sem.
  </div>
</article>

</div>

<div class="column"></div>
<div class="column"></div>

</div>

    </body>
</html>