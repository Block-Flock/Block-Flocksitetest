<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$title = 'Create Head';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH || $my->administrator < 2) {
    header('Location: /');
    die();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && $AUTH && $my->administrator >= 2) {
    
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? $_POST['price'] : '';
    
    if(!is_numeric($price) || $price < -1) {
        $err = "Price is Invalid";
    } else if(strlen($name) < 2 || strlen($name) > 250) {
        $err = "Head name must be between 2 and 250 characters.";
    } else if(strlen($description) < 3 || strlen($description) > 250) {
        $err = "Description must be between 3 and 250 characters.";
    } else if(empty($_FILES['image']['name'])) {
        $err = "You need to upload a store image.";
    } else if($_FILES['image']['size'] > 2097152) {
        $err = "Store image must be less than 2MB.";
    } else if(empty($_FILES['obj']['name'])) {
        $err = "You need to upload an OBJ file.";
    } else if($_FILES['obj']['size'] > 5242880) {
        $err = "OBJ file must be less than 5MB.";
    }

    if(!isset($err)) {
        try {
            $Hash = bin2hex(random_bytes(16));
            
            $insert = $db->prepare('INSERT INTO store_items (`name`, about, creator, `type`, price, offsale, moderation, `image`, timeupdated, timecreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insert->bindValue(1, $name, PDO::PARAM_STR);
            $insert->bindValue(2, $description, PDO::PARAM_STR);
            $insert->bindValue(3, $_SESSION['id'], PDO::PARAM_INT);
            $insert->bindValue(4, '7', PDO::PARAM_INT); // Type 7 for Heads
            $insert->bindValue(5, $price, PDO::PARAM_INT);
            $insert->bindValue(6, ($price == -1) ? '1' : '0', PDO::PARAM_INT);
            $insert->bindValue(7, '1', PDO::PARAM_STR);
            $insert->bindValue(8, $Hash, PDO::PARAM_STR);
            $insert->bindValue(9, time(), PDO::PARAM_INT);
            $insert->bindValue(10, time(), PDO::PARAM_INT);
            $insert->execute();

            $id = $db->lastInsertId();

            $db->prepare('UPDATE store_items SET obj = ?, texture = ? WHERE id = ?')->execute([$id, $id, $id]);

            $invinsert = $db->prepare('INSERT INTO user_inventory (user, item, timebought) VALUES (?, ?, ?)');
            $invinsert->execute([$_SESSION['id'], $id, time()]);

            $db->prepare('UPDATE store_items SET owners = owners + 1 WHERE id = ?')->execute([$id]);

            $storeImagePath = $_SERVER['DOCUMENT_ROOT'].'/assets/images/store/'.$Hash.'.png';
            $renderDir = $_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/head/'.$id.'/';

            $storeImageDir = dirname($storeImagePath);
            if (!file_exists($storeImageDir)) {
                mkdir($storeImageDir, 0777, true);
            }
            if (!file_exists($renderDir)) {
                mkdir($renderDir, 0777, true);
            }

            if(move_uploaded_file($_FILES['image']['tmp_name'], $storeImagePath)) {
                if(move_uploaded_file($_FILES['obj']['tmp_name'], $renderDir.$id.'.obj')) {
                    header('Location: /store/view/'.$id);
                    die();
                } else {
                    $err = "Failed to move OBJ file to rendering directory.";
                }
            } else {
                $err = "Failed to upload store image.";
            }

        } catch (PDOException $e) {
            $err = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<div class="container main-container">
    <div class="columns">
        <div class="column is-3">
             <aside class="menu glass-box">
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
                    <li><a href="/admin/store/create/face">Faces</a></li>
                    <li><a href="/admin/store/create/gear">Gears</a></li>
                    <li><a class="is-active" href="/admin/store/create/head">Heads</a></li>
                </ul>
                <p class="menu-label">Transactions</p>
                <ul class="menu-list">
                    <li><a href="/admin/coins/">Coins</a></li>
                    <li><a href="/admin/premium">Premium</a></li>
                </ul>
            </aside>
        </div>
        <div class="column is-9">
            <div class="glass-box">
                <h2 class="title is-4">Create New Head</h2>
                <?php if(isset($err)): ?>
                    <div class="notification is-danger"><?php echo htmlspecialchars($err); ?></div>
                <?php endif; ?>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="field">
                        <label class="label">Name</label>
                        <div class="control">
                            <input class="input" type="text" name="name" placeholder="Item Name" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Description</label>
                        <div class="control">
                            <textarea class="textarea" name="description" placeholder="Item Description" required></textarea>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Price</label>
                        <div class="control">
                            <input class="input" type="number" name="price" placeholder="Price (-1 for Offsale)" required>
                        </div>
                    </div>
                    
                    <div class="file has-name is-fullwidth mb-3">
                        <label class="file-label">
                            <input class="file-input" type="file" name="obj" accept=".obj" required>
                            <span class="file-cta">
                                <span class="file-icon"><i class="fas fa-upload"></i></span>
                                <span class="file-label">Upload OBJ File...</span>
                            </span>
                            <span class="file-name">No file selected</span>
                        </label>
                    </div>

                    <div class="file has-name is-fullwidth mb-3">
                        <label class="file-label">
                            <input class="file-input" type="file" name="image" accept=".png,.jpg,.jpeg" required>
                            <span class="file-cta">
                                <span class="file-icon"><i class="fas fa-upload"></i></span>
                                <span class="file-label">Upload Store Image (.png)...</span>
                            </span>
                            <span class="file-name">No file selected</span>
                        </label>
                    </div>

                    <div class="control">
                        <button type="submit" class="button is-primary is-fullwidth">Create Head</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // file input selection
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', () => {
                if (input.files.length > 0) {
                    const fileName = input.parentNode.querySelector('.file-name');
                    if (fileName) {
                        fileName.textContent = input.files[0].name;
                    }
                }
            });
        });
    });
</script>
<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php'); ?>
