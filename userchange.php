<?php
$title = "Profile";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isset($_GET['id'])) {
    header('Location: /');
    exit;
}

$id = preg_replace('/[^0-9]/', '', $_GET['id']);

// Check if user exists
$selectProfile = $db->prepare('SELECT * FROM users WHERE id = ?');
$selectProfile->bindValue(1, $id, PDO::PARAM_INT);
$selectProfile->execute();

if($selectProfile->rowCount() == 0) {
    header('Location: /');
    exit;
}

$gP = $selectProfile->fetch(PDO::FETCH_OBJ);

// Check if this user is the profile owner
$isOwner = ($AUTH && $_SESSION['id'] == $id);

// Get or create user customization
$checkCustomization = $db->prepare('SELECT * FROM user_profile_customization WHERE user_id = ?');
$checkCustomization->bindValue(1, $id, PDO::PARAM_INT);
$checkCustomization->execute();

if($checkCustomization->rowCount() == 0) {
    // Create default customization settings
    $insertCustomization = $db->prepare('INSERT INTO user_profile_customization (user_id, theme, background_color, accent_color, text_color, background_image, background_music, custom_css, profile_font) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insertCustomization->execute([$id, 'default', '#1a1a2e', '#6c5ce7', '#ffffff', '', '', '', 'Inter']);
    
    // Fetch the newly created record
    $checkCustomization->execute();
}

$userCustomization = $checkCustomization->fetch(PDO::FETCH_OBJ);

// Handle customization updates
if($isOwner && isset($_POST['save_customization'])) {
    $theme = $_POST['theme'] ?? 'default';
    $bgColor = $_POST['background_color'] ?? '#1a1a2e';
    $accentColor = $_POST['accent_color'] ?? '#6c5ce7';
    $textColor = $_POST['text_color'] ?? '#ffffff';
    $bgImage = filter_var($_POST['background_image'] ?? '', FILTER_SANITIZE_URL);
    $bgMusic = filter_var($_POST['background_music'] ?? '', FILTER_SANITIZE_URL);
    $customCSS = $_POST['custom_css'] ?? '';
    $profileFont = $_POST['profile_font'] ?? 'Inter';
    
    // Sanitize custom CSS to prevent XSS
    $customCSS = strip_tags($customCSS);
    
    $updateCustomization = $db->prepare('UPDATE user_profile_customization SET theme = ?, background_color = ?, accent_color = ?, text_color = ?, background_image = ?, background_music = ?, custom_css = ?, profile_font = ? WHERE user_id = ?');
    $updateCustomization->execute([$theme, $bgColor, $accentColor, $textColor, $bgImage, $bgMusic, $customCSS, $profileFont, $id]);
    
    header("Location: /user/".$id);
    exit;
}

// Get or create user colors
$checkColors = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
$checkColors->bindValue(1, $id, PDO::PARAM_INT);
$checkColors->execute();

if($checkColors->rowCount() == 0) {
    $insertColors = $db->prepare('INSERT INTO user_colors (user, head, torso, left_arm, right_arm, left_leg, right_leg) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $insertColors->execute([$id, '0.723055, 0.791298, 0.806952', '0.033105, 0.313989, 0.708376', '0.723055, 0.791298, 0.806952', '0.723055, 0.791298, 0.806952', '0.723055, 0.791298, 0.806952', '0.723055, 0.791298, 0.806952']);
}

// Get or create user data
$checkUserData = $db->prepare('SELECT * FROM user_data WHERE user = ?');
$checkUserData->bindValue(1, $id, PDO::PARAM_INT);
$checkUserData->execute();

if($checkUserData->rowCount() == 0) {
    $insertUserData = $db->prepare('INSERT INTO user_data (user, hat1, hat2, hat3, face, shirt, pants, gear) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $insertUserData->execute([$id, '0', '0', '0', '0', '0', '0', '0']);
}

// Ensure user has avatar_hash
if(empty($gP->avatar_hash) || $gP->avatar_hash == 'NULL') {
    $updateAvatar = $db->prepare('UPDATE users SET avatar_hash = ? WHERE id = ?');
    $updateAvatar->execute(['default', $id]);
    $gP->avatar_hash = 'default';
}

// Ensure user has blurb
if(empty($gP->blurb) || $gP->blurb == 'NULL') {
    $updateBlurb = $db->prepare('UPDATE users SET blurb = ? WHERE id = ?');
    $updateBlurb->execute(['Welcome to Avatoria!', $id]);
    $gP->blurb = 'Welcome to Avatoria!';
}

// Friend relationship check
$friends = null;
if($AUTH) {
    $selectfr = $db->prepare('SELECT * FROM user_friends WHERE (receiver = ? AND sender = ? AND status = 0) OR (sender = ? AND receiver = ? AND status = 0)');
    $selectfr->execute([$_SESSION['id'], $id, $_SESSION['id'], $id]);

    if($selectfr->rowCount() >= 1) {
        $friends = 0; // Pending request
    } else {
        $selectfr2 = $db->prepare('SELECT * FROM user_friends WHERE (sender = ? AND receiver = ? AND status = 1) OR (receiver = ? AND sender = ? AND status = 1)');
        $selectfr2->execute([$_SESSION['id'], $id, $_SESSION['id'], $id]);

        if($selectfr2->rowCount() == 1) {
            $friends = 1; // Already friends
        }
    }
}

// Get ban status
$selectBan = $db->prepare('SELECT * FROM user_bans WHERE user_id = ? AND expired = 0');
$selectBan->bindValue(1, $id, PDO::PARAM_INT);
$selectBan->execute();

// Get forum post count
$postCount = $db->prepare('SELECT COUNT(*) as count FROM forum_posts WHERE author = ?');
$postCount->execute([$id]);
$postCountData = $postCount->fetch(PDO::FETCH_OBJ);

$postCountReply = $db->prepare('SELECT COUNT(*) as count FROM forum_replies WHERE author = ?');
$postCountReply->execute([$id]);
$postCountReplyData = $postCountReply->fetch(PDO::FETCH_OBJ);

$numPosts = $postCountData->count + $postCountReplyData->count;

// Get profile views
$profilenum = 0;
try {
    $viewCount = $db->prepare('SELECT COUNT(*) as count FROM UserProfileView WHERE TargetID = ?');
    $viewCount->execute([$id]);
    $viewData = $viewCount->fetch(PDO::FETCH_OBJ);
    $profilenum = $viewData->count;
} catch (PDOException $e) {
    // Table doesn't exist yet
    $profilenum = 0;
}

// Handle friend requests
if(isset($_POST['send']) && $AUTH && $_SESSION['id'] != $id && !isset($friends)) {
    $insert = $db->prepare("INSERT INTO user_friends (receiver, sender, date, status) VALUES (?, ?, ?, 0)");
    $insert->execute([$id, $_SESSION['id'], time()]);
    header("Location: /user/".$id);
    exit;
}

if(isset($_POST['remove']) && $AUTH && $friends == 1) {
    $del = $db->prepare("DELETE FROM user_friends WHERE (sender = ? AND receiver = ?) OR (receiver = ? AND sender = ?)");
    $del->execute([$id, $_SESSION['id'], $id, $_SESSION['id']]);
    header("Location: /user/".$id);
    exit;
}

if(isset($_POST['accept']) && $AUTH) {
    $accept = $db->prepare("UPDATE user_friends SET status = 1 WHERE sender = ? AND receiver = ? AND status = 0");
    $accept->execute([$id, $_SESSION['id']]);
    header("Location: /user/".$id);
    exit;
}

if(isset($_POST['decline']) && $AUTH) {
    $decline = $db->prepare("DELETE FROM user_friends WHERE sender = ? AND receiver = ? AND status = 0");
    $decline->execute([$id, $_SESSION['id']]);
    header("Location: /user/".$id);
    exit;
}

// Admin re-render avatar
if(isset($_POST['reRenderAvatar']) && $AUTH && $my->administrator >= 1) {
    $update = $db->prepare('UPDATE users SET avatar_hash = ? WHERE id = ?');
    $update->execute(['default', $id]);
    
    $update2 = $db->prepare('UPDATE user_data SET hat1 = ?, hat2 = ?, hat3 = ?, face = ?, shirt = ?, pants = ?, gear = ? WHERE user = ?');
    $update2->execute(['0', '0', '0', '0', '0', '0', '0', $id]);

    if(class_exists('context')) {
        context::sendDiscordAdminLogs($my->username.' has re-rendered the avatar for user ID **'.$id.'**');
    }
    
    header("Location: /user/".$id);
    exit;
}

// Get friends list
$selectFriends = $db->prepare("
    (SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* 
     FROM user_friends 
     JOIN users ON user_friends.receiver = users.id 
     WHERE user_friends.status = 1 AND user_friends.sender = ? 
     ORDER BY user_friends.date DESC) 
    UNION ALL 
    (SELECT users.id as userid, users.username, users.expire, users.avatar_hash, user_friends.* 
     FROM user_friends 
     JOIN users ON user_friends.sender = users.id 
     WHERE user_friends.receiver = ? AND user_friends.status = 1 
     ORDER BY user_friends.date DESC) 
    LIMIT 8
");
$selectFriends->execute([$id, $id]);

// Track profile view
if($AUTH && $my->id != $gP->id) {
    try {
        $checkProfileView = $db->prepare("SELECT COUNT(*) as count FROM UserProfileView WHERE UserID = ? AND TargetID = ?");
        $checkProfileView->execute([$my->id, $gP->id]);
        $viewExists = $checkProfileView->fetch(PDO::FETCH_OBJ);

        if ($viewExists->count == 0) {
            $insert = $db->prepare("INSERT INTO UserProfileView (UserID, TargetID, Recurring) VALUES(?, ?, 1)");
            $insert->execute([$my->id, $gP->id]);
        } else {
            $update = $db->prepare("UPDATE UserProfileView SET Recurring = Recurring + 1 WHERE UserID = ? AND TargetID = ?");
            $update->execute([$my->id, $gP->id]);
        }
    } catch (PDOException $e) {
        // Table doesn't exist, skip
    }
}

// Get past usernames
$pastUsernames = $db->prepare('SELECT * FROM user_usernames WHERE owner = ? ORDER BY date DESC LIMIT 5');
$pastUsernames->execute([$id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($gP->username); ?>'s Profile - Avatoria</title>
    
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/buefy2.css">
    <link rel="stylesheet" href="/assets/bulma-divider.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <meta property="og:title" content="<?php echo htmlspecialchars($gP->username); ?>'s Profile">
    <meta property="og:url" content="/user/<?php echo $gP->id; ?>">
    <meta property="og:image" content="/assets/images/avatars/<?php echo htmlspecialchars($gP->avatar_hash); ?>.png">
    <meta property="og:site_name" content="Avatoria">
    <meta property="og:description" content="A social platform designed to connect players and game creation">
    
    <style>
        :root {
            --primary-gradient: <?php echo $userCustomization ? htmlspecialchars($userCustomization->background_color) : '#1a1a2e'; ?>;
            --accent-color: <?php echo $userCustomization ? htmlspecialchars($userCustomization->accent_color) : '#6c5ce7'; ?>;
            --text-color: <?php echo $userCustomization ? htmlspecialchars($userCustomization->text_color) : '#ffffff'; ?>;
            --profile-font: <?php echo $userCustomization ? htmlspecialchars($userCustomization->profile_font) : 'Inter'; ?>;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        body {
            background: linear-gradient(135deg, var(--primary-gradient), #2a5298) !important;
            <?php if($userCustomization && !empty($userCustomization->background_image)): ?>
            background-image: url('<?php echo htmlspecialchars($userCustomization->background_image); ?>') !important;
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            <?php endif; ?>
            min-height: 100vh;
            font-family: var(--profile-font), 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
        }

        .profile-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--card-shadow);
            margin: 2rem 0;
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--accent-color), #764ba2);
            padding: 2rem;
            position: relative;
            overflow: hidden;
            border-radius: 20px 20px 0 0;
        }

        .avatar-container {
            position: relative;
            display: inline-block;
        }

        .avatar-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .avatar-image:hover {
            transform: scale(1.05);
        }

        .online-indicator {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            border: 3px solid white;
        }

        .online-indicator.online {
            background: #23d160;
            box-shadow: 0 0 10px rgba(35, 209, 96, 0.6);
        }

        .online-indicator.offline {
            background: #dbdbdb;
        }

        .username-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin: 1rem 0 0.5rem 0;
        }

        .user-blurb {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            font-style: italic;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            margin: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .action-button {
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin: 0.5rem 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .stats-card {
            background: linear-gradient(135deg, var(--accent-color) 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .customization-panel {
            background: rgba(0, 0, 0, 0.95);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .customization-panel.active {
            display: block;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        .color-picker {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
            flex-wrap: wrap;
        }

        .color-option {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .color-option:hover,
        .color-option.active {
            border-color: white;
            transform: scale(1.1);
        }

        .friends-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .friend-card {
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 1rem;
            transition: transform 0.3s ease;
        }

        .friend-card:hover {
            transform: translateY(-5px);
        }

        .friend-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid var(--accent-color);
            margin-bottom: 0.5rem;
        }

        .ban-warning {
            background: linear-gradient(135deg, #ff4757, #ff3838);
            color: white;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        @media (max-width: 768px) {
            .username-title {
                font-size: 2rem;
            }
            
            .avatar-image {
                width: 120px;
                height: 120px;
            }
        }

        /* User's Custom CSS */
        <?php if($userCustomization && !empty($userCustomization->custom_css)): ?>
        <?php echo htmlspecialchars_decode($userCustomization->custom_css); ?>
        <?php endif; ?>
    </style>
</head>
<body>
    <?php if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/private/components/navbar.inc.php')): ?>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/private/components/navbar.inc.php'); ?>
    <?php endif; ?>
    
    <?php if($userCustomization && !empty($userCustomization->background_music)): ?>
    <audio id="profile-audio" loop volume="0.3">
        <source src="<?php echo htmlspecialchars($userCustomization->background_music); ?>" type="audio/mpeg">
    </audio>
    <script>
        // Auto-play with user interaction
        document.addEventListener('click', function() {
            const audio = document.getElementById('profile-audio');
            if(audio && audio.paused) {
                audio.play().catch(e => console.log('Audio play failed:', e));
            }
        }, { once: true });
    </script>
    <?php endif; ?>
    
    <?php if($isOwner): ?>
    <div style="position: fixed; top: 80px; right: 20px; z-index: 1000;">
        <button class="button is-primary" onclick="toggleCustomization()">
            <i class="fas fa-palette"></i> Customize Profile
        </button>
    </div>
    
    <div id="overlay" class="overlay" onclick="toggleCustomization()"></div>
    
    <div id="customization-panel" class="customization-panel">
        <form method="post">
            <h3 class="title is-3">
                <i class="fas fa-palette"></i> Customize Your Profile
            </h3>
            
            <div class="columns">
                <div class="column">
                    <h4 class="subtitle is-5">Theme Presets</h4>
                    <div class="color-picker">
                        <div class="color-option" style="background: linear-gradient(135deg, #1a1a2e, #16213e)" 
                             onclick="selectTheme('default', '#1a1a2e', '#6c5ce7')" title="Default"></div>
                        <div class="color-option" style="background: linear-gradient(135deg, #667eea, #764ba2)" 
                             onclick="selectTheme('purple', '#667eea', '#764ba2')" title="Purple"></div>
                        <div class="color-option" style="background: linear-gradient(135deg, #f093fb, #f5576c)" 
                             onclick="selectTheme('pink', '#f093fb', '#f5576c')" title="Pink"></div>
                        <div class="color-option" style="background: linear-gradient(135deg, #4facfe, #00f2fe)" 
                             onclick="selectTheme('blue', '#4facfe', '#00f2fe')" title="Blue"></div>
                        <div class="color-option" style="background: linear-gradient(135deg, #43e97b, #38f9d7)" 
                             onclick="selectTheme('green', '#43e97b', '#38f9d7')" title="Green"></div>
                    </div>
                </div>
                
                <div class="column">
                    <div class="field">
                        <label class="label">Font</label>
                        <div class="select is-fullwidth">
                            <select name="profile_font" id="profile_font">
                                <option value="Inter">Inter</option>
                                <option value="Roboto">Roboto</option>
                                <option value="Poppins">Poppins</option>
                                <option value="Orbitron">Orbitron</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">Background Color</label>
                        <input class="input" type="color" name="background_color" id="background_color" 
                               value="<?php echo htmlspecialchars($userCustomization->background_color); ?>">
                    </div>
                </div>
                
                <div class="column">
                    <div class="field">
                        <label class="label">Accent Color</label>
                        <input class="input" type="color" name="accent_color" id="accent_color" 
                               value="<?php echo htmlspecialchars($userCustomization->accent_color); ?>">
                    </div>
                </div>
                
                <div class="column">
                    <div class="field">
                        <label class="label">Text Color</label>
                        <input class="input" type="color" name="text_color" id="text_color" 
                               value="<?php echo htmlspecialchars($userCustomization->text_color); ?>">
                    </div>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Background Image URL</label>
                <input class="input" type="url" name="background_image" placeholder="https://example.com/image.jpg" 
                       value="<?php echo htmlspecialchars($userCustomization->background_image); ?>">
            </div>
            
            <div class="field">
                <label class="label">Background Music URL</label>
                <input class="input" type="url" name="background_music" placeholder="https://example.com/music.mp3" 
                       value="<?php echo htmlspecialchars($userCustomization->background_music); ?>">
            </div>
            
            <div class="field">
                <label class="label">Custom CSS</label>
                <textarea class="textarea" name="custom_css" rows="6" 
                          placeholder="Add custom CSS..."><?php echo htmlspecialchars($userCustomization->custom_css); ?></textarea>
            </div>
            
            <input type="hidden" name="theme" id="theme_input" value="<?php echo htmlspecialchars($userCustomization->theme); ?>">
            
            <div class="buttons">
                <button type="submit" name="save_customization" class="button is-success is-large">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button type="button" class="button is-danger is-large" onclick="toggleCustomization()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <?php if($selectBan->rowCount() >= 1): ?>
        <div class="ban-warning has-text-centered">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>This user has been suspended</strong>
        </div>
        <?php endif; ?>
        
        <section class="section">
            <div class="columns">
                <div class="column is-3">
                    <div class="profile-container">
                        <div class="profile-header has-text-centered">
                            <div class="avatar-container">
                                <img src="/assets/images/avatars/<?php echo htmlspecialchars($gP->avatar_hash); ?>.png" 
                                     class="avatar-image"
                                     onerror="this.src='/assets/images/avatars/default.png'"
                                     alt="<?php echo htmlspecialchars($gP->username); ?>">
                                <div class="online-indicator <?php echo ($gP->expire + 300 > time()) ? 'online' : 'offline'; ?>"></div>
                            </div>
                            
                            <h1 class="username-title">
                                <?php echo htmlspecialchars($gP->username); ?>
                            </h1>
                            
                            <?php if(!empty($gP->blurb)): ?>
                            <p class="user-blurb"><?php echo nl2br(htmlspecialchars($gP->blurb)); ?></p>
                            <?php endif; ?>
                        </div>

                        <div style="padding: 1rem;">
                            <?php if($AUTH && $id != $_SESSION['id'] && $selectBan->rowCount() < 1): ?>
                            <form method="post">
                                <?php if(!isset($friends)): ?>
                                <button type="submit" name="send" class="button is-success is-fullwidth action-button">
                                    <i class="fas fa-user-plus"></i> Add Friend
                                </button>
                                <?php elseif($friends == 0): ?>
                                <button class="button is-warning is-fullwidth action-button" disabled>
                                    <i class="fas fa-clock"></i> Request Pending
                                </button>
                                <?php elseif($friends == 1): ?>
                                <button type="submit" name="remove" class="button is-danger is-fullwidth action-button">
                                    <i class="fas fa-user-times"></i> Remove Friend
                                </button>
                                <?php endif; ?>
                            </form>
                            
                            <a href="/user/inbox/send/<?php echo $id; ?>" class="button is-primary is-fullwidth action-button">
                                <i class="fas fa-comments"></i> Send Message
                            </a>
                            
                            <?php if($AUTH && isset($my) && $my->administrator >= 1): ?>
                            <a href="/admin/modify/<?php echo $id; ?>" class="button is-dark is-fullwidth action-button">
                                <i class="fas fa-hammer"></i> Admin Tools
                            </a>
                            <form method="post" style="margin-top: 0.5rem;">
                                <button type="submit" name="reRenderAvatar" class="button is-warning is-fullwidth action-button">
                                    <i class="fas fa-redo"></i> Re-render Avatar
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="stats-card">
                            <h3 class="title is-5 has-text-white">
                                <i class="fas fa-chart-bar"></i> Statistics
                            </h3>
                            <div class="stat-item">
                                <span><i class="fas fa-calendar"></i> Joined</span>
                                <span><?php echo date('M d, Y', $gP->joined); ?></span>
                            </div>
                            <div class="stat-item">
                                <span><i class="fas fa-clock"></i> Last Seen</span>
                                <span><?php echo date('M d, Y', $gP->expire); ?></span>
                            </div>
                            <div class="stat-item">
                                <span><i class="fas fa-comments"></i> Forum Posts</span>
                                <span><?php echo number_format($numPosts); ?></span>
                            </div>
                            <?php if($profilenum > 0): ?>
                            <div class="stat-item">
                                <span><i class="fas fa-eye"></i> Profile Views</span>
                                <span><?php echo number_format($profilenum); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="column is-9">
                    <div class="profile-container">
                        <div style="padding: 2rem;">
                            <?php if(!empty($gP->status)): ?>
                            <div class="profile-card">
                                <div class="card-content">
                                    <h3 class="title is-5">
                                        <i class="fas fa-quote-left"></i> Status
                                    </h3>
                                    <p class="is-size-6">
                                        <strong><?php echo htmlspecialchars($gP->username); ?> says:</strong><br>
                                        "<?php echo nl2br(htmlspecialchars($gP->status)); ?>"
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="profile-card">
                                <div class="card-content">
                                    <h3 class="title is-5">
                                        <i class="fas fa-users"></i> Friends
                                        <span class="is-size-7 has-text-grey">(<?php echo $selectFriends->rowCount(); ?>)</span>
                                    </h3>
                                    
                                    <?php if($selectFriends->rowCount() == 0): ?>
                                    <p class="has-text-grey-light">This user has not yet added any friends</p>
                                    <?php else: ?>
                                    <div class="friends-grid">
                                        <?php while($friend = $selectFriends->fetch(PDO::FETCH_OBJ)): ?>
                                        <div class="friend-card">
                                            <a href="/user/<?php echo $friend->userid; ?>">
                                                <img src="/assets/images/avatars/<?php echo htmlspecialchars($friend->avatar_hash); ?>.png" 
                                                     class="friend-avatar"
                                                     onerror="this.src='/assets/images/avatars/default.png'"
                                                     alt="<?php echo htmlspecialchars($friend->username); ?>">
                                            </a>
                                            <div class="is-size-7">
                                                <?php if($friend->expire + 300 > time()): ?>
                                                <span class="has-text-success">●</span>
                                                <?php else: ?>
                                                <span class="has-text-grey-light">●</span>
                                                <?php endif; ?>
                                                <a href="/user/<?php echo $friend->userid; ?>" class="has-text-dark">
                                                    <?php echo htmlspecialchars($friend->username); ?>
                                                </a>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <script>
        function toggleCustomization() {
            const panel = document.getElementById('customization-panel');
            const overlay = document.getElementById('overlay');
            
            if(panel && overlay) {
                panel.classList.toggle('active');
                overlay.classList.toggle('active');
                
                document.body.style.overflow = panel.classList.contains('active') ? 'hidden' : 'auto';
            }
        }

        function selectTheme(theme, bgColor, accentColor) {
            document.getElementById('theme_input').value = theme;
            document.getElementById('background_color').value = bgColor;
            document.getElementById('accent_color').value = accentColor;
            
            document.documentElement.style.setProperty('--primary-gradient', bgColor);
            document.documentElement.style.setProperty('--accent-color', accentColor);
            
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const panel = document.getElementById('customization-panel');
                if (panel && panel.classList.contains('active')) {
                    toggleCustomization();
                }
            }
        });

        const bgColorInput = document.getElementById('background_color');
        const accentColorInput = document.getElementById('accent_color');
        const textColorInput = document.getElementById('text_color');
        const fontSelect = document.getElementById('profile_font');

        if(bgColorInput) {
            bgColorInput.addEventListener('input', function() {
                document.documentElement.style.setProperty('--primary-gradient', this.value);
            });
        }

        if(accentColorInput) {
            accentColorInput.addEventListener('input', function() {
                document.documentElement.style.setProperty('--accent-color', this.value);
            });
        }

        if(textColorInput) {
            textColorInput.addEventListener('input', function() {
                document.documentElement.style.setProperty('--text-color', this.value);
            });
        }

        if(fontSelect) {
            fontSelect.addEventListener('change', function() {
                document.documentElement.style.setProperty('--profile-font', this.value);
            });
        }
    </script>

<?php 
if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php');
}
?>

</body>
</html>