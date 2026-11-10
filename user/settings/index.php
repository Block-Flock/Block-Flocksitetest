<?php
$title = "Settings";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);
?>
<section class="section">
    <div class="container">
        <div class="columns"> 
            <div class="column is-3">
                <aside class="menu settings-menu glass-box" style="padding: 1.5rem;">
                    <p class="menu-label">General</p>
                    <ul class="menu-list">
                        <li><a id="information_btn" href="/user/settings" class="is-active">Information</a></li>
                        <li><a id="profile_btn" href="/user/settings/general/profile">Profile</a></li>
                        <li><a href="/user/settings/profile-customization">Profile Customization</a></li>
                    </ul>
                    <p class="menu-label mt-4">Account</p>
                    <ul class="menu-list">
                        <li><a href="/user/settings/account/username">Change Username</a></li>
                        <li><a href="/user/settings/account/password">Change Password</a></li>
                        <li><a href="/user/settings/account/email">Change Email</a></li>
                    </ul>
                    <p class="menu-label mt-4">Other</p>
                    <ul class="menu-list">
                        <li><a href="#">Billing</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">
                <div class="glass-box">
                    <h2 class="title is-4 mb-5">General Information</h2>
                    
                    <div class="field mb-4">
                        <label class="label">Username</label>
                        <div class="control">
                            <input class="input is-static has-text-white" type="text" value="<?= $my->username; ?>" readonly>
                        </div>
                    </div>

                    <div class="field mb-4">
                        <label class="label">User ID</label>
                        <div class="control">
                            <input class="input is-static has-text-white" type="text" value="<?= $my->id; ?>" readonly>
                        </div>
                    </div>

                    <div class="field mb-4">
                        <label class="label">Email</label>
                        <div class="control">
                            <input class="input is-static has-text-white" type="text" value="<?=preg_replace('/[^@]+@([^\s]+)/', ''.substr($my->email, 0, 3).'********@$1', $my->email).''?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$app->footer();
?>












