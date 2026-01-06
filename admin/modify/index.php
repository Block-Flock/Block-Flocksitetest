<?php
$title = 'Modify User';
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    header('Location: /');
    die();

}

if($my->administrator < 1) {

    header('Location: /');
    die();

}

if(!isset($_GET['id'])) {

    die('Please specify a user ID.');

}

if(!is_numeric($_GET['id'])) {

    die('Please specify a valid user ID.');

}

$selectModify = $db->prepare('SELECT * FROM users WHERE id = ?');
$selectModify->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$selectModify->execute();

if($selectModify->rowCount() == 0) {

    die('User not found. Please specify a valid user ID.');

}

$sm = $selectModify->fetch(PDO::FETCH_OBJ);

if($sm->administrator >= 1) {
    
    die('This user is an administrator. You cannot edit them.');
    
}

if(isset($_POST['scrub_username']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('UPDATE users SET username = ? WHERE id = ?');
    $scrub->bindValue(1, 'Deleted('.$_GET['id'].')', PDO::PARAM_STR);
    $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Scrubbed Username', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();
  
  context::sendDiscordAdminLogs($my->username.' has scrubbed the username for user ID **'.$_GET['id'].'**');

    header('Location: /user/'.$_GET['id']);
    die();

}

if(isset($_POST['scrub_status']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('UPDATE users SET `status` = ? WHERE id = ?');
    $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
    $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Scrubbed Status', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();
  
  context::sendDiscordAdminLogs($my->username.' has scrubbed the status for user ID **'.$_GET['id'].'**');

    header('Location: /user/'.$_GET['id']);
    die();

}

if(isset($_POST['scrub_blurb']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('UPDATE users SET `blurb` = ? WHERE id = ?');
    $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
    $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Scrubbed blurb', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();
  
  context::sendDiscordAdminLogs($my->username.' has scrubbed the blurb for user ID **'.$_GET['id'].'**');

    header('Location: /user/'.$_GET['id']);
    die();

}

if(isset($_POST['unban']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('DELETE FROM user_bans WHERE user = ?');
    $scrub->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Has Unban', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();
  
  context::sendDiscordAdminLogs($my->username.' has Unban user ID **'.$_GET['id'].'**');

    header('Location: /user/'.$_GET['id']);
    die();

}

if(isset($_POST['scrub_description']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('UPDATE users SET `description` = ? WHERE id = ?');
    $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
    $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Scrubbed Description', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();
  
  context::sendDiscordAdminLogs($my->username.' has scrubbed the description for user ID **'.$_GET['id'].'**');

    header('Location: /user/'.$_GET['id']);
    die();

}

if(isset($_POST['ban']) && isset($_POST['ban_note']) && isset($_POST['ban_reason']) && isset($_POST['ban_time']) && $AUTH && $my->administrator >= 1) {

    $selban = $db->prepare("SELECT * FROM user_bans WHERE user = ?");
    $selban->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $selban->execute();
    
    
    $insban = $db->prepare("INSERT INTO user_bans (user, admin, moderator_note , reason, ban_time, ban_when) VALUES (?, ?,?, ?, ?, ?)");
    $insban->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $insban->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $insban->bindValue(3, $_POST["ban_reason"], PDO::PARAM_STR);
    $insban->bindValue(4, $_POST["ban_note"], PDO::PARAM_STR);
    $insban->bindValue(5, $_POST["ban_time"], PDO::PARAM_INT);
    $insban->bindValue(6, time(), PDO::PARAM_INT);
    $insban->execute();
  
  context::sendDiscordAdminLogs($my->username.' has banned user ID **'.$_GET['id'].'** for the reason of **'.$_POST['ban_reason'].'** with a ban note that says **'.$_POST['ban_note'].'** and a time of **'.$_POST['ban_time'].'**');
    
    header('Location: /user/'.$_GET['id']);
    die();

}
?>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column"></div>
            <div class="column is-7">
                <div class="box">
                    <div class="push-5"></div>
                    <form method="post" style="justify-content: center;">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">        
                        <button type="submit" style="display: inline-block;" class="button is-danger is-fullwidth" name="scrub_username" onclick="this.classList.toggle('button--loading')"><span class="button__text"><b>Scrub Username</b></span></button>
                    </form>
                    <div class="push-5"></div>
                    <form method="post" style="justify-content: center;">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        <button type="submit" style="display: inline-block;" class="button is-danger is-fullwidth" name="scrub_blurb" onclick="this.classList.toggle('button--loading')"><span class="button__text"><b>Scrub Blurb</b></span></button>
                    </form>
                    <div class="push-5"></div>
                    <form method="post" style="justify-content: center;">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        <button type="submit" style="display: inline-block;" class="button is-danger is-fullwidth" name="scrub_status" onclick="this.classList.toggle('button--loading')"><span class="button__text"><b>Scrub Status</b></span></button>
                    </form>
                    <div class="push-5"></div>
                    <form method="post" style="justify-content: center;">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        <button type="submit" style="display: inline-block;" class="button is-danger is-fullwidth" name="unban" onclick="this.classList.toggle('button--loading')"><span class="button__text"><b>Unban User</b></span></button>
                    </form>
                    <div class="push-5"></div>
                    <form method="post">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                    <div class="push-5"></div>
                    <label class='label'>Reason</label>
                    <div class="select is-fullwidth ">
            <select name="ban_reason" required>
                <option value="Name Hoarding">Name Hoarding</option>
                <option value="Alt Hoarding">Alt Hoarding</option>
                <option value="Ban Request">Ban Request</option>
                <option value="False Info">False Info</option>
                <option value="Coinfarming">Coinfarming</option>
                <option value="Derogatory Language">Derogatory Language</option>
                <option value="Inappropriate Language">Inappropriate Language</option>
                <option value="Inappropriate Discussion">Inappropriate Discussion</option>
                <option value="Stealing Clothing">Stealing Clothing</option>
                <option value="Account Transfer">Account Transfer</option>
                <option value="Unauthorized Account Access">Unauthorized Account Access</option>
                <option value="Exploiting Vulnerabilities">Exploiting Vulnerabilities</option>
                <option value="False Report">False Report</option>
                <option value="Others">Others</option>
            </select>
            </div>
            <div class="push-5"></div>
            <label class='label'>Ban Note</label>
                        <textarea type="text" class="textarea"  placeholder="Ban Reason" name="ban_note" required></textarea>
                        <div class="push-5"></div>
                        <label class='label'>Ban Length</label>
                        <div class="select">
            <select name="ban_time" required>
                <option value="<?php echo time(); ?>">Warning</option>
                <option value="<?php echo time()+3600 ; ?>">1 Hour</option>
                <option value="<?php echo time()+43200 ; ?>">12 Hour</option>
                <option value="<?php echo time()+86400; ?>">1 Day</option>
                <option value="<?php echo time()+604800; ?>">7 Days</option>
                <option value="<?php echo time()+1209600; ?>">14 Days</option>
                <option value="<?php echo time()+2592000; ?>">1 Month</option>
                <option value="<?php echo 2082715200; ?>">Forever</option>
            </select>
            </div>
        <div class="push-5"></div>
                        <button name="ban" type="submit" class="button is-danger" onclick="this.classList.toggle('button--loading')"><span class="button__text"><b>Ban</b></span></button>
                </div>
                </form>
            </div>
            <div class="column"></div>
        </div>
    </div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php');
 ?>
