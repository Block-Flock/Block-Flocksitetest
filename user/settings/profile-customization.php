<?php
$title = "Profile Customization";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if (!$AUTH) {
    header('Location: /log-in');
    die;
}

// Fetch customization from the correct table as seen in user.php
$stmt = $db->prepare('SELECT * FROM user_profile_customization WHERE user_id = ?');
$stmt->execute([$_SESSION['id']]);
$custom = $stmt->fetch(PDO::FETCH_OBJ);

if (!$custom) {
    $db->prepare('INSERT INTO user_profile_customization (user_id) VALUES (?)')->execute([$_SESSION['id']]);
    $stmt->execute([$_SESSION['id']]);
    $custom = $stmt->fetch(PDO::FETCH_OBJ);
}

if (isset($_POST['save_customization']) && $AUTH) {
    // CSRF Check (using the meta or session token)
    
    $bgColor = $_POST['background_color'] ?? '#1a1a2e';
    $accentColor = $_POST['accent_color'] ?? '#6c5ce7';
    $textColor = $_POST['text_color'] ?? '#ffffff';
    $bgImage = filter_var($_POST['background_image'] ?? '', FILTER_SANITIZE_URL);
    $bgMusic = filter_var($_POST['background_music'] ?? '', FILTER_SANITIZE_URL);
    $font = $_POST['profile_font'] ?? 'Inter';
    $customCSS = strip_tags($_POST['custom_css'] ?? '');

    $update = $db->prepare('UPDATE user_profile_customization SET background_color = ?, accent_color = ?, text_color = ?, background_image = ?, background_music = ?, profile_font = ?, custom_css = ? WHERE user_id = ?');
    $update->execute([$bgColor, $accentColor, $textColor, $bgImage, $bgMusic, $font, $customCSS, $_SESSION['id']]);
    
    $successMessage = "Profile customization saved successfully!";
    
    // Refresh data
    $stmt->execute([$_SESSION['id']]);
    $custom = $stmt->fetch(PDO::FETCH_OBJ);
}
?>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
                <aside class="menu">
                    <p class="menu-label">General</p>
                    <ul class="menu-list">
                        <li><a href="/user/settings">Information</a></li>
                        <li><a href="/user/settings/general/profile">Profile</a></li>
                        <li><a href="/user/settings/profile-customization" class="is-active">Profile Customization</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">
                <div class="box">
                    <h1 class="title is-4">Profile Customization</h1>
                    <?php if (isset($successMessage)): ?>
                        <div class="notification is-success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="columns is-multiline">
                            <div class="column is-4">
                                <label class="label">Background Color</label>
                                <input class="input" type="color" name="background_color" value="<?= htmlspecialchars($custom->background_color); ?>">
                            </div>
                            <div class="column is-4">
                                <label class="label">Accent Color</label>
                                <input class="input" type="color" name="accent_color" value="<?= htmlspecialchars($custom->accent_color); ?>">
                            </div>
                            <div class="column is-4">
                                <label class="label">Text Color</label>
                                <input class="input" type="color" name="text_color" value="<?= htmlspecialchars($custom->text_color); ?>">
                            </div>
                            <div class="column is-6">
                                <label class="label">Font</label>
                                <div class="select is-fullwidth">
                                    <select name="profile_font">
                                        <?php $fonts = ['Inter', 'Roboto', 'Poppins', 'Orbitron', 'Courier New']; ?>
                                        <?php foreach($fonts as $f): ?>
                                            <option value="<?= $f ?>" <?= ($custom->profile_font == $f) ? 'selected' : '' ?>><?= $f ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="column is-12">
                                <label class="label">Background Image URL</label>
                                <input class="input" type="url" name="background_image" value="<?= htmlspecialchars($custom->background_image); ?>" placeholder="https://...">
                            </div>
                            <div class="column is-12">
                                <label class="label">Background Music URL (.mp3)</label>
                                <input class="input" type="url" name="background_music" value="<?= htmlspecialchars($custom->background_music); ?>" placeholder="https://...">
                            </div>
                            <div class="column is-12">
                                <label class="label">Custom CSS</label>
                                <textarea class="textarea" name="custom_css" rows="10"><?= htmlspecialchars($custom->custom_css); ?></textarea>
                            </div>
                        </div>
                        <div class="field mt-4">
                            <button type="submit" name="save_customization" class="button is-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php'); ?>
