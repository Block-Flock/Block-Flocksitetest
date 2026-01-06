<?php
$title = 'Admin Control Panel';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH || $my->administrator < 1) {
    header('Location: /');
    die();
}

// Logic for accepting/declining assets
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asset_id'])) {
    $asset_id = (int)$_POST['asset_id'];
    
    if(isset($_POST['accept_true'])) {
        $Hash = bin2hex(random_bytes(16));
        $update = $db->prepare('UPDATE store_items SET moderation = 1, `image` = ? WHERE id = ?');
        $update->execute([$Hash, $asset_id]);
        
        // Handle rendering folder sync if needed (copying from temporary to final if applicable)
        // For shirts/pants/tshirts, they are already in JGFJFSHFSAH/.../$id/$id.png
        
        header('Location: /admin/review');
        die;
    }
    
    if(isset($_POST['accept_false'])) {
        $update = $db->prepare('UPDATE store_items SET moderation = 2, `image` = ? WHERE id = ?');
        $update->execute(['declined', $asset_id]);
        header('Location: /admin/review');
        die;
    }
}

$getAssets = $db->prepare('SELECT * FROM store_items WHERE moderation = 0 ORDER BY id DESC');
$getAssets->execute();
?>
<section class="section">
<div class="container">
    <div class="columns">
        <div class="column is-3">
            <aside class="menu">
                <div class="box">
                <p class="menu-label">General</p>
                <ul class="menu-list">
                    <li><a href="/admin">Dashboard</a></li>
                </ul>
                <p class="menu-label">Administration</p>
                <ul class="menu-list">
                    <li><a class="is-active" href="/admin/review">Reviews</a></li>
                    <li><a href="/admin/reports/">Reports</a></li>
                </ul>
                <p class="menu-label">Assets Creation</p>
                <ul class="menu-list">
                    <li><a href="/admin/store/create/hats">Hats</a></li>
                    <li><a href="/admin/store/create/face">Faces</a></li>
                    <li><a href="/admin/store/create/gear">Gears</a></li>
                    <li><a href="/admin/store/create/head">Heads</a></li>
                </ul>
                <p class="menu-label">Transactions</p>
                <ul class="menu-list">
                    <li><a href="/admin/coins/">Coins</a></li>
                    <li><a href="/admin/premium">Premium</a></li>
                </ul>
                </div>
            </aside>
        </div>
        
        <div class="column is-9">
            <div class="box">
                <h1 class="title is-4">Asset Moderation Queue</h1>
                
                <div class="tabs is-toggle is-fullwidth">
                    <ul>
                        <li class="is-active"><a>Clothing & Items</a></li>
                        <li><a href="/admin/review/groups">Groups</a></li>
                        <li><a href="/admin/review/adverts">Adverts</a></li>
                    </ul>
                </div>

                <?php if($getAssets->rowCount() == 0) { ?>
                    <div class="has-text-centered py-6">
                        <p class="is-size-5 has-text-grey">No pending assets in queue.</p>
                    </div>
                <?php } else { ?>
                    <div class="columns is-multiline">
                        <?php while($ga = $getAssets->fetch(PDO::FETCH_OBJ)) { 
                            $typeNames = [
                                1 => 'Hat', 2 => 'Face', 3 => 'Shirt', 4 => 'Pants',
                                5 => 'Gear', 6 => 'Accessory', 7 => 'Head', 8 => 'T-Shirt'
                            ];
                            $typeName = $typeNames[$ga->type] ?? 'Unknown';
                            
                            // Determine preview image path (pre-moderation image)
                            // Creators upload to /assets/images/store/$Hash.png or similar
                            // In our new create logic, we use $Hash
                            $previewImg = "/assets/images/store/" . $ga->image . ".png";
                        ?>
                            <div class="column is-4">
                                <div class="card">
                                    <header class="card-header">
                                        <p class="card-header-title"><?= htmlspecialchars($ga->name) ?> (<?= $typeName ?>)</p>
                                    </header>
                                    <div class="card-image has-text-centered p-3" style="background: #f0f0f0;">
                                        <img src="<?= $previewImg ?>" alt="Preview" style="max-height: 150px;" onerror="this.src='/assets/images/store/default.png'">
                                    </div>
                                    <footer class="card-footer">
                                        <form method="post" class="card-footer-item p-0">
                                            <input type="hidden" name="asset_id" value="<?= $ga->id ?>">
                                            <button type="submit" name="accept_true" class="button is-success is-fullwidth is-radiusless">Accept</button>
                                        </form>
                                        <form method="post" class="card-footer-item p-0">
                                            <input type="hidden" name="asset_id" value="<?= $ga->id ?>">
                                            <button type="submit" name="accept_false" class="button is-danger is-fullwidth is-radiusless">Decline</button>
                                        </form>
                                    </footer>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php'); ?>
