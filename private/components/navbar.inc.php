<?php
// Fetch notification counts only if logged in
$numReq = 0;
$numReqMessage = 0;

if (isset($AUTH) && $AUTH && isset($_SESSION['id'])) {
    $friendStmt = $db->prepare("SELECT COUNT(*) FROM user_friends WHERE receiver = ? AND status = 0");
    $friendStmt->execute([$_SESSION['id']]);
    $numReq = $friendStmt->fetchColumn();
    
    $msgStmt = $db->prepare("SELECT COUNT(*) FROM user_inboxes WHERE recipient = ? AND hasread = 0");
    $msgStmt->execute([$_SESSION['id']]);
    $numReqMessage = $msgStmt->fetchColumn();
}

// Format counts (9+)
$displayReq = ($numReq > 9) ? "9+" : $numReq;
$displayMsg = ($numReqMessage > 9) ? "9+" : $numReqMessage;
?>

<style>
    :root {
        --nav-primary: #ff4500;
        --nav-dark: #1a1a1a;
        --nav-glass: rgba(255, 69, 0, 0.95);
        --coral-dark: #d84315;
        --coral-light: #ff6b35;
        --text-light: #ffffff;
        --shadow-color: rgba(255, 69, 0, 0.3);
    }

    /* Animated Diagonal Checker Background */
    @keyframes diagonalScroll {
        0% { background-position: 0 0; }
        100% { background-position: 70px 70px; }
    }

    /* Modern Glass Navbar with Checker Pattern */
    .modern-navbar {
        background: var(--nav-glass);
        backdrop-filter: blur(12px) saturate(180%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        margin-bottom: 0 !important;
        box-shadow: 
            0 8px 32px rgba(0, 0, 0, 0.25),
            0 4px 8px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0.75rem 0;
        min-height: 80px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        border-radius: 0 !important;
        margin: 0;
        width: 100%;
        
        /* Seamless checkerboard pattern */
        background-image: 
            linear-gradient(15deg, var(--coral-dark) 25%, transparent 25%),
            linear-gradient(-15deg, var(--coral-dark) 25%, transparent 25%),
            linear-gradient(15deg, transparent 75%, var(--coral-dark) 75%),
            linear-gradient(-15deg, transparent 75%, var(--coral-dark) 75%);
        background-size: 70px 70px;
        background-position: 0 0, 0 35px, 35px -35px, -35px 0px;
        background-color: var(--coral-light);
        animation: diagonalScroll 4s linear infinite;
    }

    .modern-navbar::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 20%, rgba(0, 0, 0, 0.1) 100%);
        pointer-events: none;
        z-index: -1;
    }

    .modern-navbar.scrolled {
        background: rgba(255, 69, 0, 0.98);
        padding: 0.5rem 0;
        min-height: 60px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    /* Center navbar content */
    .navbar .container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        max-width: 100%;
        padding: 0 20px;
    }

    .navbar-brand { margin-right: auto; }
    .navbar-menu { flex-grow: 1; display: flex; justify-content: center; align-items: center; }
    .navbar-start { margin-right: auto; display: flex; justify-content: center; flex-grow: 1; }
    .navbar-end { margin-left: auto; position: relative; display: flex; align-items: center; }

    /* Brand / Logo */
    .logo-banner img {
        height: 50px;
        width: auto;
        transition: all 0.3s;
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
    }
    .logo-banner:hover img {
        transform: scale(1.05) rotate(-2deg);
    }

    /* Navigation Items */
    .navbar-item {
        color: rgba(255, 255, 255, 0.95) !important;
        font-weight: 600;
        font-size: 0.95rem;
        border-radius: 50px !important;
        margin: 0 5px;
        transition: all 0.3s;
        padding: 0.5rem 1.2rem !important;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(5px);
        white-space: nowrap;
    }
    .navbar-item:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        transform: translateY(-3px) scale(1.05);
    }

    /* --- FIXED DROPDOWN STYLES --- */
    .navbar-item.has-dropdown {
        position: relative;
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
        overflow: visible !important;
        z-index: 2000;
    }

    .dropdown-trigger {
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.5rem 1.2rem !important;
        border-radius: 50px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white !important;
        transition: all 0.3s ease;
    }
    
    .dropdown-trigger:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .navbar-dropdown {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        padding: 8px 0;
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        min-width: 200px;
        display: none;
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.2s ease, transform 0.2s ease;
        z-index: 2001;
    }

    .navbar-dropdown.is-active {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    .navbar-dropdown .navbar-item {
        color: #333 !important;
        background: transparent !important;
        border: none !important;
        border-radius: 0 !important;
        margin: 0;
        padding: 10px 20px !important;
        width: 100%;
        display: flex;
        align-items: center;
        text-align: left;
        box-shadow: none !important;
        transform: none !important;
        font-weight: 500;
    }

    .navbar-dropdown .navbar-item:hover {
        background: #f5f5f5 !important;
        color: var(--nav-primary) !important;
        padding-left: 25px !important;
    }

    .navbar-dropdown .navbar-item i {
        width: 24px;
        text-align: center;
        margin-right: 8px;
    }

    .navbar-divider {
        height: 1px;
        background-color: #ededed;
        margin: 4px 0;
        border: none;
    }

    /* Badges */
    .notification-badge { position: relative; display: inline-block; }
    .badge-counter {
        position: absolute; top: -5px; right: -5px;
        background: #cc0000; color: white;
        border-radius: 50%; height: 20px; width: 20px;
        font-size: 11px; display: flex; align-items: center; justify-content: center;
        border: 2px solid rgba(255,255,255,0.3);
        font-weight: bold; z-index: 2;
        animation: pulse 2s infinite;
    }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }

    /* User Avatar in Nav */
    .user-avatar-nav {
        width: 32px; height: 32px; border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.6);
    }

    /* Currency Stats */
    .currency-pill {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 50px; padding: 8px 18px !important; margin: 0 5px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }

    /* Join Button */
    .btn-join {
        background: #ffffff !important; color: var(--nav-primary) !important;
        border-radius: 50px !important; font-weight: 800 !important;
        padding: 0 30px !important; height: 45px;
    }

    /* Mobile Burger */
    .navbar-burger {
        color: white !important; background: rgba(0,0,0,0.2);
        border-radius: 12px; height: 45px; width: 45px;
        margin-right: 10px; border: 1px solid rgba(255,255,255,0.1);
    }

    /* Mobile Responsive Tweaks */
    @media screen and (max-width: 1023px) {
        .navbar-menu {
            position: absolute; top: 100%; left: 0; right: 0;
            background: var(--coral-dark); padding: 1rem;
            display: none;
        }
        .navbar-menu.is-active { display: block; }
        .navbar-start, .navbar-end { flex-direction: column; width: 100%; }
        .navbar-item, .dropdown-trigger { width: 100%; justify-content: flex-start; margin: 5px 0; }
        
        .navbar-dropdown {
            position: static;
            opacity: 1;
            transform: none;
            display: none;
            width: 100%;
            background: rgba(0,0,0,0.1);
            box-shadow: none;
            border: none;
            margin-top: 5px;
        }
        .navbar-dropdown.is-active { display: block; }
        .navbar-dropdown .navbar-item { color: white !important; }
        .navbar-dropdown .navbar-item:hover { background: rgba(255,255,255,0.1) !important; color: white !important; }
    }
