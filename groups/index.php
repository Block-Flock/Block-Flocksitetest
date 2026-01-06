<?php
$title = 'Groups';
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$limit = 15;

if(!isset($_GET['page'])) {

    $page = 1;

} else {

    $page = $_GET['page'];

}

$offset = ($page - 1) * $limit;

if($offset < 0) { $offset = 0; }

$selectGroupsNum = $db->prepare('SELECT * FROM user_groups ');
$selectGroupsNum->execute();

$pages = ceil($selectGroupsNum->rowCount() / $limit);

if(!isset($_GET['query'])) {

    $selectGroups = $db->prepare('SELECT user_groups.*, users.id AS `uid`, users.username FROM user_groups JOIN users ON user_groups.creator = users.id ORDER BY user_groups.id DESC LIMIT '.$limit.' OFFSET '.$offset);
    $selectGroups->execute();

} else if(isset($_GET['query'])) {

    $selectGroups = $db->prepare('SELECT user_groups.*, users.id AS `uid`, users.username FROM user_groups JOIN users ON user_groups.creator = users.id WHERE user_groups.name LIKE ? ORDER BY user_groups.id DESC LIMIT '.$limit.' OFFSET '.$offset);
    $selectGroups->bindValue(1, '%'.$_GET['query'].'%', PDO::PARAM_STR);
    $selectGroups->execute();

}

if(isset($_POST['query'])) {

    header('Location: /groups?query='.$_POST['query']);
    die;

}

?>
<section class="section">
<div class=" page container">
<div class="columns">
<div class="column">
</div>
<div class="column is-10">
<a href="/group/create" class="button is-info is-pulled-left is-small"><b><i class="fas fa-plus"></i>&nbsp;Create Group</b></a>
<?php if($AUTH) {?>
<a href="#" class="button is-danger is-pulled-right is-small"><b><i class="fas fa-crown"></i>&nbsp;My groups</b></a>
<?php } ?>
<br><br>
<form method="post">
                            <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
<input type="text" name="query" class="input" style="width: 100%;display: inline-block;" placeholder="Search">&nbsp;&nbsp;
</form>
<?php if($selectGroupsNum->rowCount() == 0) { ?>
<p class="is-fullwidth has-text-centered">No groups found.</p>
<?php } else {
    while($sg = $selectGroups->fetch(PDO::FETCH_OBJ)) {
        if($sg->moderation_status == 0) {

            $sg->logo = "pending.png";

        } else if($sg->moderation_status == 2) {

            $sg->logo = "declined.png";

        }
        
        if(strlen($sg->description) > 71) {

        $sg->description = substr($sg->description, 0, 71) . '...';

    }
    ?>
<div class="box">
<article class="media">
<figure class="media-left">
<img width="128" src="/assets/images/group/<?php echo htmlspecialchars($sg->logo); ?>?r=<?php echo bin2hex(random_bytes(16)); ?>">
</figure>
<div class="media-content">
<div class="content">
<a  class="title is-6 storetitle" href="/group/view/<?=$sg->id?>"><?php echo htmlspecialchars($sg->name); ?></a> 
<?php if($sg->verified >= 1) { ?>
<i class="fas fa-crown"></i>
<?php } ?>
<span class="has-text-grey-lighter is-size-7">&nbsp;Creator: <a href="/user/<?php echo htmlspecialchars($sg->creator);?>"><?php echo htmlspecialchars($sg->username);?></a>&nbsp;&nbsp;Members: <?php echo number_format($sg->members); ?></span>
<br>
<p class="usercontent"><?php echo nl2br(htmlspecialchars($sg->description)); ?></p>
</div>
</div>
</article>
</div>
<?php } ?>
<?php if($pages > 1) { ?>
    <nav  class="pagination is-centered" role="navigation"
    aria-label="pagination">  
<a role="button" <?php if($page-1 != 0) { echo 'href="/groups?'; if(isset($_GET['query'])) { echo 'query='.$_GET['query;'].'&'; } echo 'page='.($page-1).'"'; } ?><?php if($page-1 == 0) { echo 'disabled="disabled" ';} ?>class="pagination-link is-hidden-mobile"><i class=" fas fa-chevron-left fa-sm"></i></a>
<ul  class="pagination-list">
<?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {

					if ($i <= $pages) { echo '
<li>
<a href="/groups?'; if(isset($_GET['query'])) { echo 'query='.$_GET['query;'].'&'; } echo 'page='.$i.'" ';if($i == $page) { echo 'aria-current="true"'; } echo ' class="pagination-link'; if($i == $page) { echo ' is-current'; } echo '">'.$i.'</a></li>'; } } ?>
</ul>
<a  role="button" <?php if($pages > 1 || $page+1 < $pages) { echo ' href="/groups?'; if(isset($_GET['query'])) { echo 'query='.$_GET['query;'].'&'; } echo 'page='.($page+1).'"'; } ?> <?php if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled" ';} ?>class="is-hidden-mobile pagination-next"><i class="fas fa-chevron-right fa-sm"></i></a>
</nav>
<?php } ?>
<?php } ?>

</div>
<div class="column"></div>
</div>
</div>
</section>
<?php
$app->footer();
?>