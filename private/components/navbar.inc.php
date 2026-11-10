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

    // 5. Initial Animation (Disabled problematic animations)
    /*
    setTimeout(() => {
        const items = document.querySelectorAll('.navbar-item');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.animation = `fadeInUp 0.5s ease forwards ${index * 0.05}s`;
        });
    }, 100);
    */
});
</script>
