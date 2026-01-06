<?php
$title = 'Admin Control Panel';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {
    die();

}

if($AUTH && $my->administrator == 0) {
    die();

}

$getGroups = $db->prepare('SELECT * FROM user_groups WHERE moderation_status = 0');
$getGroups->execute();


if(isset($_POST['group_id']) && isset($_POST['accept_true']) && $AUTH && $my->administrator > 0) {

    $updateAll = $db->prepare('UPDATE user_groups SET moderation_status = 1 WHERE id = ?');
    $updateAll->bindValue(1, $_POST['group_id'], PDO::PARAM_INT);
    $updateAll->execute();
  
  header('Location: /admin/review/groups');

}

if(isset($_POST['group_id']) && isset($_POST['accept_false']) && $AUTH && $my->administrator > 0) {

    $updateAll = $db->prepare('UPDATE user_groups SET moderation_status = 2 WHERE id = ?');
    $updateAll->bindValue(1, $_POST['group_id'], PDO::PARAM_INT);
    $updateAll->execute();
  
  header('Location: /admin/review/groups');

}
?>
<section class="section">
<div class="container">
        <div class="columns">
            <div class="column is-3 ">
                <aside class="menu">
                    <div class="box">
                    <p class="menu-label">
                        General
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin">Dashboard</a></li>
                    </ul>
                    <p class="menu-label">
                        Administration
                    </p>
                    <ul class="menu-list">
                        <li><a class="is-active" href="/admin/review">Reviews</a></li>
                        <li>
                            <a href="/admin/reports/">Reports</a>
                        </li>
                    </ul>
                    <p class="menu-label">
                        Assets Creation
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/store/create/hats">Hats</a></li>
                        <li><a href="/admin/store/create/face">Faces</a></li>
                        <li><a href="/admin/store/create/gear">Gears</a></li>
                        <li><a href="#">Heads</a></li>
                    </ul>
                    <p class="menu-label">
                        Transactions
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/coins/">Coins</a></li>
                        <li><a>Verified</a></li>
                        <li><a href="/admin/premium/">Premiun</a></li>
                        <li><a>Prestige</a></li>
                    </ul>
                </aside>
            </div>
<div class="column is-9">
<div class="box">
<div class="tabs is-centered">
<div class="b-tabs is-fullwidth"><nav role="tablist" aria-orientation="horizontal" class="tabs is-toggle is-fullwidth"><ul>
  <li role="presentation" ><a role="tab" href="/admin/review/">Clothing</a></li>
    <li role="presentation" class="is-active"><a role="tab">Groups</a></li>
<li role="presentation"><a href="/admin/review/adverts/"role="tab">Adverts</a></li>
  </ul>
</nav>

                    <tabs type="is-centered">
                    <?php if($getGroups->rowCount() == 0) { ?>
<p class="is-fullwidth has-text-centered">No pending groups yet</p>
<?php } else { ?>
<div class="columns is-multiline">
<?php while($gg = $getGroups->fetch(PDO::FETCH_OBJ)) { ?>
<div class="column has-text-centered">
<a href="/groups/view/?id=<?php echo $gg->id; ?>"><img style="width:256px; height:256px;" src="/assets/images/group/<?php echo $gg->logo; ?>?r=<?php echo bin2hex(random_bytes(16)); ?>" alt=""></a>
<p><?php echo htmlspecialchars($gg->name); ?></p>
<form method="post">
<button type="submit" class="button is-success" name="accept_true">Accept</button>
<input type="hidden" name="group_id" value="<?php echo $gg->id; ?>">
<input type="hidden" name="logo" value="<?php echo $gg->logo; ?>">
</form>
<div style="margin-bottom: 5px;"></div>
<form method="post">
<button type="submit" class="button is-danger" name="accept_false">Decline</button>
<input type="hidden" name="group_id" value="<?php echo $gg->id; ?>">
</form>
</div>
<?php } ?>
</div>
<?php } ?>
</tabs>
</div>
</div>
<div class="column"></div>
</div>
</div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php');
?>