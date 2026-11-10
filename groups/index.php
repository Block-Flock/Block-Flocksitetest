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

$selectGroupsNum = $db->prepare('SELECT * FROM user_groups WHERE moderation_status = 1');
$selectGroupsNum->execute();

$pages = ceil($selectGroupsNum->rowCount() / $limit);

if(!isset($_GET['query'])) {

    $selectGroups = $db->prepare('SELECT user_groups.*, users.id AS `uid`, users.username FROM user_groups JOIN users ON user_groups.creator = users.id WHERE user_groups.moderation_status = 1 ORDER BY user_groups.id DESC LIMIT '.$limit.' OFFSET '.$offset);
    $selectGroups->execute();

} else if(isset($_GET['query'])) {

    $selectGroups = $db->prepare('SELECT user_groups.*, users.id AS `uid`, users.username FROM user_groups JOIN users ON user_groups.creator = users.id WHERE user_groups.name LIKE ? AND user_groups.moderation_status = 1 ORDER BY user_groups.id DESC LIMIT '.$limit.' OFFSET '.$offset);
    $selectGroups->bindValue(1, '%'.$_GET['query'].'%', PDO::PARAM_STR);
    $selectGroups->execute();

}

if(isset($_POST['query'])) {

    header('Location: /groups?query='.$_POST['query']);
    die;

}

?>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column"></div>
            <div class="column is-10">
                <div class="glass-box mb-5">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <h1 class="title is-3 has-text-white mb-0">Groups</h1>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <div class="buttons">
                                    <a href="/group/create" class="button is-primary is-small">
                                        <span class="icon is-small"><i class="fas fa-plus"></i></span>
                                        <span>Create Group</span>
                                    </a>
                                    <?php if($AUTH) {?>
                                    <a href="#" class="button is-info is-small">
                                        <span class="icon is-small"><i class="fas fa-crown"></i></span>
                                        <span>My Groups</span>
                                    </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="post" class="mt-4">
                        <div class='control'>
                            <input type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
                        <div class="field has-addons">
                            <div class="control is-expanded">
                                <input type="text" name="query" class="input has-background-dark has-text-white" style="border-color: rgba(255,255,255,0.1);" placeholder="Search groups...">
                            </div>
                            <div class="control">
                                <button class="button is-primary">
                                    <span class="icon"><i class="fas fa-search"></i></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if($selectGroupsNum->rowCount() == 0) { ?>
                    <div class="glass-box has-text-centered">
                        <p class="subtitle has-text-grey-light">No groups found.</p>
                    </div>
                <?php } else { ?>
                    <div class="group-grid">
                        <?php while($sg = $selectGroups->fetch(PDO::FETCH_OBJ)) {
                            if($sg->moderation_status == 0) {
                                $sg->logo = "pending.png";
                            } else if($sg->moderation_status == 2) {
                                $sg->logo = "declined.png";
                            }
                            
                            if(strlen($sg->description) > 71) {
                                $sg->description = substr($sg->description, 0, 71) . '...';
                            }
                        ?>
                        <div class="glass-box p-4 group-card" style="transition: transform 0.2s ease; cursor: pointer;" onclick="location.href='/group/view/<?=$sg->id?>'">
                            <div class="has-text-centered mb-3">
                                <figure class="image is-128x128 is-inline-block">
                                    <img class="is-rounded" src="/assets/images/group/<?php echo htmlspecialchars($sg->logo); ?>?r=<?php echo bin2hex(random_bytes(16)); ?>" style="border: 4px solid rgba(255,255,255,0.1); background: #16213e;">
                                </figure>
                            </div>
                            <div class="content has-text-centered flex-grow-1">
                                <h3 class="title is-5 mb-1">
                                    <a href="/group/view/<?=$sg->id?>" class="has-text-white"><?php echo htmlspecialchars($sg->name); ?></a>
                                    <?php if($sg->verified >= 1) { ?>
                                        <span class="icon has-text-warning is-small ml-1" title="Verified"><i class="fas fa-crown"></i></span>
                                    <?php } ?>
                                </h3>
                                <p class="is-size-7 has-text-grey-light mb-2">
                                    By <a href="/user/<?php echo htmlspecialchars($sg->creator);?>" class="has-text-primary"><?php echo htmlspecialchars($sg->username);?></a>
                                </p>
                                <p class="is-size-7 has-text-grey-lighter">
                                    <i class="fas fa-users"></i> <?php echo number_format($sg->members); ?> Members
                                </p>
                                <hr class="my-2" style="background-color: rgba(255,255,255,0.1);">
                                <p class="is-size-7 has-text-grey-light" style="min-height: 40px;">
                                    <?php echo nl2br(htmlspecialchars($sg->description)); ?>
                                </p>
                            </div>
                            <div class="mt-auto pt-3">
                                <a href="/group/view/<?=$sg->id?>" class="button is-primary is-small is-fullwidth is-outlined">View Group</a>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <?php if($pages > 1) { ?>
                        <div class="glass-box mt-5 p-3">
                            <nav class="pagination is-centered" role="navigation" aria-label="pagination">  
                                <a role="button" <?php if($page-1 != 0) { echo 'href="/groups?'; if(isset($_GET['query'])) { echo 'query='.$_GET['query;'].'&'; } echo 'page='.($page-1).'"'; } ?><?php if($page-1 == 0) { echo 'disabled="disabled" ';} ?>class="pagination-link has-text-white glass-box is-hidden-mobile"><i class=" fas fa-chevron-left fa-sm"></i></a>
                                <ul class="pagination-list">
                                    <?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {
                                        if ($i <= $pages) { 
                                            echo '<li><a href="/groups?'; 
                                            if(isset($_GET['query'])) { echo 'query='.$_GET['query;'].'&'; } 
                                            echo 'page='.$i.'" ';
                                            if($i == $page) { echo 'aria-current="true"'; } 
                                            echo ' class="pagination-link has-text-white glass-box'; 
                                            if($i == $page) { echo ' is-current has-background-primary'; } 
                                            echo '" style="'.($i == $page ? 'border-color: var(--primary);' : '').'">'.$i.'</a></li>'; 
                                        } 
                                    } ?>
                                </ul>
                                <a role="button" <?php if($pages > 1 || $page+1 < $pages) { echo ' href="/groups?'; if(isset($_GET['query'])) { echo 'query='.$_GET['query;'].'&'; } echo 'page='.($page+1).'"'; } ?> <?php if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled" ';} ?>class="is-hidden-mobile pagination-next has-text-white glass-box"><i class="fas fa-chevron-right fa-sm"></i></a>
                            </nav>
                        </div>
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