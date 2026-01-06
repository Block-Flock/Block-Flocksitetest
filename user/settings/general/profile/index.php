<?php
$title = "Profile | Settings";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);
if(isset($_POST['blurb']) && $AUTH) {

    $check = new Check();

    $blurb = $check->obfuscateIfProfane($_POST['blurb']);

    if(strlen($blurb) > 255) {

        $err = true;
        echo "Your blurb cannot be longer than 255 characters.";
    }

    if(count(preg_split("/\n|\r/", $blurb)) > 9){
      $err = true;
      echo "You cannot have more than 5 lines/rows in your blurb!";
    }

    if(!isset($err)) {

        $update = $db->prepare('UPDATE users SET `blurb` = ? WHERE id = ?');
        $update->bindValue(1, $blurb, PDO::PARAM_STR);
        $update->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $update->execute();

        header('Location: /user/settings/general/profile/');
        die;

    }

}
?>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column">
                <aside class="menu">
                <div class="">
                    <p class="menu-label">General</p>
                    <ul class="menu-list">
                        <li><a id="information_btn" href="/user/settings">Information</a></li>
                        <li><a id="profile_btn" href="/user/settings/general/profile" class="is-active">Profile</a></li>
                    </ul>
<ul class="menu-list">
 <p class="menu-label">Account</p>
                <li><a href="/user/settings/account/username">Change Username</a></li>
                <li><a href="/user/settings/account/password">Change Password</a></li>
                <li><a href="/user/settings/account/email">Change Email</a></li>
</ul>
                    <p class="menu-label">Other</p>
                    <ul class="menu-list">
                        <li><a href="#">Billing</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">
                <div class="box">
<?php if(isset($err)) { ?><div class="has-text-danger"><?php echo $err; ?></div><?php } ?>
                    <h2 class="is-size-4">Change blurb</h2>
                    <p class="has-text-grey-light">Tell us something about yourself! </p>
                        <form method="post">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                    <textarea name="blurb" class="textarea" maxlength="255"
                        placeholder="Tell something about yourself. Keep it short and simple and make sure to not share personal details."><?= nl2br($my->blurb); ?></textarea>
                    <div class="push-5"></div>
                    <button class="button is-info">Save changes</button>
                </div>
</form>
            </div>
        </div>
    </div>
</section>
<?php
$app->footer();
?>













