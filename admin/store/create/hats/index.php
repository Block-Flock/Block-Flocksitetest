<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$title = 'Admin Control Panel';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {
    echo "Debug: Not Authenticated (AUTH is false)";
    die();
}

if($AUTH && $my->administrator < 2) {
    echo "Debug: Insufficient Privileges. User Level: " . $my->administrator . " (Required: 2)";
    die();
} 

if(isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_FILES['image']['name']) && $AUTH && $my->administrator >= 2) {

    $name = $_POST['name'];
    $description = $_POST['description'];

    if(!is_numeric($_POST['price']) || $_POST['price'] < -1) {
        $err = "Price is Invalid";
    } else if(strlen(str_replace(' ', '', $_POST['name'])) < 2) {
        $err = "Your hat name must be at least 2 characters.";
    } else if(strlen($_POST['name']) > 250) {
        $err = "Your hat name must be less than 250 characters.";
    } else if(strlen(str_replace(' ', '', $_POST['description'])) < 3) {
        $err = "Your hat description must be above 3 characters.";
    } else if(strlen($_POST['description']) > 250) {
        $err = "Your hat description must be less than 250 characters.";
    } else if(empty($_FILES['image']['name'])) {
        $err = "You need to upload an image.";
    } else if(!getimagesize($_FILES['image']['tmp_name'])) {
        $err = "Invalid image.";
    } else if($_FILES['image']['size'] > 1000000) {
        $err = "Your image must weigh less than 1MB.";
    } else if(empty($_FILES['obj']['name'])) {
        $err = "You need to upload an obj.";
    } else if($_FILES['obj']['size'] > 5000000) {
        $err = "Your obj must weigh less than 5MB.";
    } else if(empty($_FILES['texture']['name'])) {
        $err = "You need to upload a texture.";
    } else if(!getimagesize($_FILES['texture']['tmp_name'])) {
        $err = "Invalid texture.";
    } else if($_FILES['texture']['size'] > 2000000) {
        $err = "Your texture must weigh less than 2MB.";
    }

    if(!isset($err)) {
        $Hash = bin2hex(random_bytes(16));

        $insert = $db->prepare('INSERT INTO store_items (`name`, about, creator, `type`, price, offsale, moderation, `image`, timeupdated, timecreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->bindValue(1, $name, PDO::PARAM_STR);
        $insert->bindValue(2, $description, PDO::PARAM_STR);
        $insert->bindValue(3, $_SESSION['id'], PDO::PARAM_INT);
        $insert->bindValue(4, '1', PDO::PARAM_INT); // Type 1 for Hats
        $insert->bindValue(5, $_POST['price'], PDO::PARAM_INT);
        $insert->bindValue(6, ($_POST['price'] == -1) ? '1' : '0', PDO::PARAM_INT);
        $insert->bindValue(7, '1', PDO::PARAM_STR);
        $insert->bindValue(8, $Hash, PDO::PARAM_STR);
        $insert->bindValue(9, time(), PDO::PARAM_INT);
        $insert->bindValue(10, time(), PDO::PARAM_INT);
        $insert->execute();

        $id = $db->lastInsertId();

        $db->prepare('UPDATE store_items SET obj = ?, texture = ? WHERE id = ?')->execute([$id . '.obj', $id . '.png', $id]);

        $invinsert = $db->prepare('INSERT INTO user_inventory (user, item, timebought) VALUES (?, ?, ?)');
        $invinsert->execute([$_SESSION['id'], $id, time()]);

        $db->prepare('UPDATE store_items SET owners = owners + 1 WHERE id = ?')->execute([$id]);

        // Define correct rendering path
        $renderPath = $_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$id;

        $storeImagePath = $_SERVER['DOCUMENT_ROOT'].'/assets/images/store/'.$Hash.'.png';
        if(move_uploaded_file($_FILES['image']['tmp_name'], $storeImagePath)) {
            if (!file_exists($renderPath)) {
                $mkdir_res = mkdir($renderPath, 0777, true);
            }

            if(move_uploaded_file($_FILES['obj']['tmp_name'], $renderPath.'/'.$id.'.obj')) {
                if(move_uploaded_file($_FILES['texture']['tmp_name'], $renderPath.'/'.$id.'.png')) {
                    header('Location: /store/view/'.$id);
                    die;
                } else {
                    $err = "Could not upload texture!";
                }
            } else {
                $err = "Could not upload OBJ file!";
            }
        } else {
            $err = "Could not upload store image!";
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
                            <li><a class="is-active" href="/admin/store/create/hats">Hats</a></li>
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
                    <h2 class="title is-4">Create New Hat</h2>
                    <?php if(isset($err)) { ?><p class="notification is-danger"><?php echo htmlspecialchars($err); ?></p><?php } ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control"><input class="input" type="text" name="name" placeholder="Name" required></div>
                        </div>
                        <div class="field">
                            <label class="label">Description</label>
                            <div class="control"><textarea class="textarea" name="description" placeholder="Description" required></textarea></div>
                        </div>
                        <div class="field">
                            <label class="label">Price</label>
                            <div class="control"><input class="input" type="number" name="price" placeholder="Price (-1 for Offsale)" required></div>
                        </div>
                        <div class="field">
                            <label class="label">Upload OBJ File</label>
                            <div class="control"><input class="input" type="file" name="obj" required></div>
                        </div>
                        <div class="field">
                            <label class="label">Upload Texture Image (.png)</label>
                            <div class="control"><input class="input" type="file" name="texture" required></div>
                        </div>
                        <div class="field">
                            <label class="label">Upload Store Image (.png)</label>
                            <div class="control"><input class="input" type="file" name="image" required></div>
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-primary">Create Hat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php'); ?>
