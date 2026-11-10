<?php
$title = "Theme Settings";
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
                        <li><a href="/user/settings">Information</a></li>
                        <li><a href="/user/settings/general/profile">Profile</a></li>
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
                        <li><a href="/user/settings/other/theme" class="is-active">Theme</a></li>
                        <li><a href="#">Billing</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">
                <div class="glass-box">
                    <h2 class="title is-4 mb-5">Theme Settings</h2>
                    
                    <div class="field">
                        <label class="label">Dark/Light Mode</label>
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" id="theme-toggle-btn">
                                Enable Light Mode
                            </label>
                        </div>
                        <p class="help is-info">Toggle between the classic dark theme and the new light theme.</p>
                    </div>

                    <hr class="dropdown-divider">

                    <h3 class="title is-5 mt-5">Preview</h3>
                    <div class="box">
                        <p>This is how content boxes will look with your current theme.</p>
                        <button class="button is-primary mt-2">Primary Button</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<?php
$app->footer();
?>
