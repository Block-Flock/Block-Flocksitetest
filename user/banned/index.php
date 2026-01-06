<?php
$title = "Account Suspended";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);

$getBan = $db->prepare('SELECT * FROM user_bans WHERE user = ? AND expired = 0');
$getBan->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$getBan->execute();

if($getBan->rowCount() == 0) {
    header('Location: /dashboard');
    die;

}

$gb = $getBan->fetch(PDO::FETCH_OBJ);

if(isset($_POST['reactivate']) && $getBan->rowCount() > 0 && $gb->ban_time <= time()) {

    $updateban = $db->prepare('UPDATE user_bans SET expired = 1 WHERE user = ?');
    $updateban->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $updateban->execute();

    header('Location: /dashboard');

}
?>

<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
            </div>
            <div class="column is-6">
<article class="message is-danger">
  <div class="message-header">
    <p>Account suspended</p>
  </div>
  <div class="message-body">
  Our content monitors have concluded that your actions on Bricktaria were in violation of our Terms of Service. We want to ensure that everyone on the Bricktaria platform can have a fun and better experience. We do not tolerate bad behaviors in our platform.<br><br>
  If you have any questions, concerns, or comments relating to this suspension, we advise that you email our <a href="mailto:support@bricktaria.com">support</a> department. <br>If this is a misunderstanding please e-email us an appeal regarding the suspension of your account in <a href="mailto:appeal@bricktaria.com">here</a>
  <textarea class="textarea" disabled="true">
      <?php echo $gb->reason; ?>
    </textarea> 
  </div>
  <?php if($gb->ban_time <= time()) { ?>
<form method="post">
<button name="reactivate" class="button is-fullwidth is-danger" style="border-radius: 0px;"><b>Re-activate Account</b></button>
</form>
<?php }?>
</article>
<?php if($gb->ban_time > time()) { ?> <subtitle class="subtitle is-7 has-text-weight-light">Your suspension will expire at <?php echo date('g:ia \o\n l jS F Y', $gb->ban_time); ?></subtitle> <?php } ?>
</div>
</div>
</div>
</section>

<?php
$app->footer();
?>