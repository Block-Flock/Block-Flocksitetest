<?php $title = 'Admin Control Panel';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
         
         
if(!$AUTH) {

    die();

}

if($AUTH && $my->administrator == 0) {
    die();

}
$playersacc = $db->prepare("SELECT `id` FROM `users` ORDER BY `id` DESC LIMIT 1");
$playersacc->execute();
$players = $playersacc->fetch(PDO::FETCH_OBJ);

$items = $db->prepare("SELECT `id` FROM `store_items` ORDER BY `id` DESC LIMIT 1");
$items->execute();
$store = $items->fetch(PDO::FETCH_OBJ);  

$banned = $db->prepare("SELECT `id` FROM `user_bans` ORDER BY `id` DESC LIMIT 1");
$banned->execute();
$bans = $banned->fetch(PDO::FETCH_OBJ);  

$group = $db->prepare("SELECT `id` FROM `user_groups` ORDER BY `id` DESC LIMIT 1");
$group->execute();
$user = $group->fetch(PDO::FETCH_OBJ);  
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
                        <li><a class="is-active">Dashboard</a></li>
                    </ul>
                    <p class="menu-label">
                        Administration
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/review">Reviews</a></li>
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
                        <li><a href="/admin/premium">Premiun</a></li>
                        <li><a>Prestige</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">
                
                <section class="hero is-info welcome is-small">
                    <div class="hero-body">
                        <div class="container">
                            <h1 class="title">
                                Hello, <?= $my->username; ?>.
                            </h1>
                            <h2 class="subtitle">
                                I hope you are having a great day! We thank you for being apart of the team and helping out, be insured that you'll have rules you'd have to go by in order to remain on the team.
                            </h2>
                        </div>
                    </div>
                </section>
                <br>
                <section class="info-tiles">
                    <div class="tile is-ancestor has-text-centered">
                        <div class="tile is-parent">
                            <article class="tile is-child box">
                                <p class="title"><?php echo $players->id;?></p>
                                <p class="subtitle">Users</p>
                            </article>
                        </div>
                        <div class="tile is-parent">
                            <article class="tile is-child box">
                                <p class="title"><?php echo $store->id;?></p>
                                <p class="subtitle">Store</p>
                            </article>
                        </div>
                        <div class="tile is-parent">
                            <article class="tile is-child box">
                                <p class="title"><?php echo $bans->id;?></p>
                                <p class="subtitle">Banned</p>
                            </article>
                        </div>
                        <div class="tile is-parent">
                            <article class="tile is-child box">
                                <p class="title"><?php echo $user->id;?></p>
                                <p class="subtitle">Groups</p>
                            </article>
                        </div>
                    </div>
                </section>
                <br />
<div class="column is-5">
<div class="box">

<?php
if(isset($_POST['grant'])){
    $id = $_POST['userid'];
    $itemID = $_POST['itemid'];
if(!$id){
    echo"Please enter a valid user ID!";
}else if(!$itemID){
    echo"Please enter a valid item ID!";
}else{
    $getItemTypeQ = $db->prepare("SELECT * FROM `store_items` WHERE `id` = :itID");
    $getItemTypeQ->bindParam(":itID", $itemID, PDO::PARAM_INT);
    $getItemTypeQ->execute();
    $getItemType = $getItemTypeQ->fetch(PDO::FETCH_OBJ);

    $insert = $db->prepare("INSERT INTO `user_inventory` (`item`,`user`,`timebought`) VALUES (:item,:user,:timebought)");
    $insert->bindParam(":item", $itemID, PDO::PARAM_INT);
    $insert->bindParam(":user", $id, PDO::PARAM_INT);
    $insert->bindParam(":timebought", time(), PDO::PARAM_INT);
    $insert->execute();

    $ownerUpdate = $db->prepare('UPDATE store_items SET owners = owners + 1 WHERE id = ?');
    $ownerUpdate->bindValue(1, $_GET['userid'], PDO::PARAM_INT);
    $ownerUpdate->execute();

    echo"Successfully granted asset!";
    echo"<script>window.location.replace('/admin');</script>";
}
}
?>

<form action="#" method="POST">
<input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
<div class='field'>
    <input type="text" class="input" name="userid" placeholder="User ID">
</div>
    <div class='field'>
    <input type="text" class="input" name="itemid" placeholder="Item ID">
    </div>
    <div class='field'>
<input type="submit" class="button is-info" name="grant" value="Grant Item">
</div>
</form>
<br/>
  <?php
if(isset($_POST['submit'])){
$bannerText = htmlspecialchars($_POST['updateAlert']);
$update = $db->prepare("UPDATE `sitesettings` SET `BannerAlertMessage` = :bannerText1");
$update->bindParam(":bannerText1", $bannerText, PDO::PARAM_STR);
$update->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, 'Status Changed', PDO::PARAM_INT);
    $log->bindValue(3, 'Changed Status to '.$bannerText.'', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();


context::sendDiscordAdminLogs($my->username.' has changed the website alert to **'.$bannerText.'**');
  
  echo"Successfully updated alert!";
    echo"<script>window.location.replace('/admin');</script>";
}
?>
<form action="#" method="POST">
<div class='field'>
    <input type="text" class="input" name="updateAlert" placeholder="Alert Text">
</div>
<div class='field'>
<input type="submit" class="button is-info" name="submit" value="Submit Changes">
  </div>
</form>

							</div>
						</div>
				</div>
</div>
</div>
</div>
</section>














