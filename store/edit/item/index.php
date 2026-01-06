<?php
/* ==========================================================================
   INITIALIZATION & AUTH
   ========================================================================== */
$title = 'Edit Item';
require_once($_SERVER['DOCUMENT_ROOT'] . "/private/header.inc.php");

// 1. Strict Auth Check
if (!$AUTH || !isset($_SESSION['id'])) {
    header('Location: /login');
    exit;
}

// 2. ID Validation
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /store/');
    exit;
}
$id = (int)$_GET['id'];

// 3. Fetch Item Data
$stmt = $db->prepare('SELECT * FROM store_items WHERE id = ?');
$stmt->execute([$id]);
$si = $stmt->fetch(PDO::FETCH_OBJ);

if (!$si) {
    header('Location: /store/');
    exit;
}

// 4. Ownership/Admin Check
// Allow entry if user is the creator OR an administrator
$isAdmin = (isset($my->administrator) && $my->administrator >= 1);
if ($si->creator != $_SESSION['id'] && !$isAdmin) {
    header('Location: /store/');
    exit;
}

// 5. CSRF Setup
if (empty($_SESSION['CSRF-TOKEN'])) {
    $_SESSION['CSRF-TOKEN'] = bin2hex(random_bytes(32));
}

/* ==========================================================================
   FORM HANDLING
   ========================================================================== */
$err = null;
$success = null;

if (isset($_POST['submit'])) {
    
    // Security Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['CSRF-TOKEN']) {
        $err = 'Security token invalid. Please refresh the page.';
    } else {
        // Sanitize Inputs
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (int)$_POST['price'];
        $status = (int)$_POST['status']; // 0 = Onsale, 1 = Offsale

        // Validation
        if (empty($name)) {
            $err = "Item name cannot be empty.";
        } elseif ($price < 0) {
            $err = "Price cannot be negative.";
        } elseif (strlen($name) > 50) {
            $err = "Item name is too long.";
        } else {
            try {
                // Single Query Update (Much more efficient)
                $update = $db->prepare("
                    UPDATE store_items 
                    SET name = ?, about = ?, price = ?, offsale = ?, timeupdated = ? 
                    WHERE id = ?
                ");
                
                $update->execute([
                    $name, 
                    $description, 
                    $price, 
                    $status, 
                    time(), 
                    $id
                ]);

                $success = "Item updated successfully!";
                
                // Refresh data to show changes immediately
                $stmt->execute([$id]);
                $si = $stmt->fetch(PDO::FETCH_OBJ);
                
            } catch (PDOException $e) {
                $err = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit <?= htmlspecialchars($si->name) ?> - Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .preview-card {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border: 1px solid #dbdbdb;
        }
        .preview-image {
            max-height: 250px;
            width: auto;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1));
        }
    </style>
</head>
<body>

<?php if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/private/components/navbar.inc.php')) include($_SERVER['DOCUMENT_ROOT'] . '/private/components/navbar.inc.php'); ?>

<section class="section">
    <div class="container">
        
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/store">Store</a></li>
                <li><a href="/store/view/<?= $id ?>">Item</a></li>
                <li class="is-active"><a href="#" aria-current="page">Edit</a></li>
            </ul>
        </nav>

        <?php if($err): ?>
            <div class="notification is-danger">
                <button class="delete" onclick="this.parentElement.style.display='none'"></button>
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="notification is-success">
                <button class="delete" onclick="this.parentElement.style.display='none'"></button>
                <?= htmlspecialchars($success) ?>
                <br>
                <a href="/store/view/<?= $id ?>" style="text-decoration: underline;">Return to Item Page</a>
            </div>
        <?php endif; ?>

        <h1 class="title is-3">Edit Item</h1>
        
        <div class="columns">
            <div class="column is-4">
                <div class="preview-card">
                    <h6 class="title is-6 has-text-grey-light is-uppercase">Preview</h6>
                    <img src="/assets/images/store/<?= htmlspecialchars($si->image) ?>.png?t=<?= time() ?>" class="preview-image" alt="Preview">
                    
                    <hr>
                    <div class="level is-mobile">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading">Sold</p>
                                    <p class="title is-5"><?= number_format($si->owners) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <div>
                                    <p class="heading">Created</p>
                                    <p class="title is-5"><?= date('M Y', $si->timecreated) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-8">
                <div class="box">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['CSRF-TOKEN'] ?>">

                        <div class="field">
                            <label class="label">Item Name</label>
                            <div class="control has-icons-left">
                                <input class="input is-medium" type="text" name="name" 
                                       value="<?= htmlspecialchars($si->name) ?>" placeholder="Item Name" required maxlength="50">
                                <span class="icon is-small is-left"><i class="fas fa-tag"></i></span>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Description</label>
                            <div class="control">
                                <textarea class="textarea" name="description" rows="5" maxlength="1000"
                                          placeholder="Describe your item..."><?= htmlspecialchars($si->about) ?></textarea>
                            </div>
                            <p class="help">Max 1000 characters.</p>
                        </div>

                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Price (Coins)</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="number" name="price" 
                                               value="<?= htmlspecialchars($si->price) ?>" min="0" required>
                                        <span class="icon is-small is-left"><i class="fas fa-coins has-text-warning"></i></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="column">
                                <div class="field">
                                    <label class="label">Sale Status</label>
                                    <div class="control has-icons-left">
                                        <div class="select is-fullwidth">
                                            <select name="status">
                                                <option value="0" <?= ($si->offsale == 0) ? 'selected' : '' ?>>On Sale</option>
                                                <option value="1" <?= ($si->offsale == 1) ? 'selected' : '' ?>>Off Sale (Hidden)</option>
                                            </select>
                                        </div>
                                        <span class="icon is-small is-left"><i class="fas fa-globe"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <a href="/store/view/<?= $id ?>" class="button is-light">Cancel</a>
                            </div>
                            <div class="control">
                                <button type="submit" name="submit" class="button is-info">
                                    <span class="icon"><i class="fas fa-save"></i></span>
                                    <span>Save Changes</span>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php')) include($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php'); ?>

</body>
</html>