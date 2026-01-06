<?php
$title = "Requests";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);
$limit = 8;

if(isset($_GET['page'])) {
    $page = $_GET['page'];
} else if(!isset($_GET['page'])) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

if($offset < 0) { $offset = 0; }

$pendingFriends = $db->prepare("SELECT user_friends.*, users.id AS userid, users.username, users.avatar_hash FROM user_friends JOIN users ON user_friends.sender = users.id WHERE user_friends.receiver = ? AND user_friends.`status` = 0 ORDER BY user_friends.`date` DESC LIMIT $limit OFFSET $offset");
$pendingFriends->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$pendingFriends->execute();

$friendCount = $db->prepare("SELECT * FROM user_friends WHERE receiver = ? AND status = 0");
$friendCount->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$friendCount->execute();

$pages = ceil($friendCount->rowCount() / $limit);

if(isset($_POST['accept']) && isset($_POST['userId']) && $AUTH) {
    $getprofile = $db->prepare("SELECT * FROM users WHERE id = ?");
    $getprofile->bindValue(1, $_POST['userId'], PDO::PARAM_INT);
    $getprofile->execute();

    if($getprofile->rowCount() == 0) {
        header("Location: /user/dashboard");
        die();
    }

    $pendingFriends = $db->prepare("SELECT * FROM user_friends WHERE sender = ? AND receiver = ? AND `status` = 0");
    $pendingFriends->bindValue(1, $_POST['userId'], PDO::PARAM_INT);
    $pendingFriends->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $pendingFriends->execute();

    if($pendingFriends->rowCount() == 0) {
        header("Location: /user/dashboard");
        die();
    }

    $accept = $db->prepare("UPDATE user_friends SET `status` = 1  WHERE sender = ? AND receiver = ? AND `status` = 0");
    $accept->bindValue(1, $_POST['userId'], PDO::PARAM_INT);
    $accept->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $accept->execute();

    header("Location: /user/friends/requests");
    die;

}

if(isset($_POST['decline']) && isset($_POST['userId']) && $AUTH) {
    $getprofile = $db->prepare("SELECT * FROM users WHERE id = ?");
    $getprofile->bindValue(1, $_POST['userId'], PDO::PARAM_INT);
    $getprofile->execute();

    if($getprofile->rowCount() == 0) {
        header("Location: /user/dashboard");
        die();
    }

    $pendingFriends = $db->prepare("SELECT * FROM user_friends WHERE sender = ? AND receiver = ? AND `status` = 0");
    $pendingFriends->bindValue(1, $_POST['userId'], PDO::PARAM_INT);
    $pendingFriends->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $pendingFriends->execute();

    if($pendingFriends->rowCount() == 0) {
        header("Location: /user/dashboard");
        die();
    }

    $decline = $db->prepare("UPDATE user_friends SET `status` = 2  WHERE sender = ? AND receiver = ? AND `status` = 0");
    $decline->bindValue(1, $_POST['userId'], PDO::PARAM_INT);
    $decline->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $decline->execute();

    header("Location: /user/friends/requests");
    die;

}

?>
<section class="section">
<div class="container">
<div class="columns">
<div class="column"></div>
<div class="column is-6">
<div class="box">
<div class="columns is-multiline">
 <?php while($fr = $pendingFriends->fetch(PDO::FETCH_OBJ)) { ?>
        <div class="column is-6 has-text-centered">
            <img src="/assets/images/avatars/<?php echo htmlspecialchars($fr->avatar_hash);?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>"
         onerror="this.src='/assets/images/avatars/default.png?r=<?php echo bin2hex(random_bytes(16)); ?>'"
        alt="" style="width: 184px; height: 184px;">
            <br>
            <a href="/user/<?php echo $fr->userid; ?>"><?php echo htmlentities($fr->username); ?></a>
            <br>
            <form method="post">
                            <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
            <div class='field'>
                            <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
                     </div>
             <input type="hidden" name="userId" value="<?php echo htmlentities($fr->userid); ?>">
             <div class="field is-grouped">
             <div class="control">
             <button type="submit" name="accept" class="button is-success"onclick="this.classList.toggle('button--loading')"><span class="button__text">Accept</span></button>
             </div>
                 <div class="control">
                     <button type="submit" name="decline" class="button is-danger" onclick="this.classList.toggle('button--loading')"><span class="button__text">Decline</span></button>
                 </div>
             </div>
            </form>
        </div>
    <?php } ?>    
</div>    
</div>    
        <?php if($pages > 1) { ?>
            <nav class="pagination is-centered" role="navigation" aria-label="pagination">
            <a class="pagination-previous" <?php if($page - 1 == 0) { echo 'disabled="disabled"'; } else { echo 'href="/user/friends/requests?page='.($page - 1).'"'; } ?>>Previous</a>
            <a class="pagination-next" <?php if($page + 1 > $pages) { echo 'disabled="disabled"'; } else { echo 'href="/user/friends/requests?page='.($page + 1).'"'; } ?>>Next page</a>
            <ul class="pagination-list">
                <?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {

                    if ($i <= $pages) { echo '
<li><a ';if($i != $page){echo'href="/user/friends/requests?page='.$i.'"';}echo'class="pagination-link'; if($i == $page){echo' is-current';}echo'" aria-label="Goto page '.$i.'">'.$i.'</a></li>'; } } ?>
            </ul>
        </nav>
    <?php } ?>
</div>
<div class="column"></div>
</div>    
</div>    
</section>
<?php
$app->footer();
?>



















