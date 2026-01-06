<?php
$title = 'Admin Control Panel';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {
    die();
}

if($AUTH && $my->administrator < 2) {
    die();
}

if(isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_FILES['image']['name']) && $AUTH && $my->administrator >= 2) {

    $name = $_POST['name'];
    $description = $_POST['description'];

    $selectItem = $db->prepare('SELECT * FROM store_items WHERE name = ?');
    $selectItem->bindValue(1, $name, PDO::PARAM_STR);
    $selectItem->execute();
    
    if($selectItem->rowCount() >= 1) {
        $err = "An item with this name already exists.";
    } else if(!is_numeric($_POST['price']) || $_POST['price'] < -1) {
        $err = "Price is Invalid";
    } else if(strlen(str_replace(' ', '', $_POST['name'])) < 1) {
        $err = 'Your face name must be at least 1 character.';
    } else if(empty($_FILES['image']['name'])) {
        $err = 'You need to upload an image.';
    }

    if(!isset($err)) {
        $Hash = bin2hex(random_bytes(16));
        $insert = $db->prepare('INSERT INTO store_items (`name`, about, creator, `type`, price, offsale, moderation, `image`, timeupdated, timecreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->bindValue(1, $name, PDO::PARAM_STR);
        $insert->bindValue(2, $description, PDO::PARAM_STR);
        $insert->bindValue(3, '1', PDO::PARAM_INT);
        $insert->bindValue(4, '2', PDO::PARAM_INT); // Type 2 for Faces
        $insert->bindValue(5, $_POST['price'], PDO::PARAM_INT);
        $insert->bindValue(6, ($_POST['price'] == -1) ? '1' : '0', PDO::PARAM_INT);
        $insert->bindValue(7, '1', PDO::PARAM_STR);
        $insert->bindValue(8, $Hash, PDO::PARAM_STR);
        $insert->bindValue(9, time(), PDO::PARAM_INT);
        $insert->bindValue(10, time(), PDO::PARAM_INT);
        $insert->execute();

        $id = $db->lastInsertId();

        $invinsert = $db->prepare('INSERT INTO user_inventory (user, item, timebought) VALUES (?, ?, ?)');
        $invinsert->execute([$_SESSION['id'], $id, time()]);
        
        $db->prepare('UPDATE store_items SET owners = owners + 1 WHERE id = ?')->execute([$id]);
        
        // Define correct rendering paths
        $storeImagePath = $_SERVER['DOCUMENT_ROOT'].'/assets/images/store/'.$Hash.'.png';
        $renderFaceDir = $_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/faces/'.$id;
        $renderFacePath = $renderFaceDir.'/'.$id.'.png';

        if(move_uploaded_file($_FILES['image']['tmp_name'], $storeImagePath)) {
            if (!file_exists($renderFaceDir)) {
                mkdir($renderFaceDir, 0777, true);
            }
            if(copy($storeImagePath, $renderFacePath)) {
                header('Location: /store/view/'.$id);
                die;
            } else {
                $err = 'Could not copy to rendering folder.';
            }
        } else {
            $err = 'Could not upload store image.';
        }
    }
}
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
                            <li><a href="/admin/review">Reviews</a></li>
                            <li><a href="/admin/reports/">Reports</a></li>
                        </ul>
                        <p class="menu-label">Assets Creation</p>
                        <ul class="menu-list">
                            <li><a href="/admin/store/create/hats">Hats</a></li>
                            <li><a class="is-active" href="/admin/store/create/face">Faces</a></li>
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
            <div class="column is-6">
                <div class="box">
                    <h2 class="title is-4">Create New Face</h2>
                    <?php if(isset($err)) { ?><p class="notification is-danger"><?php echo htmlspecialchars($err); ?></p><?php } ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control"><input class="input" type="text" name="name" maxlength="250" placeholder="Name" required></div>
                        </div>
                        <div class="field">
                            <label class="label">Description</label>
                            <div class="control"><textarea class="textarea" maxlength="250" name="description" placeholder="Description" required></textarea></div>
                        </div>
                        <div class="field">
                            <label class="label">Price</label>
                            <div class="control"><input class="input" type="number" name="price" placeholder="Price (-1 for Offsale)" required></div>
                        </div>
                        <div class="field">
                            <label class="label">Face Image (.png)</label>
                            <div class="control"><input class="input" type="file" name="image" required></div>
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-primary">Create Face</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php'); ?>
