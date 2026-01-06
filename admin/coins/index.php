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
                        <li><a class="is-active">Coins</a></li>
                        <li><a>Verified</a></li>
                        <li><a href="/admin/premium">Premiun</a></li>
                        <li><a>Prestige</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-5">
                <div class="box">
                    <?php if(isset($_POST['grantCurrency'])){
    $wavescripts = htmlspecialchars($_POST['id']);
    $Currency = htmlspecialchars($_POST['coins']);
  $addCurrency = $db->prepare("UPDATE `users` SET `Currency` = `Currency` + :gimme WHERE `id` = :uIDD");
  $addCurrency->bindParam(":gimme", $Currency, PDO::PARAM_INT);
  $addCurrency->bindParam(":uIDD", $wavescripts, PDO::PARAM_INT);
  $addCurrency->execute();
  $loginsert = $db->prepare('INSERT INTO user_history (admin, user, action, timemoderated) VALUES (?, ?, ?, ?)');
  $loginsert->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
  $loginsert->bindValue(2, $wavescripts, PDO::PARAM_INT);
  $loginsert->bindValue(3, 'Granted '.$Currency.' Coins', PDO::PARAM_STR);
  $loginsert->bindValue(4, time(), PDO::PARAM_INT);
  $loginsert->execute();
  //your welcome for logs kinda sus of mods rn -Intel
  
  context::sendDiscordAdminLogs($my->username.' has awarded **'.$Currency.'** coins to user ID **'.$wavescripts.'**');
  
  $strGrant = "Granted ".$Currency." coins to user ID ".$wavescripts."";
                    }
?>
                <form action="" method="POST">
                     <div class='field'>
                            <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
                     </div>
                        <div class='field'>
                            <label class='label'>ID</label>
                                <input type='number' name='id' class='input' required>
                        </div>
                        <div class='field'>
                            <label class='label'>Coins</label>
                                <input type='number' name='coins' class='input' required>
                        </div>
                        <div class='field'>
                            <button type='submit'  name='grantCurrency' class='button is-primary' onclick="this.classList.toggle('button--loading')"><span class="button__text">Submit</span></button>
                        </div>
      </form>
</div>
</div>
      <div class="column">
<div class="box is-hidden-tablet-only is-hidden-mobile">
<div class="title is-size-4">Rules and Regulations</div>
<strong>#1</strong>
<p class="has-text-grey-light">Do not grant yourself any sort of currency without permission.</p>
<strong>#2</strong>
<p class="has-text-grey-light">Do not grant anyone who doesn't show proof that they're truly in need of a refund.</p>
<strong>#3</strong>
<p class="has-text-danger">Do not abuse this feature if do so you will be removed off the team.</p>
</div>
</div>
</div>
                </section>