<?php
$title = "Change E-mail | Settings";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth($AUTH, true);


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
                <li><a href="/user/settings/account/password">Change Password</a></li>
                <li><a class="is-active" href="/user/settings/account/email">Change Email</a></li>
</ul>
                    <p class="menu-label">Other</p>
                    <ul class="menu-list">
                        <li><a href="#">Billing</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">
                <div class="box">
                    <h2 class="is-size-4">Change email</h2>
                    <p class="has-text-grey-light">Need to change your e-mail address? You can do so here.
                    </p>
                    <div class="field">
                        <label class="label">New e-mail address</label>
                        <div class="control">
                            <input class="input" id="newEmail" type="email"
                                placeholder="Please enter your new e-mail address">
                            <p class="help" id="newEmailMessage"></p>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Confirm new e-mail address</label>
                        <div class="control">
                            <input class="input" id="newEmailConfirm" type="email"
                                placeholder="Please confirm your new e-mail address">
                            <p class="help" id="newEmailConfirmMessage"></p>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Enter your password</label>
                        <div class="control">
                            <input class="input" id="password" type="password" placeholder="Please enter your password"
                                style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAASCAYAAABSO15qAAAAAXNSR0IArs4c6QAAAPhJREFUOBHlU70KgzAQPlMhEvoQTg6OPoOjT+JWOnRqkUKHgqWP4OQbOPokTk6OTkVULNSLVc62oJmbIdzd95NcuGjX2/3YVI/Ts+t0WLE2ut5xsQ0O+90F6UxFjAI8qNcEGONia08e6MNONYwCS7EQAizLmtGUDEzTBNd1fxsYhjEBnHPQNG3KKTYV34F8ec/zwHEciOMYyrIE3/ehKAqIoggo9inGXKmFXwbyBkmSQJqmUNe15IRhCG3byphitm1/eUzDM4qR0TTNjEixGdAnSi3keS5vSk2UDKqqgizLqB4YzvassiKhGtZ/jDMtLOnHz7TE+yf8BaDZXA509yeBAAAAAElFTkSuQmCC&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
                            <p class="help" id="passwordMessage"></p>
                        </div>
                    </div>
                    <p class="help" id="message"></p>
                    <button class="button is-info" id="changeEmailBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$app->footer();
?>