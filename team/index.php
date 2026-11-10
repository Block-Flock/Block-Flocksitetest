<?php
$title = 'Users';
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
$limit = 8;

if(!isset($_GET['page'])) {

    $page = 1;

} else {

    $page = $_GET['page'];

}

$offset = ($page-1) * $limit;

if($offset < 0) { $offset = 0; }

$selectUserCount = $db->prepare('SELECT * FROM `users` WHERE `administrator` = 1');
$selectUserCount->execute();

$pages = ceil($selectUserCount->rowCount() / $limit);

if(!isset($_POST['query'])) {

$selectUsers = $db->prepare("SELECT * FROM `users` WHERE `administrator` = 1");
$selectUsers->execute();

} else if(isset($_POST['query'])) {

$selectUsers = $db->prepare('SELECT * FROM users WHERE administrator == 1 ? ORDER BY expire DESC LIMIT '.$limit.' OFFSET '.$offset);
$selectUsers->bindValue(1, '%'.$_POST['query'].'%', PDO::PARAM_STR);
$selectUsers->execute();

}
$selectUserCount = $db->prepare("SELECT * FROM `users` WHERE `administrator` = 1");
$selectUserCount->execute();
$nousers= $selectUserCount->fetch(PDO::FETCH_OBJ);
?>
<section class="section">
<div class="page container">
<div class="columns">
<div class="column is-1">
</div>
<div class="column is-11">
<nav class="level is-mobile">
<div class="level-left">
<div class="level-item">
<div class="has-text-weight-semibold">
All Users (<?php echo number_format($nousers->administrator); ?>)</div>
</div>
</div>
<div class="level-right">
<form method="post">
<input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
<fieldset>
<div class="field has-addons mb-0">
<p class="control">
<input class="input is-small" type="text" name="query" placeholder="Search"></p> 
<p class="control"><button class="button is-info is-small" type="submit">Search</button></p>
</div>
</fieldset>
</form>
</div>
</nav>
<div class="columns is-multiline">
<?php while($st = $selectUsers->fetch(PDO::FETCH_OBJ)) {
if(strlen($st->blurb> 71)) {
$st->blurb = substr($st->blurb, 0, 71) . '...';
}?>
<div class="column is-6 mobile-no-paddingBottom">
<div class="box userPageBox is-marginless">
<div class="columns is-mobile">
<div class="column is-one-fifth-tablet is-4-mobile userColumn column-is-vcentered">
<div class="avatar_user">
<img class="is-rounded"  Src="/assets/images/avatars/headshots/<?php echo htmlspecialchars($st->avatar_hash);?>.png?r=<?php echo bin2hex(random_bytes(16)); ?> "onerror="https://bricktaria.com/default.png"
onerror="this.src='/assets/images/avatars/headshots/default.png?r=<?php echo bin2hex(random_bytes(16)); ?>'"
alt="Could Not Get Profile Image!"style="">
</div>
</div>
<div class="column is-four-fifths-tablet is-8-mobile userColumn column-is-vcentered">
<div class="columns is-mobile is-marginless-bottom is-paddingless-bottom">
<div class="column is-8 is-paddingless-bottom">
<a class="is-size-5 usercontent username" href="/user/<?php echo (htmlspecialchars($st->administrator));?>"><?php echo nl2br(htmlspecialchars($st->username)); ?></a>
<p class="usercontent has-text-grey-light user_page_blurb"><?php echo htmlentities($st->blurb); ?></p>
</div>
<?php if (($st->expire + 1800) > time()) { ?>
<div class="column is-4 is-paddingless-bottom">
<span class="tag is-success is-pulled-right has-tooltip-top" data-tooltip="<?php echo date("F j, Y, g:i a", $st->expire); ?>">Online</span>
<?php }else{ ?>
<div class="column is-4 is-paddingless-bottom">
<?php } ?>
</div>
</div>
</div>
</div>
</div>
</div>
<?php } ?>
</div>
<?php if($pages > 1) { ?>
<nav  class="pagination is-centered" role="navigation" aria-label="pagination"> 
<a <?php if($page-1 != 0) { echo 'href="/users/?page='.($page-1).'"'; } ?><?php if($page-1 == 0) { echo 'disabled="disabled" ';} ?>class="pagination-link is-hidden-mobile"><i class=" fas fa-chevron-left fa-sm"></i></a>
<ul  class="pagination-list">
<?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {

					if ($i <= $pages) { echo '
<li>
<a href="/users/?page='.$i.'" ';if($i == $page) { echo 'aria-current="true"'; } echo ' class="pagination-link'; if($i == $page) { echo ' is-current'; } echo '">'.$i.'</a></li>'; } } ?>
</ul>
<a <?php if($pages > 1 || $page+1 <= $pages) { echo ' href="/users/?page='.($page+1).'"'; } ?> <?php if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled" ';} ?>class="is-hidden-mobile pagination-next"><i class="fas fa-chevron-right fa-sm"></i></a>
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