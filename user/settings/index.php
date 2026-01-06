<?php
$title = "Settings";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);
?>
<section class="section">
    <div class="container">
        <div class="columns"> 
            <div class="column">
                <aside class="menu">
                    <div class="">
                    <p class="menu-label">General</p>
                    <ul class="menu-list">
                        <li><a id="information_btn" href="/user/settings" class="is-active">Information</a></li>
                        <li><a id="profile_btn" href="/user/settings/general/profile">Profile</a></li>
                        <li><a href="/user/settings/profile-customization">Profile Customization</a></li>
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
                    <h2 class="is-size-4">General Information</h2>
                    <p><b>Username</b></p>
                    <p class="has-text-grey-light"><?= $my->username; ?></p>
                    <div class="push-5"></div>
                    <p><b>User ID</b></p>
                    <p class="has-text-grey-light"><?= $my->id; ?></p>
                    <div class="push-5"></div>
                    <p><b>Email</b></p>
                    <p class="has-text-grey-light"><?=preg_replace('/[^@]+@([^\s]+)/', ''.substr($my->email, 0, 3).'********@$1', $my->email).''?></p>
            </div>
        </div>
    </div>
</section>
<?php
$app->footer();
?>












