<?php
$title = "Dashboard";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);
$id = isset($_GET['id']) ? $_GET['id'] : '';

$limit = 8;
if(!isset($_GET['page']) || isset($_GET['page']) && !is_numeric($_GET['page'])) {

    $_GET['page'] = 1;

}

$page = $_GET['page'];

if($AUTH && isset($_POST['status'])) {

    $check = new Check();
    
    $status = $check->obfuscateIfProfane($_POST['status']);

    if(strlen(str_replace(' ', '', $status)) < 3) {
        
        $err = 'Your status cannot be less than 3 characters long.';

    }

    if(strlen($status) > 255) {

        $err = 'Your status cannot be longer than 255 characters.';

    } else if($my->flood >= time()) {

        $err = 'Please wait another '.($my->flood-time()).' seconds before posting another status update.';

    } else if($my->verified == 0) {

        $err = 'Please verify your account to post a status.';

    }

    if(!isset($err)) {
        
        $add = $db->prepare('INSERT INTO user_statuses (user, `status`, `date`) VALUES (?, ?, ?)');
        $add->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $add->bindValue(2, $status, PDO::PARAM_STR);
        $add->bindValue(3, time(), PDO::PARAM_INT);
        $add->execute();

        $update = $db->prepare('UPDATE users SET `status` = ? WHERE id = ?');
        $update->bindValue(1, $status, PDO::PARAM_STR);
        $update->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $update->execute();

        $flood = $db->prepare('UPDATE users SET flood = ? WHERE id = ?');
        $flood->bindValue(1, time()+30, PDO::PARAM_STR);
        $flood->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $flood->execute();

        header('Location: /dashboard');
        die;

    }

}
$offset = ($page - 1) * $limit;

if ($offset < 0) { $offset = 0; }

$countStatuses = $db->prepare('SELECT * FROM user_statuses');
$countStatuses->execute();

$pages = ceil($countStatuses->rowCount() / $limit);

$selectStatuses = $db->prepare('SELECT user_statuses.*, users.id, users.username, users.expire, users.avatar_hash FROM user_statuses JOIN users ON user_statuses.user = users.id ORDER BY user_statuses.id DESC LIMIT '.$limit.' OFFSET '.$offset);
$selectStatuses->execute();

$selectFriends = $db->prepare("(SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.receiver = users.id WHERE user_friends.status = 1 AND user_friends.sender = ? ORDER BY user_friends.`date` DESC) UNION ALL (SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.sender = users.id WHERE user_friends.receiver = ? AND user_friends.status = 1 ORDER BY user_friends.`date` DESC) LIMIT 4");
$selectFriends->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$selectFriends->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
$selectFriends->execute();

if(isset($_POST['scrub']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('UPDATE user_statuses SET `status` = ? WHERE user = ?');
    $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
    $scrub->bindValue(2, $id, PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Scrubbed Status', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();

    header('Location: /dashboard');
    die;

}

?>

<script>
    // check if "loginRedirect" exists
    if(localStorage.hasOwnProperty('loginRedirect') === true){
        // make redirect
        window.location.href = localStorage.getItem('loginRedirect');

        // remove it from localStorage for next dashboard visit
        localStorage.removeItem('loginRedirect');
    }
</script>
<section class="section">
<div class="container">
<div class="columns is-centered">
<div class="column is-3"></div>
<div class="column is-3">
<div class="box has-text-centered" style="justify-content: center;">
<img width="255" src="/assets/images/avatars/<?php echo $my->avatar_hash.'.png'; ?>?r=<?php echo bin2hex(random_bytes(16)); ?>" alt="">
</div>
</div>
        <div class="column is-5">
                <div class="control is-expanded">
                <form method="post">
<div class='control'>
<input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
</div> 
                    <input class="input " type="text" name="status" maxlength="255"
                        placeholder="What's up, <?= $my->username; ?>?">
                        <?php if(isset($err)) { ?><p class="help is-danger"><?php echo $err; ?></p><?php } ?> 
                </div>
                <div class="control">
                    <button class="button is-info" type="submit">Post Status</button>
</form>
                        </div>
                <?php while($st = $selectStatuses->fetch(PDO::FETCH_OBJ)) {?>
<div class="wall_post" style="word-break: break-all;">
<article class="media box">
<figure class="media-left">
<div class="avatar_dashboard_friend_container">
<?php if($st->expire+300 > time()) {?><div title="" class="avatar_online_circle circle-xs"></div><?php }else if($st->expire+300 < time()) { ?><div title="" class="avatar_offline_circle circle-xs"></div><?php } ?>
<div class="avatar_user avatar_dashboard_friend">
<img  width="45" height="45" src="/assets/images/avatars/headshots/<?php echo htmlspecialchars($st->avatar_hash); ?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>">
</figure>
<div class="media-content wall_post_content">
<div class="content">
<strong><a class="username" href="/user/<?php echo htmlspecialchars($st->user); ?>"><?php echo htmlspecialchars($st->username); ?></a></strong>&nbsp;<span class="has-text-grey-light">, <small><?php echo timeAgo(date('Y/m/d H:i:s', $st->date)); ?></small></span>
<br>
<p class="usercontent"><?php echo htmlspecialchars($st->status); ?></p>
</div>
</div>
</article>
</div>
<?php } ?>
<?php if($pages > 1) { ?>
    <nav  class="pagination is-centered" role="navigation"
    aria-label="pagination"> 
<a <?php if($page-1 != 0) { echo 'href="/dashboard/?page='.($page-1).'"'; } ?><?php if($page-1 == 0) { echo 'disabled="disabled" ';} ?>class="pagination-link"><i class="fas fa-chevron-left fa-sm"></i></button></a>
<ul  class="pagination-list">
<?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {
					
					if ($i <= $pages) { echo '
<li>
<a href="/dashboard/?page='.$i.'" ';if($i == $page) { echo 'aria-current="true"'; } echo ' class="pagination-link'; if($i == $page) { echo ' is-current'; } echo '">'.$i.'</a></li>'; } } ?>
</ul>
<a <?php if($pages > 1 || $page+1 <= $pages) { echo ' href="/dashboard/?page='.($page+1).'"'; } ?> <?php if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled" ';} ?>class="is-hidden-mobile pagination-next"><i class="fas fa-chevron-right fa-sm"></i></button></a>
</nav>
<?php } ?>
</div>

  
              <div class='column is-2'>
                </div>
          </div>
      </div>
    </body>
</section>
<?php
$app->footer();
?>




