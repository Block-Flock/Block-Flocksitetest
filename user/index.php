<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Modern Design</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.4/css/bulma.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            --profile-border: linear-gradient(45deg, #667eea, #764ba2, #f093fb);
        }

        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            background: var(--primary-gradient);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="1000,100 1000,0 0,100"/></svg>');
            background-size: cover;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            padding: 1rem;
        }

        .achievement-badge {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            position: relative;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .achievement-badge:hover {
            transform: scale(1.1);
        }

        .achievement-badge.admin {
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
        }

        .achievement-badge.premium {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
        }

        .achievement-badge.prestige {
            background: linear-gradient(135deg, #9c27b0, #673ab7);
        }

        .status-box {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
            position: relative;
            overflow: hidden;
        }

        .status-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .status-box:hover::before {
            left: 100%;
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
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .menu-label {
            color: #4a4a4a;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }

        .menu-list a {
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .menu-list a:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(5px);
        }

        .menu-list a.is-active {
            background: var(--primary-gradient);
            color: white;
        }

        .friends-preview {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .friend-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #667eea;
            transition: transform 0.3s ease;
        }

        .friend-avatar:hover {
            transform: scale(1.1);
        }

        .customization-panel {
            background: rgba(0, 0, 0, 0.9);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            display: none;
        }

        .customization-panel.active {
            display: block;
        }

        .color-picker {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
        }

        .color-option {
            width: 40px;
            height: 40px;
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

        @media (max-width: 768px) {
            .profile-container {
                margin: 1rem;
                border-radius: 15px;
            }
            
            .username-title {
                font-size: 2rem;
            }
            
            .avatar-image {
                width: 120px;
                height: 120px;
            }
        }

        /* Profile customization styles for owner */
        .profile-owner-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .customize-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 24px;
            cursor: pointer;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }

        .customize-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php
    // Mock data for demonstration - replace with your actual PHP variables
    $gP = (object)[
        'id' => 123,
        'username' => 'CoolUser2024',
        'blurb' => 'Welcome to my awesome profile! 🎮',
        'status' => 'Just finished an epic gaming session! Ready for more adventures.',
        'joined' => strtotime('2024-01-15'),
        'expire' => time() - 7200, // 2 hours ago
        'avatar_hash' => 'default',
        'administrator' => 1,
        'premium' => 1,
        'prestige' => 1
    ];
    
    $numPosts = 1337;
    $selectBan = (object)['rowCount' => 0]; // Mock ban check
    $AUTH = true;
    $isOwner = true; // Set to true for demo
    $friends = null; // null = can add friend, 0 = pending, 1 = friends
    ?>

    <!-- Profile Owner Controls (only show if user owns profile) -->
    <?php if($isOwner): ?>
    <div class="profile-owner-controls">
        <button class="customize-btn" onclick="toggleCustomization()">
            <i class="fas fa-palette"></i> Customize Profile
        </button>
    </div>
    <?php endif; ?>

    <div class="container">
        <section class="section">
            <!-- Ban Warning -->
            <?php if($selectBan->rowCount() >= 1): ?>
            <div class="ban-warning has-text-centered">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>This user has been suspended</strong>
            </div>
            <?php endif; ?>

            <!-- Profile Customization Panel (for owner only) -->
            <?php if($isOwner): ?>
            <div id="customization-panel" class="customization-panel">
                <h3 class="title is-4">
                    <i class="fas fa-palette"></i> Customize Your Profile
                </h3>
                
                <div class="columns">
                    <div class="column">
                        <h4 class="subtitle is-6">Background Color</h4>
                        <div class="color-picker">
                            <div class="color-option" style="background: linear-gradient(135deg, #1e3c72, #2a5298)" onclick="changeTheme('default')"></div>
                            <div class="color-option" style="background: linear-gradient(135deg, #667eea, #764ba2)" onclick="changeTheme('purple')"></div>
                            <div class="color-option" style="background: linear-gradient(135deg, #f093fb, #f5576c)" onclick="changeTheme('pink')"></div>
                            <div class="color-option" style="background: linear-gradient(135deg, #4facfe, #00f2fe)" onclick="changeTheme('blue')"></div>
                            <div class="color-option" style="background: linear-gradient(135deg, #43e97b, #38f9d7)" onclick="changeTheme('green')"></div>
                        </div>
                    </div>
                    <div class="column">
                        <h4 class="subtitle is-6">Profile Music</h4>
                        <input class="input" type="url" placeholder="Enter music URL" id="profile-music">
                        <button class="button is-small is-primary" onclick="setProfileMusic()">Set Music</button>
                    </div>
                </div>
                
                <div class="field">
                    <label class="label">Custom CSS</label>
                    <textarea class="textarea" placeholder="Add your custom CSS here..." id="custom-css" rows="5"></textarea>
                    <button class="button is-success" onclick="applyCustomCSS()">Apply CSS</button>
                </div>
                
                <button class="button is-danger" onclick="toggleCustomization()">Close</button>
            </div>
            <?php endif; ?>

            <div class="columns is-centered">
                <!-- Sidebar -->
                <div class="column is-3">
                    <div class="sidebar">
                        <aside class="menu">
                            <p class="menu-label">Profile</p> 
                            <ul class="menu-list">
                                <li><a class="is-active" href="/user?i=<?php echo htmlspecialchars($gP->id);?>">
                                    <i class="fas fa-user"></i> Profile
                                </a></li>
                            </ul>
                            <p class="menu-label">More</p> 
                            <ul class="menu-list">
                                <li><a href="/user/profile/friends/?id=<?php echo htmlspecialchars($gP->id);?>">
                                    <i class="fas fa-users"></i> Friends
                                </a></li>
                                <li><a href="/user/profile/posts/?id=<?php echo htmlspecialchars($gP->id);?>">
                                    <i class="fas fa-comments"></i> Posts
                                </a></li>
                            </ul>
                        </aside>
                    </div>
                </div>

                <!-- Main Profile Content -->
                <div class="column is-9">
                    <div class="profile-container">
                        <!-- Profile Header -->
                        <div class="profile-header has-text-centered">
                            <div class="avatar-container">
                                <img src="/assets/images/avatars/<?php echo $gP->avatar_hash; ?>.png" 
                                     class="avatar-image"
                                     onerror="this.src='/assets/images/avatars/default.png'"
                                     alt="<?php echo htmlspecialchars($gP->username); ?>">
                                <div class="online-indicator <?php echo ($gP->expire + 1800 > time()) ? 'online' : 'offline'; ?>"></div>
                            </div>
                            
                            <h1 class="username-title">
                                <?php echo htmlspecialchars($gP->username); ?>
                                <?php if($AUTH && $_GET['id'] != $_SESSION['id']): ?>
                                <a href="/user/report/?id=<?php echo $_GET['id']; ?>" class="has-text-white-ter">
                                    <i class="fas fa-flag"></i>
                                </a>
                                <?php endif; ?>
                            </h1>
                            
                            <?php if(!empty($gP->blurb)): ?>
                            <p class="user-blurb"><?php echo htmlentities($gP->blurb); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="columns">
                            <!-- Left Column - Actions & Stats -->
                            <div class="column is-4">
                                <!-- Action Buttons -->
                                <?php if($AUTH && $_GET['id'] != $_SESSION['id'] && $selectBan->rowCount() < 1): ?>
                                <div class="profile-card">
                                    <div class="card-content">
                                        <form method="post">
                                            <?php if(!isset($friends)): ?>
                                            <button type="submit" name="send" class="button is-success is-fullwidth action-button">
                                                <i class="fas fa-user-plus"></i> Add Friend
                                            </button>
                                            <?php elseif($friends == 0): ?>
                                            <button class="button is-warning is-fullwidth action-button" disabled>
                                                <i class="fas fa-clock"></i> Friend Request Pending
                                            </button>
                                            <?php elseif($friends == 1): ?>
                                            <button type="submit" name="remove" class="button is-danger is-fullwidth action-button">
                                                <i class="fas fa-user-times"></i> Remove Friend
                                            </button>
                                            <?php endif; ?>
                                        </form>
                                        
                                        <a href="/user/inbox/send/?id=<?php echo $_GET['id']; ?>" 
                                           class="button is-primary is-fullwidth action-button">
                                            <i class="fas fa-comments"></i> Send Message
                                        </a>
                                        
                                        <?php if($AUTH && isset($my) && $my->administrator >= 1): ?>
                                        <a href="/admin/modify/?id=<?php echo $_GET['id']; ?>" 
                                           class="button is-dark is-fullwidth action-button">
                                            <i class="fas fa-hammer"></i> Admin Tools
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Statistics -->
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
                                        <span><?php echo timeAgo(date('Y/m/d H:i:s', $gP->expire)); ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <span><i class="fas fa-comments"></i> Forum Posts</span>
                                        <span><?php echo number_format($numPosts); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Status & Achievements -->
                            <div class="column is-8">
                                <!-- Status -->
                                <?php if(!empty($gP->status)): ?>
                                <div class="status-box">
                                    <h3 class="title is-5 has-text-white">
                                        <i class="fas fa-quote-left"></i> Status
                                    </h3>
                                    <p class="is-size-6">
                                        <strong><?php echo htmlspecialchars($gP->username); ?> says:</strong><br>
                                        "<?php echo htmlspecialchars($gP->status); ?>"
                                    </p>
                                </div>
                                <?php endif; ?>

                                <!-- Achievements -->
                                <div class="profile-card">
                                    <div class="card-content">
                                        <h3 class="title is-5">
                                            <i class="fas fa-trophy"></i> Achievements
                                        </h3>
                                        <div class="badge-container">
                                            <?php if($gP->administrator): ?>
                                            <div class="achievement-badge admin" title="Administrator">
                                                <i class="fas fa-crown"></i>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if($gP->premium): ?>
                                            <div class="achievement-badge premium" title="Premium User">
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if($gP->prestige): ?>
                                            <div class="achievement-badge prestige" title="Prestige Member">
                                                <i class="fas fa-gem"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Friends Preview -->
                                <div class="profile-card">
                                    <div class="card-content">
                                        <h3 class="title is-5">
                                            <i class="fas fa-users"></i> Friends
                                            <a href="/user/profile/friends/?id=<?php echo $gP->id; ?>" class="is-pulled-right">
                                                <small>View All</small>
                                            </a>
                                        </h3>
                                        <div class="friends-preview">
                                            <!-- You would populate this with actual friends data -->
                                            <img src="/assets/images/avatars/default.png" class="friend-avatar" alt="Friend">
                                            <img src="/assets/images/avatars/default.png" class="friend-avatar" alt="Friend">
                                            <img src="/assets/images/avatars/default.png" class="friend-avatar" alt="Friend">
                                            <img src="/assets/images/avatars/default.png" class="friend-avatar" alt="Friend">
                                        </div>
                                    </div>
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
            panel.classList.toggle('active');
        }

        function changeTheme(theme) {
            const themes = {
                'default': 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
                'purple': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'pink': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'blue': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'green': 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'
            };
            
            document.body.style.background = themes[theme];
            
            // Save to localStorage or send to server
            localStorage.setItem('profileTheme', theme);
        }

        function setProfileMusic() {
            const musicUrl = document.getElementById('profile-music').value;
            if (musicUrl) {
                // Remove existing audio
                const existingAudio = document.getElementById('profile-audio');
                if (existingAudio) {
                    existingAudio.remove();
                }
                
                // Create new audio element
                const audio = document.createElement('audio');
                audio.id = 'profile-audio';
                audio.src = musicUrl;
                audio.loop = true;
                audio.volume = 0.3;
                audio.autoplay = true;
                document.body.appendChild(audio);
                
                // Save to server/localStorage
                localStorage.setItem('profileMusic', musicUrl);
            }
        }

        function applyCustomCSS() {
            const customCSS = document.getElementById('custom-css').value;
            
            // Remove existing custom styles
            const existingStyle = document.getElementById('custom-profile-styles');
            if (existingStyle) {
                existingStyle.remove();
            }
            
            // Apply new styles
            if (customCSS.trim()) {
                const style = document.createElement('style');
                style.id = 'custom-profile-styles';
                style.textContent = customCSS;
                document.head.appendChild(style);
                
                // Save to server/localStorage
                localStorage.setItem('profileCustomCSS', customCSS);
            }
        }

        // Load saved customizations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('profileTheme');
            if (savedTheme) {
                changeTheme(savedTheme);
            }
            
            const savedMusic = localStorage.getItem('profileMusic');
            if (savedMusic) {
                document.getElementById('profile-music').value = savedMusic;
                setProfileMusic();
            }
            
            const savedCSS = localStorage.getItem('profileCustomCSS');
            if (savedCSS) {
                document.getElementById('custom-css').value = savedCSS;
                applyCustomCSS();
            }
        });

        // Add some interactive effects
        document.querySelectorAll('.achievement-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                this.style.animation = 'pulse 0.5s';
                setTimeout(() => {
                    this.style.animation = '';
                }, 500);
            });
        });
    </script>
</body>
</html>