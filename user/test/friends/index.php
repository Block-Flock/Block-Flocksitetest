<?php
$title = "Profile";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isset($_GET['id'])) {

    header('Location: /');
    die;

}

$id = isset($_GET['id']) ? $_GET['id'] : '';

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
<div class="container">
<section class="section">
<?php if($selectBan->rowCount() >= 1) { ?>
<div class="site-announcement">
<div class="box has-background-danger has-text-white-bis has-text-centered is-marginless has-shadow novelloAnnouncement">
<div class="container">
                <p><b>This user has been suspended</b></p>
            </div>
        </div>
        </div>
        <br>
<?php } ?>
<div class="columns is-centered">
<div class="column is-3"><aside class="menu has-text-left"><p class="menu-label">Profile</p> 
<ul class="menu-list"><li><a href="/user/profile/?id=<?php echo htmlspecialchars($gP->id);?>">Profile</a></li></ul>
<p class="menu-label">More related to this user</p> 
<ul class="menu-list"><li><a class="is-active" href="/user/profile/friends/?id=<?php echo htmlspecialchars($gP->id);?>">Friends</a></li>
</ul></aside></div>
<div class="column is-3">
<div id="profile_username" class="novello-title is-size-5 usercontent"><?php echo htmlspecialchars($gP->username); ?>
<?php if($AUTH  && $_GET['id'] != $_SESSION['id']) { ?>
            <a class="is-pulled-right"  style=" font-size: 17px;" href="/user/report/?id=<?php echo $_GET['id']; ?>">
                <i class="fas fa-flag"></i>
         </a><?php } ?></div>
<div class="box has-text-centered" id="profile_imagebox" style="border-bottom: 3px solid <?php if($gP->expire+300 > time()) { echo '#23d160;'; } else if($gP->expire+300 < time()) { echo 'hsl(0, 0%, 48%);'; } ?>"
title="<?php echo htmlspecialchars($gP->username); ?> is <?php if($gP->expire+300 > time()) { echo 'online'; } else if($gP->expire+300 < time()) { echo 'offline'; } ?>">
<img src="/assets/images/avatars/<?php echo $gP->avatar_hash; ?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>"
onerror="this.src='/assets/images/avatars/default.png?r=<?php echo bin2hex(random_bytes(16)); ?>'"
alt="">
<hr>
<p class="usercontent is-size-6 has-text-grey-light"><?php echo htmlentities($gP->blurb); ?></p>
</div> 
<?php if($AUTH  && $_GET['id'] != $_SESSION['id']) { ?>
                  <?php } ?>
    <?php if(!isset($friends) && $AUTH && $_GET['id'] != $_SESSION['id'] && $selectBan->rowCount() < 1) { ?>
    
    <form method="post">
  <button type="submit" name="send" class="button is-success is-fullwidth"><i class="fas fa-user-plus"></i>&nbsp;<b>Add friend</b></button>
    <?php } ?>

    <?php if(isset($friends) && $AUTH && $selectfr->rowCount() == 1 && $friends == 0 && $_GET['id'] != $_SESSION['id']) { ?>
      <div class="push-5"></div>
  <button name="send" class="button is-dark is-fullwidth"><i class="fas fa-clock"></i>&nbsp;<b>Pending Friend Request</b></button>
    <?php } ?>

    <?php if(isset($friends) && $AUTH && $selectFriends4->rowCount() >= 1 && $friends == 1 && $_GET['id'] != $_SESSION['id'] OR isset($friends) && $AUTH && $selectFriends3->rowCount() == 1 && $friends == 1 && $_GET['id'] != $_SESSION['id']) { ?>
      <div class="push-5"></div>
      <form method="post">
  <button type="submit" name="remove" class="button is-danger is-fullwidth"><i class="fas fa-user-times"></i>&nbsp;<b>Remove Friend</b></button>
  <?php } ?>

  <?php if($AUTH && $_GET['id'] != $_SESSION['id']) { ?>
    <div class="push-5"></div>
  <a type="submit" href="/user/inbox/send/?id=<?php echo $_GET['id']; ?>" class="button is-primary is-fullwidth"><i class="fas fa-comments"></i>&nbsp;<b>Message</b></a>
  <?php } ?>

  <?php if($AUTH && $my->administrator >= 1 && $_GET['id'] != $_SESSION['id']) { ?>
    <div class="push-5"></div>
  <a class="button is-danger is-fullwidth" href="/admin/modify/?id=<?php echo $_GET['id']; ?>"><i class="fas fa-hammer"></i>&nbsp;<b>Modify User</b></a>
        <?php if($AUTH && $my->administrator >= 1 && $_GET['id'] != $_SESSION['id']) { ?>
          <div class="push-5"></div>
  <button type="submit" class="button is-warning is-fullwidth" name="reRenderAvatar"><i class="fas fa-user-astronaut"></i>&nbsp;<b>Rerender Avatar</b></button>
  <?php } ?>
  </form>
  <?php } ?>
                <div class="push-5"></div>
<div class="novello-title is-size-5 usercontent">Statistics</div> 
<div class="box">
<strong>Join Date: </strong><?php echo date('d/m/Y ', $gP->joined); ?> 
<br>
<strong>Last Seen: </strong><?php echo timeAgo(date('Y/m/d H:i:s', $gP->expire)); ?>
<br>
<strong>Forum Posts: </strong><?php echo number_format($numPosts); ?>
</div></div>
<div class="column is-6">
<br>
<div class="box has-text-centered">
<?php if($selectFriends->rowCount() == 0) { ?>
<i class="fa fa-users fa-5x"></i>
<p>This user does not have any friends yet.</p>
          </div>
                <?php } else if($selectFriends->rowCount() >= 1) { ?>
                    <div class="columns is-mobile is-multiline has-text-centered">
                    <?php while($fr = $selectFriends->fetch(PDO::FETCH_OBJ)) { ?>
                        <div class="column is-half-mobile is-3-tablet">
                        <img src="/assets/images/avatars/<?php echo htmlspecialchars($fr->avatar_hash);?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>"
                        onerror="this.src='/assets/images/avatars/default.png?r=<?php echo bin2hex(random_bytes(16)); ?>'"
                        alt="">
                            <div class="push-10"></div>
                            <p class="text-truncate"><?php if($fr->expire+300 > time()) {?><span class="is-online">●</span><?php }else if($fr->expire+300 < time()) { ?><span class="has-text-grey-light">●</span><?php } ?>&nbsp;<a href="/user/profile/?id=<?php echo htmlspecialchars($fr->userid); ?>"><?php echo htmlspecialchars($fr->username); ?></a></p>
                        </div>
                        <?php } ?>
                    </div>
                    </div>
                    <?php } ?>
<?php
$app->footer();
?>