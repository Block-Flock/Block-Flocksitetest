<?php
$root = $_SERVER['DOCUMENT_ROOT'];
?>
<?php 
       include $root.'/private/config.inc.php';
      
if(isset($_POST['id'])) {

if(!is_numeric($_POST['id'])) {

    die;

}

if($_POST['id'] > 6) {

    die;

}

$limit = 8;

if(isset($_POST['page'])) {

    if(!is_numeric($_POST['page'])) {

        die;

    }

    $page = $_POST['page'];

} else {

    $page = 1;

}


$offset = ($page - 1) * $limit;

if($offset < 0) { $offset = 0; }

$postCount = $db->prepare('SELECT * FROM forum_posts WHERE subforum = '.$_POST['id']);
$postCount->execute();

$pages = ceil($postCount->rowCount() / $limit);

$selectSub = $db->prepare('SELECT forum_posts.*, users.id, users.username, users.expire, users.administrator, users.premium, users.avatar_hash FROM users JOIN forum_posts ON users.id = forum_posts.author WHERE forum_posts.subforum = '.$_POST['id'].' ORDER BY forum_posts.pinned DESC, forum_posts.lastposttime DESC LIMIT '.$limit.' OFFSET '.$offset);
$selectSub->execute();
?>
<?php if($selectSub->rowCount() == 0) {
echo '<p class="is-fullwidth has-text-centered">No posts has currently been made.</p>';
} else { ?>

<?php while($sa = $selectSub->fetch(PDO::FETCH_OBJ)) { 
$replyCount = $db->prepare('SELECT * FROM forum_posts WHERE subforum = ?');
$replyCount->bindValue(1, $sa->lastpostid, PDO::PARAM_INT);
$replyCount->execute();

$userinfo = $db->prepare('SELECT forum_posts.*, users.* FROM forum_posts JOIN users WHERE forum_posts.lastpostauthor = users.id AND forum_posts.id = ?');
    $userinfo->bindValue(1, $sa->lastpostid, PDO::PARAM_INT);
    $userinfo->execute();
    $uI = $userinfo->fetch(PDO::FETCH_OBJ);


?>
<div id="postsContainer">
<article class="media box">
<figure class="media-left image is-64x64">
<img src="/assets/images/avatars/headshots/<?php echo $sa->avatar_hash; ?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>" alt="Avatar" class="is-rounded">
</figure>

<div class="media-right v-center"></div>
<div class="content">
<div class="novello-title is-size-6">
<?php if($sa->locked == 1) { echo '<i class="fas fa-lock fa-lg"></i>&nbsp;'; } ?> 
<?php if($sa->pinned == 1) { echo '<i class="fas fa-thumbtack fa-lg"></i>&nbsp;'; } ?><a class="has-text-weight-bold" href="/forum/post/<?php echo $sa->lastpostid; ?>"><?php echo htmlentities($sa->title); ?></a>
</div>
<p> Posted by: <a <?php if($sa->administrator >= 1) { echo 'class="has-text-danger" title="Administrator" '; } else if($sa->administrator == 0 && $sa->premium == 1) { echo 'class="has-text-success" title="Premium" '; }  ?>href="/user/<?php echo htmlspecialchars($sa->author); ?>"><span class="usercontent"><?php echo htmlspecialchars($sa->username); ?></span></a>, <?php echo timeAgo(date('Y/m/d H:i:s', $sa->timemade)); ?>
<br><b>Latest reply by</b>: <a <?php if($uI->administrator >= 1) { echo 'class="has-text-danger" title="Administrator" '; } else if($uI->administrator == 0 && $uI->premium == 1) { echo 'class="has-text-success" title="Premium" '; }  ?>href="/user/<?php echo htmlspecialchars($uI->lastpostauthor); ?>"><span class="usercontent"><?php echo htmlspecialchars($uI->username); ?></span></a>, <?php echo timeAgo(date('Y/m/d H:i:s', $uI->lastposttime)); ?></p>
</p>
                 </div>
        </article>
</div>
    </div>
<?php } } ?>
        </div>
<?php if($pages > 1) { ?>
    <nav class="pagination is-centered" role="navigation" aria-label="pagination">
<a role="button" <?php if($page-1 != 0) { echo ' onclick="getSub('.$_POST['id'].','.($page-1).')"'; } ?><?php if($page-1 == 0) { echo 'disabled="disabled" ';} ?>class="is-hidden-mobile pagination-link disabled" disabled="true"><i class="fas fa-chevron-left fa-sm"></i></a>
<ul class="pagination-list">
<?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {
					
					if ($i <= $pages) { echo ' <li>
<a onclick="getSub('.$_POST['id'].','.$i.')" ';if($i == $page) { echo 'aria-current="true"'; } echo ' class="pagination-link'; if($i == $page) { echo ' is-current'; } echo '">'.$i.'</a></li>'; } } ?>
</ul>
<a role="button" <?php if($pages > 1 || $page+1 <= $pages) { echo ' onclick="getSub('.$_POST['id'].','.($page+1).')"'; } ?> <?php if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled" ';} ?>class="is-hidden-mobile pagination-next"><i class="fas fa-chevron-right fa-sm"></i></a>
</nav>
<?php } } ?>

















