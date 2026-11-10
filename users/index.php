<?php
$title = 'Users';

// Add Three.js and avatar rendering scripts
$extra_head_content = '
<!-- Three.js Libraries for Avatar Rendering -->
<script src="/assets/vendor/three.min.js"></script>
<script src="/assets/vendor/GLTFLoader.js"></script>
<script src="/assets/vendor/OBJLoader.js"></script>
';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$limit = 8;

if(!isset($_GET['page'])) {
    $page = 1;
} else {
    $page = $_GET['page'];
}

$offset = ($page-1) * $limit;
if($offset < 0) { $offset = 0; }

$selectUserCount = $db->prepare('SELECT * FROM users');
$selectUserCount->execute();

$pages = ceil($selectUserCount->rowCount() / $limit);

if(!isset($_POST['query'])) {
    $selectUsers = $db->prepare('SELECT * FROM users ORDER BY expire DESC LIMIT '.$limit.' OFFSET '.$offset);
    $selectUsers->execute();
} else if(isset($_POST['query'])) {
    $queryTerm = '%' . $_POST['query'] . '%';
    $selectUsers = $db->prepare('SELECT * FROM users WHERE username LIKE ? ORDER BY expire DESC LIMIT '.$limit.' OFFSET '.$offset);
    $selectUsers->bindValue(1, $queryTerm, PDO::PARAM_STR);
    $selectUsers->execute();
}

$selectUserCount = $db->prepare("SELECT * FROM `users` ORDER BY `id` DESC LIMIT 1");
$selectUserCount->execute();
$nousers = $selectUserCount->fetch(PDO::FETCH_OBJ);

// Fetch all users data
$usersData = $selectUsers->fetchAll(PDO::FETCH_OBJ);

// Batch fetch avatar data for all users
$allUserIds = array_map(function($user) { return (int)$user->id; }, $usersData);
$userColors = [];
$userWearables = [];

if (!empty($allUserIds)) {
    $placeholders = str_repeat('?,', count($allUserIds) - 1) . '?';
    
    $colorStmt = $db->prepare("SELECT * FROM user_colors WHERE user IN ($placeholders)");
    $colorStmt->execute($allUserIds);
    while ($color = $colorStmt->fetch(PDO::FETCH_OBJ)) {
        $userColors[(int)$color->user] = $color;
    }

    $wearableStmt = $db->prepare("SELECT * FROM user_data WHERE user IN ($placeholders)");
    $wearableStmt->execute($allUserIds);
    while ($wearable = $wearableStmt->fetch(PDO::FETCH_OBJ)) {
        $userWearables[(int)$wearable->user] = $wearable;
    }
}
?>

