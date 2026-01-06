<?php $title = 'Admin Control Panel';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
if(!$AUTH) {
    die();

}
if($AUTH && $my->administrator == 0) {
    die();

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
                        <li><a href="/admin/">Dashboard</a></li>
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
                        <li><a class="is-active">Premiun</a></li>
                        <li><a>Prestige</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-5">
                <div class="box">
<?php
if(isset($_POST['grantPremium'])){
  $wavescripts = htmlspecialchars($_POST['id']);
  $kOne = 1;
$addPrem = $db->prepare("UPDATE `users` SET `premium` = :oneVal WHERE `id` = :wave");
$addPrem->bindParam(":oneVal", $kOne, PDO::PARAM_INT);
$addPrem->bindParam(":wave", $wavescripts, PDO::PARAM_INT);
$addPrem->execute();
$loginsert = $db->prepare('INSERT INTO user_history (admin, user, action, timemoderated) VALUES (?, ?, ?, ?)');
$loginsert->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$loginsert->bindValue(2, $wavescripts, PDO::PARAM_INT);
$loginsert->bindValue(3, 'Granted Premium', PDO::PARAM_STR);
$loginsert->bindValue(4, time(), PDO::PARAM_INT);
$loginsert->execute();
//your welcome for logs kinda sus of mods rn -Intel
  
  context::sendDiscordAdminLogs($my->username.' has awarded premium to user ID **'.$wavescripts.'**');
  
$strGrant2 = "Granted premium membership to user ID ".$wavescripts."";

if ($my->premium_reward< time()) {
        $query = $app->query("UPDATE users SET premium_expired = " . (time() + 86400) . " WHERE id = " . $my->id . "");
        $query->execute();
    }

echo"<b style='color:green;'>Successfully granted premium.</b>";

} ?>
                <form action="" method="POST">
                     <div class='field'>
                            <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
                     </div>
                        <div class='field'>
                            <label class='label'>ID</label>
                                <input type='number' name="id" class='input' required>
                        </div>
                        <div class='field'>
                            <button type='submit'  name='grantPremium' class='button is-primary' onclick="this.classList.toggle('button--loading')"><span class="button__text">Submit</span></button>
                        </div>
      </form>
</div>
</div>
</section>