<?php
$title = 'Admin Control Panel';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {
    die();

}

if($AUTH && $my->administrator == 0) {
    die();

}

$getAds = $db->prepare('SELECT * FROM user_ads WHERE active = 0');
$getAds->execute();


if(isset($_POST['ad_id']) && isset($_POST['aaccept_true']) && $AUTH && $my->administrator > 0) {

    $updateAll = $db->prepare('UPDATE user_ads SET active = 1 WHERE id = ?');
    $updateAll->bindValue(1, $_POST['ad_id'], PDO::PARAM_INT);
    $updateAll->execute();

}

if(isset($_POST['ad_id']) && isset($_POST['aaccept_false']) && $AUTH && $my->administrator > 0) {

    $updateAll = $db->prepare('UPDATE user_ads SET active = 2 WHERE id = ?');
    $updateAll->bindValue(1, $_POST['ad_id'], PDO::PARAM_INT);
    $updateAll->execute();

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
    <li role="presentation" ><a role="tab" href="/admin/review/">Groups</a></li>
  <li role="presentation" class="is-active"><a role="tab">Adverts</a></li>
  </ul>
</nav>

                    <tabs type="is-centered">
                    <?php if($getAds->rowCount() == 0) { ?>
<p class="is-fullwidth has-text-centered">No pending adverts yet</p>
<?php } else { ?>
<div class="columns is-multiline">
<?php while($ga = $getAds->fetch(PDO::FETCH_OBJ)) { ?>
<div class="column has-text-centered">
<a href="#"><img style="width:256px; height:256px;" src="/assets/images/landing/<?php echo $ga->thumbnail; ?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>" alt=""></a>
<p>Creator: <?php echo htmlspecialchars($ga->creator); ?></p>
<form method="post">
<button type="submit" class="button is-success" name="aaccept_true">Accept</button>
<input type="hidden" name="ad_id" value="<?php echo $ga->id; ?>">
<input type="hidden" name="logo" value="<?php echo $ga->thumbnail; ?>">
</form>
<div style="margin-bottom: 5px;"></div>
<form method="post">
<button type="submit" class="button is-danger" name="aaccept_false">Decline</button>
<input type="hidden" name="ad_id" value="<?php echo $ga->id; ?>">
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