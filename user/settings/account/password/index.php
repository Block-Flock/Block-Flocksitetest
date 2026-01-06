<?php
$title = "Change Password | Settings";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth($AUTH, true);
if(isset($_POST['curpass']) && isset($_POST['newpass']) && isset($_POST['confnewpass']) && $AUTH) {

    if(!password_verify($_POST['curpass'], $my->password)) {

        $err = "Your current password does not match your account's password";

    } else if($_POST['newpass'] == $_POST['curpass'] && password_verify($_POST['curpass'], $my->password)) {

        $err = "Your new password cannot match your current password";

    } else if(!hash_equals($_POST['newpass'], $_POST['confnewpass'])) {

        $err = "Your new password and confirm password must be the same.";

    }

    if(!isset($err)) {

        $updatepass = $db->prepare('UPDATE users SET `password` = ? WHERE id = ?');
        $updatepass->bindValue(1, password_hash($_POST['newpass'], PASSWORD_BCRYPT), PDO::PARAM_STR);
        $updatepass->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $updatepass->execute();
        $InsertUserActionLog = $db->prepare("INSERT INTO useractionlogs (UserID, Action, TimeLog, IP) VALUES(".$my->id.", ?, ".time().", ?)");
        $InsertUserActionLog->bindValue(1, 'Changed their account password', PDO::PARAM_STR);
        $InsertUserActionLog->bindValue(2, $my->ip, PDO::PARAM_STR);
        $InsertUserActionLog->execute();
header('Location: /user/settings/account/password');
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
                        <li><a id="profile_btn" href="/user/settings/general/profile">Profile</a></li>
                    </ul>
<ul class="menu-list">
 <p class="menu-label">Account</p>
                <li><a href="/user/settings/account/username">Change Username</a></li>
                <li><a href="/user/settings/account/password" class="is-active">Change Password</a></li>
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
                    <h2 class="is-size-4">Change password</h2>
                    <p class="has-text-grey-light">If you wish to change your password, please complete the form with
                        the required information. Upon changing your password, all other sessions will be logged out
                        immediately.</p>
                        <form method="post">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                    <div class="field">
                        <label class="label">Enter your current password</label>
                        <div class="control">
                            <input class="input" name="curpass" type="password"
                                placeholder="Please enter your current password"
                                style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACIUlEQVQ4EX2TOYhTURSG87IMihDsjGghBhFBmHFDHLWwSqcikk4RRKJgk0KL7C8bMpWpZtIqNkEUl1ZCgs0wOo0SxiLMDApWlgOPrH7/5b2QkYwX7jvn/uc//zl3edZ4PPbNGvF4fC4ajR5VrNvt/mo0Gr1ZPOtfgWw2e9Lv9+chX7cs64CS4Oxg3o9GI7tUKv0Q5o1dAiTfCgQCLwnOkfQOu+oSLyJ2A783HA7vIPLGxX0TgVwud4HKn0nc7Pf7N6vV6oZHkkX8FPG3uMfgXC0Wi2vCg/poUKGGcagQI3k7k8mcp5slcGswGDwpl8tfwGJg3xB6Dvey8vz6oH4C3iXcFYjbwiDeo1KafafkC3NjK7iL5ESFGQEUF7Sg+ifZdDp9GnMF/KGmfBdT2HCwZ7TwtrBPC7rQaav6Iv48rqZwg+F+p8hOMBj0IbxfMdMBrW5pAVGV/ztINByENkU0t5BIJEKRSOQ3Aj+Z57iFs1R5NK3EQS6HQqF1zmQdzpFWq3W42WwOTAf1er1PF2USFlC+qxMvFAr3HcexWX+QX6lUvsKpkTyPSEXJkw6MQ4S38Ljdbi8rmM/nY+CvgNcQqdH6U/xrYK9t244jZv6ByUOSiDdIfgBZ12U6dHEHu9TpdIr8F0OP692CtzaW/a6y3y0Wx5kbFHvGuXzkgf0xhKnPzA4UTyaTB8Ph8AvcHi3fnsrZ7Wore02YViqVOrRXXPhfqP8j6MYlawoAAAAASUVORK5CYII=&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%;">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">New password</label>
                        <div class="control">
                            <input class="input" name="newpass" type="password"
                                placeholder="Please enter your new password"
                                style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACIUlEQVQ4EX2TOYhTURSG87IMihDsjGghBhFBmHFDHLWwSqcikk4RRKJgk0KL7C8bMpWpZtIqNkEUl1ZCgs0wOo0SxiLMDApWlgOPrH7/5b2QkYwX7jvn/uc//zl3edZ4PPbNGvF4fC4ajR5VrNvt/mo0Gr1ZPOtfgWw2e9Lv9+chX7cs64CS4Oxg3o9GI7tUKv0Q5o1dAiTfCgQCLwnOkfQOu+oSLyJ2A783HA7vIPLGxX0TgVwud4HKn0nc7Pf7N6vV6oZHkkX8FPG3uMfgXC0Wi2vCg/poUKGGcagQI3k7k8mcp5slcGswGDwpl8tfwGJg3xB6Dvey8vz6oH4C3iXcFYjbwiDeo1KafafkC3NjK7iL5ESFGQEUF7Sg+ifZdDp9GnMF/KGmfBdT2HCwZ7TwtrBPC7rQaav6Iv48rqZwg+F+p8hOMBj0IbxfMdMBrW5pAVGV/ztINByENkU0t5BIJEKRSOQ3Aj+Z57iFs1R5NK3EQS6HQqF1zmQdzpFWq3W42WwOTAf1er1PF2USFlC+qxMvFAr3HcexWX+QX6lUvsKpkTyPSEXJkw6MQ4S38Ljdbi8rmM/nY+CvgNcQqdH6U/xrYK9t244jZv6ByUOSiDdIfgBZ12U6dHEHu9TpdIr8F0OP692CtzaW/a6y3y0Wx5kbFHvGuXzkgf0xhKnPzA4UTyaTB8Ph8AvcHi3fnsrZ7Wore02YViqVOrRXXPhfqP8j6MYlawoAAAAASUVORK5CYII=&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%;">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Confirm new password</label>
                        <div class="control">
                            <input class="input" name="confnewpass" type="password"
                                placeholder="Please confirm your new password"
                                style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACIUlEQVQ4EX2TOYhTURSG87IMihDsjGghBhFBmHFDHLWwSqcikk4RRKJgk0KL7C8bMpWpZtIqNkEUl1ZCgs0wOo0SxiLMDApWlgOPrH7/5b2QkYwX7jvn/uc//zl3edZ4PPbNGvF4fC4ajR5VrNvt/mo0Gr1ZPOtfgWw2e9Lv9+chX7cs64CS4Oxg3o9GI7tUKv0Q5o1dAiTfCgQCLwnOkfQOu+oSLyJ2A783HA7vIPLGxX0TgVwud4HKn0nc7Pf7N6vV6oZHkkX8FPG3uMfgXC0Wi2vCg/poUKGGcagQI3k7k8mcp5slcGswGDwpl8tfwGJg3xB6Dvey8vz6oH4C3iXcFYjbwiDeo1KafafkC3NjK7iL5ESFGQEUF7Sg+ifZdDp9GnMF/KGmfBdT2HCwZ7TwtrBPC7rQaav6Iv48rqZwg+F+p8hOMBj0IbxfMdMBrW5pAVGV/ztINByENkU0t5BIJEKRSOQ3Aj+Z57iFs1R5NK3EQS6HQqF1zmQdzpFWq3W42WwOTAf1er1PF2USFlC+qxMvFAr3HcexWX+QX6lUvsKpkTyPSEXJkw6MQ4S38Ljdbi8rmM/nY+CvgNcQqdH6U/xrYK9t244jZv6ByUOSiDdIfgBZ12U6dHEHu9TpdIr8F0OP692CtzaW/a6y3y0Wx5kbFHvGuXzkgf0xhKnPzA4UTyaTB8Ph8AvcHi3fnsrZ7Wore02YViqVOrRXXPhfqP8j6MYlawoAAAAASUVORK5CYII=&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%;">
                        </div>
                    </div>
                    <button class="button is-info">Submit</button>
                </div>
</form>
            </div>
        </div>
    </div>
</section>
<?php
$app->footer();
?>