</style>

<nav class="navbar is-fixed-top modern-navbar" role="navigation" aria-label="main navigation">
    <div class="container">
        <div class="navbar-brand">
            <a class="navbar-item logo-banner" href="/dashboard">
                <img src="/assets/images/logo/bricktaria-banner.png" alt="Bricktaria">
            </a>

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="mainNavbar">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="mainNavbar" class="navbar-menu">
            <div class="navbar-start">
                <?php if (!isset($title) || $title !== "Admin Control Panel") : ?>
                    <a class="navbar-item" href="/games"><i class="fas fa-gamepad"></i>&nbsp; Games</a>
                    <a class="navbar-item" href="/store"><i class="fas fa-store"></i>&nbsp; Store</a>
                    <a class="navbar-item" href="/forum"><i class="fas fa-comments"></i>&nbsp; Forum</a>
                    <a class="navbar-item" href="/users"><i class="fas fa-users"></i>&nbsp; Users</a>
                    <a class="navbar-item" href="/groups"><i class="fas fa-flag"></i>&nbsp; Groups</a>
                <?php endif; ?>
            </div>

            <div class="navbar-end">
                <?php if (!isset($AUTH) || !$AUTH) : ?>
                    <div class="navbar-item">
                        <div class="buttons">
                            <a class="button is-text has-text-white" href="/log-in">
                                <i class="fas fa-sign-in-alt mr-2"></i> Log in
                            </a>
                            <a class="button btn-join" href="/join">
                                <strong>Join Now</strong>
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <?php if (!isset($title) || $title !== "Admin Control Panel") : ?>
                        <a class="navbar-item currency-pill" title="Cash">
                            <i class="fas fa-money-bill-wave has-text-success"></i>&nbsp;
                            <?= number_format($my->cash); ?>
                        </a>
                        <a class="navbar-item currency-pill" title="Coins">
                            <i class="fas fa-coins has-text-warning"></i>&nbsp;
                            <?= number_format($my->Currency); ?>
                        </a>
                    <?php endif; ?>

                    <a class="navbar-item notification-badge" href="/user/friends/requests">
                        <i class="fas fa-user-friends"></i>
                        <?php if ($numReq > 0) : ?><span class="badge-counter"><?= $displayReq ?></span><?php endif; ?>
                    </a>

                    <a class="navbar-item notification-badge" href="/user/inbox/received/?page=1">
                        <i class="fas fa-envelope"></i>
                        <?php if ($numReqMessage > 0) : ?><span class="badge-counter"><?= $displayMsg ?></span><?php endif; ?>
                    </a>

                    <div class="navbar-item has-dropdown">
                        <div class="dropdown-trigger" id="userDropdownTrigger">
                            <img src="/assets/images/avatars/headshots/<?= $my->avatar_hash ?? 'default' ?>.png" class="user-avatar-nav" onerror="this.src='/assets/images/avatars/default.png'">
                            <span><?= htmlspecialchars($my->username); ?></span>
                            <i class="fas fa-chevron-down ml-2" id="dropdownChevron" style="transition: transform 0.3s; font-size: 0.8em;"></i>
                        </div>

                        <div class="navbar-dropdown is-right" id="userDropdown">
                            <a class="navbar-item" href="/user/<?= $my->id; ?>">
                                <i class="fas fa-id-card"></i> Profile
                            </a>
                            <a class="navbar-item" href="/avatar">
                                <i class="fas fa-tshirt"></i> Character
                            </a>
                            <a class="navbar-item" href="/user/settings">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            
                            <?php if ($my->administrator >= 1) : ?>
                                <hr class="navbar-divider">
                                <a class="navbar-item has-text-danger" href="/admin">
                                    <i class="fas fa-shield-alt"></i> Admin
                                </a>
                            <?php endif; ?>
                            
                            <hr class="navbar-divider">
                            <a class="navbar-item" href="/upgrade">
                                <i class="fas fa-star has-text-warning"></i> Upgrade
                            </a>
                            <a class="navbar-item has-text-grey" id="logoutBtn" href="javascript:void(0)">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div style="height: 90px;"></div>

