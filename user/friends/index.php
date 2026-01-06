<?php
$title = "Friends";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);

$userExists = $db->prepare("SELECT * FROM users WHERE id = ?");
$userExists->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$userExists->execute();

if($userExists->rowCount() == 0) {
    header("Location: /");
    die;
}

$ue = $userExists->fetch(PDO::FETCH_OBJ);

$limit = 20;

if(!isset($_GET['page'])) {
    $page = 1;
} else if(isset($_GET['page'])) {
    $page = $_GET['page'];
}

$offset = ($page-1) * $limit;

if($offset < 0) { $offset = 0; }

$selectFriends = $db->prepare("(SELECT users.id as userid, users.username, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.receiver = users.id WHERE user_friends.status = 1 AND user_friends.sender = ? ORDER BY user_friends.`date` DESC) UNION ALL (SELECT users.id as userid, users.username, users.avatar_hash, user_friends.* FROM user_friends JOIN users ON user_friends.sender = users.id WHERE user_friends.receiver = ? AND user_friends.status = 1 ORDER BY user_friends.`date` DESC) LIMIT $limit OFFSET $offset");
$selectFriends->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$selectFriends->bindValue(2, $_GET['id'], PDO::PARAM_INT);
$selectFriends->execute();

$friendCount = $db->prepare("SELECT * FROM user_friends WHERE sender = ? AND `status` = 0 OR receiver = ? AND `status` = 0");
$friendCount->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$friendCount->bindValue(2, $_GET['id'], PDO::PARAM_INT);
$friendCount->execute();

$pages = ceil($friendCount->rowCount() / $limit);
?>
<section class="section">
<div class="container">
<div class="columns">
<div class="column"></div>
<div class="column is-8">
<p><?php echo htmlspecialchars($ue->username); ?>'s friends</p>    
<div class="box">
    <div class="columns is-multiline">
        <?php while($fr = $selectFriends->fetch(PDO::FETCH_OBJ)) { ?>
        <div class="column is-6 has-text-centered">
            <img src="/assets/images/avatars/<?php echo htmlspecialchars($fr->avatar_hash);?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>"
         onerror="this.src='/assets/images/avatars/default.png?r=<?php echo bin2hex(random_bytes(16)); ?>'"
        alt="Could Not Get Profile Image!" style="width: 184px;height: 184px;">
            <div class="push-10"></div>
            <a href="/user/<?php echo $fr->userid; ?>"><?php echo htmlspecialchars($fr->username); ?></a>
        </div>
    <?php } ?>
</div>
        </div>
        <?php if($pages > 1) { ?>
            <nav class="pagination is-centered" role="navigation" aria-label="pagination">
            <a class="pagination-previous" <?php if($page - 1 == 0) { echo 'disabled="disabled"'; } else { echo 'href="/user/friends/?id='.$_GET['id'].'/user/friends&page='.($page - 1).'"'; } ?>>Previous</a>
            <a class="pagination-next" <?php if($page + 1 > $pages) { echo 'disabled="disabled"'; } else { echo 'href="/user/friends/?id='.$_GET['id'].'/user/friends&page='.($page + 1).'"'; } ?>>Next page</a>
            <ul class="pagination-list">
                <?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {

                    if ($i <= $pages) { echo '
<li><a ';if($i != $page){echo'href="/user/friends/?id='.$_GET['id'].'&page='.$i.'"';}echo'class="pagination-link'; if($i == $page){echo' is-current';}echo'" aria-label="Goto page '.$i.'">'.$i.'</a></li>'; } } ?>
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
