<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Modern Design</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.4/css/bulma.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/user.css" rel="stylesheet">
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