<style>
    .avatar-user-container {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 12px;
        overflow: hidden;
        background: radial-gradient(circle at center, #252d3f 0%, #1a1f2e 100%);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        flex-shrink: 0;
    }
    
    .avatar-user {
        display: block;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, #252d3f 0%, #1a1f2e 100%);
    }
    
    .avatar-status-indicator {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid #fff;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .avatar-status-online {
        background-color: #23d160;
        box-shadow: 0 0 12px rgba(35, 209, 96, 0.6);
        animation: pulse-online 2s infinite;
    }
    
    .avatar-status-offline {
        background-color: #718096;
    }
    
    @keyframes pulse-online {
        0% { box-shadow: 0 0 0 0 rgba(35, 209, 96, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(35, 209, 96, 0); }
        100% { box-shadow: 0 0 0 0 rgba(35, 209, 96, 0); }
    }
    
    .userPageBox {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .userPageBox:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }
    
    .username {
        color: #6c5ce7;
        font-weight: 600;
        transition: color 0.2s ease;
    }
    
    .username:hover {
        color: #4a90e2;
    }
</style>

<section class="section">
    <div class="page container">
        <div class="columns">
            <div class="column is-1"></div>
            <div class="column is-11">
                <nav class="level is-mobile">
                    <div class="level-left">
                        <div class="level-item">
                            <div class="has-text-weight-semibold">
                                All Users (<?php echo isset($nousers->id) ? number_format($nousers->id) : '0'; ?>)
                            </div>
                        </div>
                        <div class="level-right">
                            <form method="post">
                                <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                                <fieldset>
                                    <div class="field has-addons mb-0">
                                        <p class="control">
                                            <input class="input is-small" type="text" name="query" placeholder="Search">
                                        </p> 
                                        <p class="control">
                                            <button class="button is-info is-small" type="submit">Search</button>
                                        </p>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </nav>
                
                <div class="columns is-multiline">
                    <?php foreach($usersData as $st): ?>
                        <?php 
                        $blurb = $st->blurb;
                        if(strlen($blurb) > 71) {
                            $blurb = substr($blurb, 0, 71) . '...';
                        }
                        
                        $avatarData = getAvatarData($st->id, $userColors, $userWearables);
                        $isOnline = ($st->expire + 1800) > time();
                        ?>
                        <div class="column is-6 mobile-no-paddingBottom">
                            <div class="box userPageBox is-marginless">
                                <div class="columns is-mobile">
                                    <div class="column is-one-fifth-tablet is-4-mobile userColumn column-is-vcentered">
                                        <div class="avatar-user-container">
                                            <canvas 
                                                class="avatar-user" 
                                                id="user-avatar-<?php echo (int)$st->id; ?>" 
                                                width="80" 
                                                height="80"
                                                data-user-id="<?php echo (int)$st->id; ?>"
                                                data-colors='<?php echo htmlspecialchars(json_encode($avatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>'
                                                data-items='<?php echo htmlspecialchars(json_encode($avatarData['items']), ENT_QUOTES, 'UTF-8'); ?>'
                                                data-hash="<?php echo htmlspecialchars($st->avatar_hash ?? 'default'); ?>">
                                            </canvas>
                                            <div class="avatar-status-indicator <?php echo $isOnline ? 'avatar-status-online' : 'avatar-status-offline'; ?>"></div>
                                        </div>
                                    </div>
                                    <div class="column is-four-fifths-tablet is-8-mobile userColumn column-is-vcentered">
                                        <div class="columns is-mobile is-marginless-bottom is-paddingless-bottom">
                                            <div class="column is-8 is-paddingless-bottom">
                                                <a class="is-size-5 usercontent username" href="/user/<?php echo (int)$st->id; ?>">
                                                    <?php echo htmlspecialchars($st->username); ?>
                                                </a>
                                                <p class="usercontent has-text-grey-light user_page_blurb">
                                                    <?php echo htmlspecialchars($blurb); ?>
                                                </p>
                                            </div>
                                            <div class="column is-4 is-paddingless-bottom">
                                                <?php if ($isOnline): ?>
                                                    <span class="tag is-success is-pulled-right has-tooltip-top" data-tooltip="<?php echo date('F j, Y, g:i a', $st->expire); ?>">
                                                        Online
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if($pages > 1): ?>
                <nav class="pagination is-centered" role="navigation" aria-label="pagination"> 
                    <a <?php if($page-1 != 0) { echo 'href="/users/?page='.($page-1).'"'; } ?>
                       <?php if($page-1 == 0) { echo 'disabled="disabled"'; } ?>
                       class="pagination-link is-hidden-mobile">
                        <i class="fas fa-chevron-left fa-sm"></i>
                    </a>
                    <ul class="pagination-list">
                        <?php for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++): ?>
                            <?php if ($i <= $pages): ?>
                                <li>
                                    <a href="/users/?page=<?php echo $i; ?>" 
                                       <?php if($i == $page) { echo 'aria-current="true"'; } ?>
                                       class="pagination-link<?php if($i == $page) { echo ' is-current'; } ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </ul>
                    <a <?php if($pages > 1 && $page+1 <= $pages) { echo 'href="/users/?page='.($page+1).'"'; } ?>
                       <?php if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled"'; } ?>
                       class="is-hidden-mobile pagination-next">
                        <i class="fas fa-chevron-right fa-sm"></i>
                    </a>
                </nav>
                <?php endif; ?>
            </div>
            <div class="column"></div>
        </div>
    </div>
</section>

<script src="/assets/js/dashboard-avatars.js?v=<?php echo time(); ?>"></script>

<?php
$app->footer();
?>