<div class="modal" id="globalModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title" id="globalModalTitle">Info</p>
            <button class="delete mclose" aria-label="close"></button>
        </header>
        <section class="modal-card-body" id="globalModalContent"></section>
        <footer class="modal-card-foot justify-content-end">
            <button class="button mclose">Close</button>
        </footer>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Burger Menu
    const burgers = document.querySelectorAll('.navbar-burger');
    burgers.forEach(el => {
        el.addEventListener('click', () => {
            const target = document.getElementById(el.dataset.target);
            el.classList.toggle('is-active');
            target.classList.toggle('is-active');
        });
    });

    // 2. Navbar Scroll Effect
    const navbar = document.querySelector('.modern-navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) navbar.classList.add('scrolled');
        else navbar.classList.remove('scrolled');
    });

    // 3. Global Modal
    document.querySelectorAll('.mclose, .modal-background').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('globalModal').classList.remove('is-active');
        });
    });

    // 4. FIXED DROPDOWN LOGIC
    const trigger = document.getElementById('userDropdownTrigger');
    const menu = document.getElementById('userDropdown');
    const chevron = document.getElementById('dropdownChevron');

    if (trigger && menu) {
        // Toggle on click
        trigger.addEventListener('click', (e) => {
            e.stopPropagation(); // Stop click from reaching document
            menu.classList.toggle('is-active');
            
            // Rotate chevron
            if(chevron) {
                chevron.style.transform = menu.classList.contains('is-active') 
                    ? 'rotate(180deg)' 
                    : 'rotate(0deg)';
            }
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                menu.classList.remove('is-active');
                if(chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if(e.key === 'Escape') {
                menu.classList.remove('is-active');
                if(chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });
    }

    // 5. Initial Animation
    setTimeout(() => {
        const items = document.querySelectorAll('.navbar-item');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.animation = `fadeInUp 0.5s ease forwards ${index * 0.05}s`;
        });
    }, 100);
});
</script>
