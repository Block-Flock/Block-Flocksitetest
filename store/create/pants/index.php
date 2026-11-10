<?php
$title = 'Create Pants';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {
    header('Location: /log-in');
    die;
}

if(isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_FILES['image']['name']) && $AUTH) {
    
    $check = new Check();
    $name = $_POST['name'];
    $description = $_POST['description'];
    $status = (int)$_POST['status'];

    if(!is_numeric($_POST['price']) || $_POST['price'] < -1) {
        $err = "Price is Invalid";
    } else if(strlen(str_replace(' ', '', $_POST['name'])) < 3) {
        $err = "Your pants name must be at least 3 characters.";
    } else if($check->hasProfanity($name)) {
        $err = "Profanity detected in name.";
    } else if(empty($_FILES['image']['name'])) {
        $err = "You need to upload an image.";
    } else if(!getimagesize($_FILES['image']['tmp_name'])) {
        $err = "Invalid image.";
    } else if($my->verified == 0) {
        $err = 'Please verify your account to create new pants.';
    } else if($my->flood > time()) {
        $err = 'Please wait ' . ($my->flood - time()) . ' seconds.';
    }

    if(!isset($err)) {
        $Hash = bin2hex(random_bytes(16));

        $insert = $db->prepare('INSERT INTO store_items (`name`, about, creator, `type`, price, offsale, moderation, `image`, timeupdated, timecreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->bindValue(1, $name, PDO::PARAM_STR);
        $insert->bindValue(2, $description, PDO::PARAM_STR);
        $insert->bindValue(3, $_SESSION['id'], PDO::PARAM_INT);
        $insert->bindValue(4, '4', PDO::PARAM_INT); // Type 4 for Pants
        $insert->bindValue(5, $_POST['price'], PDO::PARAM_INT);
        $insert->bindValue(6, $status, PDO::PARAM_INT);
        $insert->bindValue(7, '0', PDO::PARAM_STR);
        $insert->bindValue(8, $Hash, PDO::PARAM_STR);
        $insert->bindValue(9, time(), PDO::PARAM_INT);
        $insert->bindValue(10, time(), PDO::PARAM_INT);
        $insert->execute();

        $id = $db->lastInsertId();

        $invinsert = $db->prepare('INSERT INTO user_inventory (user, item, timebought) VALUES (?, ?, ?)');
        $invinsert->execute([$_SESSION['id'], $id, time()]);

        $db->prepare('UPDATE store_items SET owners = owners + 1 WHERE id = ?')->execute([$id]);
        $db->prepare('UPDATE users SET flood = ? WHERE id = ?')->execute([time() + 30, $_SESSION['id']]);

        // Define correct rendering paths
        $storeImagePath = $_SERVER['DOCUMENT_ROOT'].'/assets/images/store/'.$Hash.'.png';
        $renderPantsDir = $_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/pants/'.$id;
        $renderPantsPath = $renderPantsDir.'/'.$id.'.png';

        if(move_uploaded_file($_FILES['image']['tmp_name'], $storeImagePath)) {
            if (!file_exists($renderPantsDir)) {
                mkdir($renderPantsDir, 0777, true);
            }
            if(copy($storeImagePath, $renderPantsPath)) {
                header('Location: /store/view/'.$id);
                die;
            } else {
                $err = "Could not setup rendering files.";
            }
        } else {
            $err = "Could not upload pants image.";
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
                        <p class="menu-label">Creator Area</p>
                        <ul class="menu-list">
                            <li><a href="/store/creator-area/">Back to Creator Area</a></li>
                            <li><a href="/store/create/shirt">Create Shirt</a></li>
                            <li><a class="is-active" href="/store/create/pants">Create Pants</a></li>
                            <li><a href="/store/create/tshirt">Create Tshirt</a></li>
                        </ul>
                    </div>
                </aside>
            </div>
            <div class="column is-6">
                <div class="box">
                    <h2 class="title is-4">Create New Pants</h2>
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
                            <div class="control"><input class="input" type="number" name="price" placeholder="Price" required></div>
                        </div>
                        <div class="field">
                            <label class="label">Status</label>
                            <div class="select is-fullwidth">
                                <select name="status">
                                    <option value="0">Onsale</option>
                                    <option value="1">Offsale</option>
                                </select>
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Pants Image (.png)</label>
                            <div class="file has-name is-fullwidth mb-3">
                                <label class="file-label">
                                    <input class="file-input" type="file" name="image" required>
                                    <span class="file-cta">
                                        <span class="file-icon"><i class="fas fa-upload"></i></span>
                                        <span class="file-label">Choose Image...</span>
                                    </span>
                                    <span class="file-name">No file selected</span>
                                </label>
                            </div>
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-info">Create Pants</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="column is-3">
                <div class="box">
                    <p class="label">Pants Template</p>
                    <img src="/assets/images/pantsTemplate2.png" alt="Template">
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', () => {
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
