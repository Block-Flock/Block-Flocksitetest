<?php
$title = 'Item';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include header or start session
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');
} else {
    session_start();
    echo "<!-- Header file not found -->";
}

// Validate item ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /store/');
    exit;
}

if (!isset($db)) {
    die('Database connection not available');
}

// Always ensure CSRF token exists
if (empty($_SESSION['CSRF-TOKEN'])) {
    $_SESSION['CSRF-TOKEN'] = bin2hex(random_bytes(32));
}

// === FETCH ITEM ===
try {
    $stmt = $db->prepare('SELECT store_items.*, users.username AS creator_name 
                          FROM store_items 
                          JOIN users ON users.id = store_items.creator 
                          WHERE store_items.id = ?');
    $stmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        header('Location: /store/');
        exit;
    }
    $si = $stmt->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die('Item load error: ' . $e->getMessage());
}

// === CHECK OWNERSHIP ===
$ownStatus = 0;
if (isset($AUTH) && $AUTH && isset($_SESSION['id'])) {
    $owns = $db->prepare('SELECT 1 FROM user_inventory WHERE item = ? AND user = ? LIMIT 1');
    $owns->execute([$_GET['id'], $_SESSION['id']]);
    $ownStatus = $owns->rowCount() > 0 ? 1 : 0;
}

// === LOAD COMMENTS (initial + load more) ===
$lastCommentId = 0;
$commentsPerPage = 20;

$commentStmt = $db->prepare("
    SELECT sc.*, sc.id AS id, sc.timecommented AS ctime,
           u.username, u.avatar_hash, u.id as user_id
    FROM store_comments sc
    JOIN users u ON sc.user = u.id
    WHERE sc.item = ? AND sc.moderation = 1
    " . (isset($_POST['morecomments']) ? "AND sc.id < ?" : "") . "
    ORDER BY sc.timecommented DESC
    LIMIT $commentsPerPage
");

if (isset($_POST['morecomments']) && !empty($_POST['last_id'])) {
    $lastCommentId = (int)$_POST['last_id'];
    $commentStmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $commentStmt->bindValue(2, $lastCommentId, PDO::PARAM_INT);
} else {
    $commentStmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);
}
$commentStmt->execute();
$comments = $commentStmt->fetchAll(PDO::FETCH_OBJ);

if (count($comments) > 0) {
    $lastCommentId = $comments[count($comments) - 1]->id;
}

// Count total comments for "Load More" button
$totalCommentsStmt = $db->prepare("SELECT COUNT(*) FROM store_comments WHERE item = ? AND moderation = 1");
$totalCommentsStmt->execute([$_GET['id']]);
$totalComments = $totalCommentsStmt->fetchColumn();

// === GET USER DATA FOR AVATAR RENDERING ===
// Get all user IDs from comments for batch fetching avatar data
$allUserIds = [];
foreach ($comments as $comment) {
    $allUserIds[] = (int)$comment->user_id;
}
// Add current user if logged in
if (isset($my->id)) {
    $allUserIds[] = (int)$my->id;
}
// Add item creator
$allUserIds[] = (int)$si->creator;
$allUserIds = array_unique($allUserIds);

$userColors = [];
$userWearables = [];

if (!empty($allUserIds)) {
    $placeholders = str_repeat('?,', count($allUserIds) - 1) . '?';
    
    // Fetch colors
    $colorStmt = $db->prepare("SELECT * FROM user_colors WHERE user IN ($placeholders)");
    $colorStmt->execute($allUserIds);
    while ($color = $colorStmt->fetch(PDO::FETCH_OBJ)) {
        $userColors[(int)$color->user] = $color;
    }

    // Fetch wearables including face
    $wearableStmt = $db->prepare("SELECT * FROM user_data WHERE user IN ($placeholders)");
    $wearableStmt->execute($allUserIds);
    while ($wearable = $wearableStmt->fetch(PDO::FETCH_OBJ)) {
        $userWearables[(int)$wearable->user] = $wearable;
    }
}

// === SECURE PURCHASE HANDLER ===
$err = $successmsg = '';

if (isset($_POST['price'], $_POST['csrf']) && $AUTH && isset($_SESSION['id'])) {
    if (!hash_equals($_SESSION['CSRF-TOKEN'], $_POST['csrf'])) {
        $err = 'Invalid security token.';
    } elseif ($_POST['price'] != $si->price) {
        $err = 'Price mismatch.';
    } elseif ($si->offsale != 0) {
        $err = 'Item is offsale.';
    } elseif ($ownStatus == 1) {
        $err = 'You already own this item.';
    } elseif (!isset($my)) {
        $err = 'Session error. Please log in again.';
    } else {
        try {
            $db->beginTransaction();

            // Lock item and user rows
            $itemLock = $db->prepare("SELECT * FROM store_items WHERE id = ? FOR UPDATE");
            $itemLock->execute([$_GET['id']]);
            $item = $itemLock->fetch(PDO::FETCH_OBJ);

            $userLock = $db->prepare("SELECT Currency, flood, premium FROM users WHERE id = ? FOR UPDATE");
            $userLock->execute([$my->id]);
            $buyer = $userLock->fetch(PDO::FETCH_OBJ);

            if (!$item || $item->offsale != 0 || $item->price != $si->price) {
                throw new Exception("Item no longer available");
            }
            
            // Check stock only if it's not unlimited (stock != -1)
            if ($item->stock !== null && $item->stock != -1 && $item->stock <= 0) {
                throw new Exception("Out of stock");
            }
            
            if ($buyer->Currency < $item->price) {
                throw new Exception("Not enough coins");
            }
            if ($buyer->flood > time()) {
                throw new Exception("Flood protection active");
            }

            // Deduct coins + flood
            $db->prepare("UPDATE users SET Currency = Currency - ?, flood = ? WHERE id = ?")
               ->execute([$item->price, time() + 10, $my->id]);

            // Decrement stock only if it's not unlimited (stock != -1)
            if ($item->stock !== null && $item->stock != -1 && $item->stock > 0) {
                $stockUpdate = $db->prepare("UPDATE store_items SET stock = stock - 1 WHERE id = ?");
                $stockUpdate->execute([$_GET['id']]);
                
                // Check if stock reached 0 after update to mark as offsale
                if ($item->stock == 1) { // If it was the last one
                    $checkStock = $db->prepare("SELECT stock FROM store_items WHERE id = ?");
                    $checkStock->execute([$_GET['id']]);
                    $newStock = $checkStock->fetchColumn();
                    
                    if ($newStock == 0) {
                        $db->prepare("UPDATE store_items SET offsale = 1 WHERE id = ?")
                           ->execute([$_GET['id']]);
                    }
                }
            }

            // Add to inventory
            $db->prepare("INSERT INTO user_inventory (user, item, timebought, serialID) VALUES (?, ?, ?, 0)")
               ->execute([$my->id, $_GET['id'], time()]);

            // Pay seller (tax applied)
            $taxRate = $buyer->premium ? 0.85 : 0.50;
            $sellerGets = (int)floor($item->price * $taxRate);

            $db->prepare("UPDATE users SET Currency = Currency + ? WHERE id = ?")
               ->execute([$sellerGets, $si->creator]);

            // Update owners count
            $db->prepare("UPDATE store_items SET owners = owners + 1 WHERE id = ?")
               ->execute([$_GET['id']]);

            // Log transaction
            $db->prepare("INSERT INTO user_transaction (item_name, item_type, buyer_name, buyer_id, price, `date`) VALUES (?, ?, ?, ?, ?, ?)")
               ->execute([$si->name, $_GET['id'], $my->username, $my->id, $item->price, time()]);

            $db->commit();

            $_SESSION['purchase_success'] = "You bought {$si->name} for " . number_format($si->price) . " coins!";
            header("Location: /store/view/{$_GET['id']}");
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $err = $e->getMessage();
            error_log("Purchase failed: " . $e->getMessage());
        }
    }
}

if (isset($_SESSION['purchase_success'])) {
    $successmsg = $_SESSION['purchase_success'];
    unset($_SESSION['purchase_success']);
}

// === ADMIN MODERATION ACTIONS ===
if(isset($_POST['scrub_moderation']) && isset($AUTH) && $AUTH && isset($my) && $my->administrator >= 1) {
    try {
        $scrub = $db->prepare('DELETE FROM store_items WHERE id = ?');
        $scrub->bindValue(1, $_GET['id'], PDO::PARAM_INT);
        $scrub->execute();

        $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
        $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $log->bindValue(3, 'Item deleted', PDO::PARAM_STR);
        $log->bindValue(4, time(), PDO::PARAM_INT);
        $log->execute();

        header('Location: /store/');
        exit;
    } catch (PDOException $e) {
        $err = 'Deletion failed: ' . $e->getMessage();
    }
}

if(isset($_POST['scrub_about']) && isset($AUTH) && $AUTH && isset($my) && $my->administrator >= 1) {
    try {
        $scrub = $db->prepare('UPDATE store_items SET `about` = ? WHERE id = ?');
        $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
        $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $scrub->execute();

        $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
        $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $log->bindValue(3, 'Scrubbed Item Description', PDO::PARAM_STR);
        $log->bindValue(4, time(), PDO::PARAM_INT);
        $log->execute();

        header('Location: /store/view/'.$_GET['id']);
        exit;
    } catch (PDOException $e) {
        $err = 'Update failed: ' . $e->getMessage();
    }
}

if(isset($_POST['scrub_name']) && isset($AUTH) && $AUTH && isset($my) && $my->administrator >= 1) {
    try {
        $scrub = $db->prepare('UPDATE store_items SET `name` = ? WHERE id = ?');
        $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
        $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $scrub->execute();

        $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
        $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $log->bindValue(3, 'Scrubbed Item Name', PDO::PARAM_STR);
        $log->bindValue(4, time(), PDO::PARAM_INT);
        $log->execute();

        header('Location: /store/view/'.$_GET['id']);
        exit;
    } catch (PDOException $e) {
        $err = 'Update failed: ' . $e->getMessage();
    }
}

// Handle comments
if(isset($_POST['comment']) && isset($AUTH) && $AUTH && $si->allowcomments == 1 && isset($_POST['commentstring']) && is_string($_POST['commentstring']) && isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])) {
    
    // Check if Check class exists
    if (class_exists('Check')) {
        $check = new Check();
    } else {
        // Fallback simple profanity filter
        $check = new stdClass();
        $check->obfuscateIfProfane = function($text) { return $text; };
        $check->hasProfanity = function($text) { return false; };
    }

    $data = array(
        'secret' => "0xcFd62e907D377Bd68c2c659d52a5c0F0275C56F2",
        'response' => $_POST['h-captcha-response']
    );
    
    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($verify);
    $responseData = json_decode($response);
    curl_close($verify);

    if(!$responseData || !$responseData->success) {
        $err2 = 'Invalid captcha submission, please try again.';
    }

    if($si->allowcomments != 1) {
        $err2 = 'Comments are not allowed on this item.';
    }

    if (is_callable(array($check, 'obfuscateIfProfane'))) {
        $comment = $check->obfuscateIfProfane($_POST['commentstring']);
    } else {
        $comment = $_POST['commentstring'];
    }

    if(strlen($comment) < 1){
        $err2 = "Your comment must contain content!";
    } else if(strlen($comment) > 200) {
        $err2 = "Your comment cannot be longer than 200 characters!";
    } else if(count(preg_split("/\n|\r/", $comment)) > 5){
        $err2 = "Your comment cannot have more than 5 lines/rows!";
    } else if(is_callable(array($check, 'hasProfanity')) && $check->hasProfanity($_POST['commentstring'])) {
        $err2 = "Our filter has detected profanity in your comment. Please remove any profanity and try again";
    } else if(isset($my) && $my->flood > time()) {
        $err2 = 'Please wait another '.($my->flood-time()).' seconds before commenting under this item.';
    }

    if(!isset($err2)) {
        try {
            $cinsert = $db->prepare('INSERT INTO store_comments (user, item, comment) VALUES (?, ?, ?)');
            $cinsert->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
            $cinsert->bindValue(2, $_GET['id'], PDO::PARAM_INT);
            $cinsert->bindValue(3, $comment, PDO::PARAM_STR);
            $cinsert->execute();

            $updateFlood = $db->prepare('UPDATE users SET flood = ? WHERE id = ?');
            $updateFlood->bindValue(1, time()+30, PDO::PARAM_INT);
            $updateFlood->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
            $updateFlood->execute();

            $comment_id = $db->lastInsertId();
            header('Location: /store/view/'.$_GET['id'].'&comment='.$comment_id);
            exit;
        } catch (PDOException $e) {
            $err2 = 'Comment submission failed: ' . $e->getMessage();
        }
    }
}

// Handle other admin actions
if(isset($_POST['scrub_comment']) && isset($AUTH) && $AUTH && isset($my) && $my->administrator >= 1) {
    try {
        $scrub = $db->prepare('UPDATE store_comments SET `comment` = ? WHERE item = ?');
        $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
        $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $scrub->execute();

        $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
        $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $log->bindValue(3, 'Scrubbed a comment', PDO::PARAM_STR);
        $log->bindValue(4, time(), PDO::PARAM_INT);
        $log->execute();

        header('Location: /store/view/'.$_GET['id']);
        exit;
    } catch (PDOException $e) {
        $err = 'Comment scrub failed: ' . $e->getMessage();
    }
}

if(isset($_POST['put']) && isset($AUTH) && $AUTH && isset($my) && $my->administrator >= 1) {
    try {
        $scrub = $db->prepare('UPDATE store_items SET `offsale` = ? WHERE id = ?');
        $scrub->bindValue(1, '1', PDO::PARAM_STR);
        $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $scrub->execute();

        $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
        $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $log->bindValue(3, 'Put item offsale', PDO::PARAM_STR);
        $log->bindValue(4, time(), PDO::PARAM_INT);
        $log->execute();

        header('Location: /store/view/'.$_GET['id']);
        exit;
    } catch (PDOException $e) {
        $err = 'Offsale update failed: ' . $e->getMessage();
    }
}

if(isset($_POST['take']) && isset($AUTH) && $AUTH && isset($my) && $my->administrator >= 1) {
    try {
        $scrub = $db->prepare('UPDATE store_items SET `offsale` = ? WHERE id = ?');
        $scrub->bindValue(1, '0', PDO::PARAM_STR);
        $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $scrub->execute();

        $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
        $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $log->bindValue(3, 'Put item onsale', PDO::PARAM_STR);
        $log->bindValue(4, time(), PDO::PARAM_INT);
        $log->execute();

        header('Location: /store/view/'.$_GET['id']);
        exit;
    } catch (PDOException $e) {
        $err = 'Onsale update failed: ' . $e->getMessage();
    }
}

// Get user inventory for reselling
$userInventoryItems = [];
if(isset($AUTH) && $AUTH && $si->special == 1 && $ownStatus == 1) {
    try {
        $getInventory = $db->prepare('SELECT * FROM user_inventory WHERE item = ? AND user = ? AND CanTrade = 1');
        $getInventory->bindValue(1, $_GET['id'], PDO::PARAM_INT);
        $getInventory->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $getInventory->execute();
        $userInventoryItems = $getInventory->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        // Handle error silently or log it
        $userInventoryItems = [];
    }
}

// Handle reselling
if (!empty($_POST['SellInventoryID']) && !empty($_POST['SellPrice']) && $si->special == 1) {
    try {
        $Count = $db->prepare("SELECT COUNT(*) FROM user_inventory WHERE id = ? AND user = ? AND CanTrade = 1");
        $Count->bindValue(1, $_POST['SellInventoryID'], PDO::PARAM_INT);
        $Count->bindValue(2, $my->id, PDO::PARAM_INT);
        $Count->execute();

        if ($Count->fetchColumn() == 0) {
            $returnError = 'An unexpected error has occurred.';
        } else if (!is_numeric($_POST['SellPrice']) || $_POST['SellPrice'] < 2) {
            $returnError = 'Invalid price, must be at least 2 Coins.';
        } else {
            $Insert = $db->prepare("INSERT INTO ItemSeller (ItemID, InventoryID, UserID, Price, TimeCreated) VALUES(?, ?, ?, ?, ?)");
            $Insert->bindValue(1, $si->id, PDO::PARAM_INT);
            $Insert->bindValue(2, $_POST['SellInventoryID'], PDO::PARAM_INT);
            $Insert->bindValue(3, $my->id, PDO::PARAM_INT);
            $Insert->bindValue(4, $_POST['SellPrice'], PDO::PARAM_INT);
            $Insert->bindValue(5, time(), PDO::PARAM_INT);
            $Insert->execute();

            header('Location: /store/view/'.$_GET['id']);
            exit;
        }
    } catch (PDOException $e) {
        $returnError = 'Selling failed: ' . $e->getMessage();
    }
}

if (!empty($_POST['RemoveItemSaleID']) && $si->special == 1) {
    try {
        $Delete = $db->prepare("DELETE FROM ItemSeller WHERE ID = ? AND ItemID = ? AND UserID = ?");
        $Delete->bindValue(1, $_POST['RemoveItemSaleID'], PDO::PARAM_INT);
        $Delete->bindValue(2, $si->id, PDO::PARAM_INT);
        $Delete->bindValue(3, $my->id, PDO::PARAM_INT);
        $Delete->execute();

        header('Location: /store/view/'.$_GET['id']);
        exit;
    } catch (PDOException $e) {
        $returnError = 'Remove sale failed: ' . $e->getMessage();
    }
}

if (!empty($_POST['PrivateSaleID']) && $si->special == 1) {
    try {
        $Get = $db->prepare("SELECT InventoryID, UserID, Price FROM ItemSeller WHERE ID = ? AND UserID != ?");
        $Get->bindValue(1, $_POST['PrivateSaleID'], PDO::PARAM_INT);
        $Get->bindValue(2, $my->id, PDO::PARAM_INT);
        $Get->execute();

        if ($Get->rowCount() > 0) {
            $G = $Get->fetch(PDO::FETCH_OBJ);

            if ($G->Price > $my->Currency) {
                $returnError = 'Insufficient funds.';
            } else {
                $db->beginTransaction();
                
                try {
                    $Remove = $db->prepare("UPDATE users SET Currency = Currency - ? WHERE ID = ?");
                    $Remove->bindValue(1, $G->Price, PDO::PARAM_INT);
                    $Remove->bindValue(2, $my->id, PDO::PARAM_INT);
                    $Remove->execute();

                    if ($Remove->rowCount() > 0) {
                        $Update = $db->prepare("UPDATE user_inventory SET user = ? WHERE id = ?");
                        $Update->bindValue(1, $my->id, PDO::PARAM_INT);
                        $Update->bindValue(2, $G->InventoryID, PDO::PARAM_INT);
                        $Update->execute();

                        $Delete = $db->prepare("DELETE FROM ItemSeller WHERE ID = ?");
                        $Delete->bindValue(1, $_POST['PrivateSaleID'], PDO::PARAM_INT);
                        $Delete->execute();

                        $Insert = $db->prepare("INSERT INTO ItemSellerHistory (ItemID, SellerID, BuyerID, Price, TimeSale) VALUES(?, ?, ?, ?, ?)");
                        $Insert->bindValue(1, $si->id, PDO::PARAM_INT);
                        $Insert->bindValue(2, $G->UserID, PDO::PARAM_INT);
                        $Insert->bindValue(3, $my->id, PDO::PARAM_INT);
                        $Insert->bindValue(4, $G->Price, PDO::PARAM_INT);
                        $Insert->bindValue(5, time(), PDO::PARAM_INT);
                        $Insert->execute();

                        $db->commit();
                        
                        header('Location: /store/view/'.$_GET['id']);
                        exit;
                    } else {
                        $db->rollBack();
                        $returnError = 'Transaction failed.';
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    $returnError = 'An error occurred during the transaction.';
                }
            }
        } else {
            $returnError = 'Item sale not found.';
        }
    } catch (PDOException $e) {
        $returnError = 'Purchase failed: ' . $e->getMessage();
    }
}

// Create fallback functions if they don't exist
if (!function_exists('timeAgo')) {
    function timeAgo($timestamp) {
        $time = time() - $timestamp;
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        return date('M j, Y', $timestamp);
    }
}

if (!defined('site')) {
    define('site', 'http://localhost'); // Replace with your site URL
}

// Function to create item-specific avatar data
function getItemAvatarData($itemId, $itemType, $userId = 0) {
    global $db;
    
    // Create empty avatar data structure
    $colors = [
        'head' => [1, 0.8, 0.6],
        'torso' => [0.2, 0.2, 0.8],
        'leftArm' => [1, 0.8, 0.6],
        'rightArm' => [1, 0.8, 0.6],
        'leftLeg' => [0.1, 0.1, 0.1],
        'rightLeg' => [0.1, 0.1, 0.1]
    ];
    
    // Create items array based on item type
    $items = [
        'hats' => [],
        'face' => 'default',
        'accessory' => 0,
        'tshirt' => 0,
        'shirt' => 0,
        'pants' => 0,
        'gear' => 0
    ];
    
    // Map store item type to avatar item field
    $typeMapping = [
        1 => ['field' => 'hats', 'is_array' => true],
        2 => ['field' => 'face', 'is_array' => false],
        3 => ['field' => 'shirt', 'is_array' => false],
        4 => ['field' => 'pants', 'is_array' => false],
        5 => ['field' => 'gear', 'is_array' => false],
        6 => ['field' => 'accessory', 'is_array' => false],
        7 => ['field' => 'face', 'is_array' => false], // Heads map to face
        8 => ['field' => 'tshirt', 'is_array' => false]
    ];
    
    if (isset($typeMapping[$itemType])) {
        $mapping = $typeMapping[$itemType];
        if ($mapping['is_array']) {
            $items[$mapping['field']] = [$itemId];
        } else {
            $items[$mapping['field']] = $itemId;
        }
    }
    
    return [
        'colors' => $colors,
        'items' => $items
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($si->name) ?> - Bricktaria</title>
    <meta property="og:title" content="<?= htmlspecialchars($si->name) ?>">
    <meta property="og:image" content="/assets/images/store/<?= htmlspecialchars($si->image) ?>.png">
    <?php
    // Add Three.js libraries like the store page does
    $extra_head_content = '
<!-- Three.js Libraries for Avatar Rendering -->
<script src="/assets/vendor/three.min.js"></script>
<script src="/assets/vendor/GLTFLoader.js"></script>
<script src="/assets/vendor/OBJLoader.js"></script>
';
    echo $extra_head_content;
    ?>
    <style>
        .item-preview { background:#f8f9fa; border-radius:8px; padding:20px; text-align:center; }
        .avatar-viewer { width:100%; height:300px; background:#fff; border:2px solid #e1e5e9; border-radius:8px; overflow:hidden; margin-bottom:15px; }
        .wall_post { margin-bottom:1rem; }
        .button i { margin-right: 0.5rem; }
        .store-item img { max-width: 100%; height: auto; border-radius: 4px; }
        .store-result-thumbnail img { width: 150px; height: 150px; object-fit: cover; }
        
        /* Modal styles */
        .modal { display: none; }
        .modal.is-active { display: flex; }
        .modal-background { background-color: rgba(10,10,10,.86); }
        .modal-card { margin: 0 20px; max-height: calc(100vh - 160px); overflow: auto; position: relative; width: 100%; }
        @media screen and (min-width: 769px) { .modal-card { margin: 0 auto; max-height: calc(100vh - 40px); width: 640px; } }
        .modal-card-head { align-items: center; background-color: #f5f5f5; border-bottom: 1px solid #dbdbdb; border-top-left-radius: 6px; border-top-right-radius: 6px; display: flex; flex-shrink: 0; justify-content: space-between; padding: 20px; position: relative; }
        .modal-card-title { color: #363636; flex-grow: 1; flex-shrink: 0; font-size: 1.5rem; line-height: 1; margin: 0; }
        .modal-card-body { background-color: #fff; flex-grow: 1; flex-shrink: 1; overflow: auto; padding: 20px; }
        .modal-card-foot { align-items: center; background-color: #f5f5f5; border-bottom-left-radius: 6px; border-bottom-right-radius: 6px; border-top: 1px solid #dbdbdb; display: flex; flex-shrink: 0; justify-content: flex-end; padding: 20px; }
        .delete { -webkit-touch-callout: none; -webkit-user-select: none; user-select: none; background-color: rgba(10,10,10,.2); border: none; border-radius: 290486px; cursor: pointer; display: inline-block; flex-grow: 0; flex-shrink: 0; font-size: 0; height: 20px; max-height: 20px; max-width: 20px; min-height: 20px; min-width: 20px; outline: none; position: relative; vertical-align: top; width: 20px; }
        .delete:before, .delete:after { background-color: #fff; content: ""; display: block; left: 50%; position: absolute; top: 50%; transform: translateX(-50%) translateY(-50%) rotate(45deg); transform-origin: center center; }
        .delete:before { height: 2px; width: 50%; }
        .delete:after { height: 50%; width: 2px; }
        
        /* Canvas styles from store code */
        .canvas-container {
            background: #16213e;
            position: relative;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            overflow: hidden;
        }
        .canvas-container canvas {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
        .item-card {
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .price-tag {
            background-color: #23d160;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            display: inline-block;
        }
        .unmoderated-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff9800;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            z-index: 10;
        }
        
        /* Custom styles for item view */
        .main-item-canvas {
            height: 300px !important;
            background: #16213e;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .recommended-canvas {
            height: 150px !important;
            background: #16213e;
            border-radius: 8px;
        }
        
        /* Comment avatar container */
        .comment-avatar-container {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            overflow: hidden;
            background: radial-gradient(circle at center, #252d3f 0%, #1a1f2e 100%);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
        }
        .comment-avatar {
            display: block;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
<section class="section"><div class="container">

<?php if ($err): ?>
    <div class="notification is-danger"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>
<?php if ($successmsg): ?>
    <div class="notification is-success"><?= htmlspecialchars($successmsg) ?></div>
<?php endif; ?>

<div class="columns">
    <div class="column is-4">
        <h2 class="title is-4">Overview</h2>
        <div class="item-preview">
            <!-- Main item 3D renderer - FIXED: Using same structure as dashboard -->
            <div class="avatar-viewer main-item-canvas" id="mainItemCanvas">
                <?php 
                    // Get avatar data for this item
                    $itemAvatarData = getItemAvatarData($si->id, $si->type);
                ?>
                <canvas id="store-item-<?= htmlspecialchars($_GET['id']) ?>" 
                        width="280" height="280" 
                        class="avatar-main avatar-canvas"
                        data-user-id="0" 
                        data-render-type="item" 
                        data-item-type-focus="<?= htmlspecialchars($si->type) ?>"
                        data-colors="<?= htmlspecialchars(json_encode($itemAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>" 
                        data-items="<?= htmlspecialchars(json_encode($itemAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>" 
                        data-hash="default">
                </canvas>
            </div>
        </div>
    </div>

    <div class="column is-8">
        <h2 class="title is-4">Information</h2>
        <div class="box">
            <h1 class="subtitle is-3"><?= htmlspecialchars($si->name) ?></h1>
            <p class="has-text-warning" style="font-size: 1.1rem;">
                <i class="fas fa-coins"></i> <?= number_format($si->price) ?> Coins
            </p>
            
            <!-- Updated stock display with unlimited support -->
            <?php if ($si->stock == -1): ?>
                <p class="has-text-grey" style="font-size: 0.9rem;">
                    <i class="fas fa-infinity"></i> Unlimited Stock
                </p>
            <?php elseif ($si->stock > 0): ?>
                <p class="has-text-grey" style="font-size: 0.9rem;">
                    Remaining: <?= number_format($si->stock) ?>
                </p>
            <?php endif; ?>
            
            <p class="mb-4"><?= nl2br(htmlspecialchars($si->about ?: 'No description.')) ?></p>

            <?php if(isset($returnError)) { ?>
            <div class="notification is-danger">
                <?php echo htmlspecialchars($returnError); ?>
            </div>
            <?php } ?>

            <div class="buttons">
                <?php if ($AUTH && $si->offsale == 0 && $ownStatus == 0 && isset($my)): ?>
                    <button class="button is-info" onclick="showPurchaseModal()">
                        <span class="icon"><i class="fas fa-shopping-cart"></i></span>
                        <span>Purchase</span>
                    </button>
                <?php elseif ($si->offsale == 1): ?>
                    <button class="button is-info" disabled>
                        <span class="icon"><i class="fas fa-ban"></i></span>
                        <span>Offsale</span>
                    </button>
                <?php elseif ($ownStatus == 1): ?>
                    <button class="button is-success" disabled>
                        <span class="icon"><i class="fas fa-check"></i></span>
                        <span>Owned</span>
                    </button>
                <?php endif; ?>
                
                <?php if(isset($AUTH) && $AUTH && isset($my) && $my->administrator >= 1 && isset($_SESSION['id']) && $_SESSION['id'] == ($si->creator ?? 0)) { ?>
                <a href="/store/edit/item/<?php echo $_GET['id']; ?>" class="button is-success">
                    <span class="icon"><i class="fas fa-edit"></i></span>
                    <span>Edit Item</span>
                </a>
                <?php } ?>
                
                <?php if(isset($AUTH) && $AUTH && isset($si->stock) && $si->stock == -1 && isset($si->special) && $si->special == 1 && $ownStatus == 1) { ?>
                <button class="button is-info" onclick="showResellModal()">
                    <span class="icon"><i class="fas fa-handshake"></i></span>
                    <span>Resell</span>
                </button>
                <?php } ?>
                
                <?php if(isset($AUTH) && $AUTH) { ?>
                <a href="/store/report/<?php echo $_GET['id']; ?>" class="button is-danger">
                    <span class="icon"><i class="fas fa-flag"></i></span>
                    <span>Report</span>
                </a>
                <?php } ?>
            </div>
            
            <?php if(isset($AUTH) && $AUTH && isset($my) && isset($my->administrator) && $my->administrator >= 1) { ?>
            <hr class="my-4">
            <div class="field is-grouped is-grouped-multiline">
                <form method="post" style="display: contents;">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN'] ?? ''; ?>">
                    <div class="control">
                        <button class="button is-danger is-small" name="scrub_name" type="submit">Scrub Name</button>
                    </div>
                    <div class="control">
                        <button class="button is-danger is-small" name="scrub_about" type="submit">Scrub About</button>
                    </div>
                    <div class="control">
                        <button class="button is-warning is-small" name="put" type="submit">Take Offsale</button>
                    </div>
                    <div class="control">
                        <button class="button is-success is-small" name="take" type="submit">Put Onsale</button>
                    </div>
                    <div class="control">
                        <button class="button is-danger is-small" name="scrub_moderation" type="submit"
                                onclick="return confirm('Are you sure you want to delete this item?')">Delete Item</button>
                    </div>
                </form>
            </div>
            <?php } ?>
        </div>

        <h2 class="title is-4 mt-6">Statistics</h2>
        <div class="box mb-4">
            <nav class="level">
                <div class="level-item has-text-centered">
                    <div>
                        <p class="heading"><i class="fas fa-crown"></i> Sales</p>
                        <p class="title"><?php echo number_format($si->owners ?? 0); ?></p>
                    </div>
                </div>
                <div class="level-item has-text-centered">
                    <div>
                        <p class="heading"><i class="fas fa-plus-circle"></i> Last Updated</p>
                        <p class="title"><?= isset($si->timeupdated) ? timeAgo($si->timeupdated) : 'Unknown'; ?></p>
                    </div>
                </div>
                <div class="level-item has-text-centered">
                    <div>
                        <p class="heading"><i class="fas fa-gavel"></i> Created</p>
                        <p class="title"><?= isset($si->timecreated) ? date("d/m/Y", $si->timecreated) : 'Unknown'; ?></p>
                    </div>
                </div>
            </nav>
        </div>

        <h2 class="title is-4">Suggested Items</h2>
        <div class="box mb-4">
            <div class="columns" id="recommendedItems">
                <?php
                try {
                    // Get items of the same type as the current item for better recommendations
                    $itemType = $si->type;
                    $limit3 = $db->prepare("SELECT `id`,`price`,`image`, `name`, `type` FROM `store_items` WHERE `type` = ? AND id != ? AND moderation = 1 AND offsale = 0 ORDER BY owners DESC, timecreated DESC LIMIT 4");
                    $limit3->bindValue(1, $itemType, PDO::PARAM_INT);
                    $limit3->bindValue(2, $_GET['id'], PDO::PARAM_INT);
                    $limit3->execute();
                    
                    if ($limit3->rowCount() > 0) {
                        while($limiterr = $limit3->fetch(PDO::FETCH_OBJ)) { 
                            $recommendedAvatarData = getItemAvatarData($limiterr->id, $limiterr->type);
                        ?>
                        <div class="column is-3">
                            <div class="box store-item item-card">
                                <a href="/store/view/<?php echo htmlspecialchars($limiterr->id);?>" class="store-result-thumbnail">
                                    <div class="canvas-container recommended-canvas">
                                        <canvas id="store-item-<?= htmlspecialchars($limiterr->id) ?>" 
                                                width="150" height="150" 
                                                class="avatar-user avatar-friend"
                                                data-user-id="0" 
                                                data-render-type="item" 
                                                data-item-type-focus="<?= htmlspecialchars($limiterr->type) ?>"
                                                data-colors="<?= htmlspecialchars(json_encode($recommendedAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                data-items="<?= htmlspecialchars(json_encode($recommendedAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                data-hash="default">
                                        </canvas>
                                    </div>
                                </a>
                                <div class="store-result-footer mt-3">
                                    <div class="has-text-centered">
                                        <a href="/store/view/<?php echo $limiterr->id;?>" class="title is-6 text-truncate">
                                            <?php echo htmlspecialchars($limiterr->name);?>
                                        </a>
                                        <div class="subtitle is-7 has-text-warning">
                                            <i class="fas fa-coins"></i> <?php echo number_format($limiterr->price);?> Coins
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php }
                    } else {
                        // Fallback to random items if no same-type items found
                        $fallback = $db->prepare("SELECT `id`,`price`,`image`, `name`, `type` FROM `store_items` WHERE moderation = 1 AND offsale = 0 AND id != ? ORDER BY RAND() LIMIT 4");
                        $fallback->bindValue(1, $_GET['id'], PDO::PARAM_INT);
                        $fallback->execute();
                        
                        if ($fallback->rowCount() > 0) {
                            while($limiterr = $fallback->fetch(PDO::FETCH_OBJ)) { 
                                $recommendedAvatarData = getItemAvatarData($limiterr->id, $limiterr->type);
                            ?>
                            <div class="column is-3">
                                <div class="box store-item item-card">
                                    <a href="/store/view/<?php echo htmlspecialchars($limiterr->id);?>" class="store-result-thumbnail">
                                        <div class="canvas-container recommended-canvas">
                                            <canvas id="store-item-<?= htmlspecialchars($limiterr->id) ?>" 
                                                    width="150" height="150" 
                                                    class="avatar-user avatar-friend"
                                                    data-user-id="0" 
                                                    data-render-type="item" 
                                                    data-item-type-focus="<?= htmlspecialchars($limiterr->type) ?>"
                                                    data-colors="<?= htmlspecialchars(json_encode($recommendedAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                    data-items="<?= htmlspecialchars(json_encode($recommendedAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                    data-hash="default">
                                            </canvas>
                                        </div>
                                    </a>
                                    <div class="store-result-footer mt-3">
                                        <div class="has-text-centered">
                                            <a href="/store/view/<?php echo $limiterr->id;?>" class="title is-6 text-truncate">
                                                <?php echo htmlspecialchars($limiterr->name);?>
                                            </a>
                                            <div class="subtitle is-7 has-text-warning">
                                                <i class="fas fa-coins"></i> <?php echo number_format($limiterr->price);?> Coins
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }
                        } else { ?>
                            <div class="column">
                                <p class="has-text-grey">No suggested items available.</p>
                            </div>
                        <?php }
                    }
                } catch (PDOException $e) { ?>
                    <div class="column">
                        <p class="has-text-danger">Error loading suggested items.</p>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Comments -->
        <h2 class="title is-4 mt-6">Comments (<?= $totalComments ?>)</h2>
        
        <?php if(isset($AUTH) && $AUTH && isset($si->allowcomments) && $si->allowcomments == 1 && isset($my)): ?>
        <article class="media mb-4">
            <figure class="media-left">
                <div class="comment-avatar-container">
                    <?php 
                        $myAvatarData = getAvatarData($my->id, $userColors, $userWearables);
                        if (!isset($myAvatarData['items'])) $myAvatarData['items'] = [];
                        $myAvatarData['items'] = normalize_avatar_items_array($myAvatarData['items']);
                    ?>
                    <canvas class="comment-avatar" 
                            id="comment-avatar-<?= (int)$my->id ?>"
                            width="64" height="64"
                            data-user-id="<?= (int)$my->id ?>" 
                            data-render-type="avatar" 
                            data-colors="<?= htmlspecialchars(json_encode($myAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>" 
                            data-items="<?= htmlspecialchars(json_encode($myAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>" 
                            data-hash="<?= htmlspecialchars($my->avatar_hash ?? 'default'); ?>">
                    </canvas>
                </div>
            </figure>
            <div class="media-content">
                <form action="" method="POST">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN'] ?? ''; ?>">
                    <div class="field">
                        <div class="control">
                            <textarea name="commentstring" class="textarea" placeholder="What are you thinking?" required maxlength="200" rows="3"></textarea>
                        </div>
                        <?php if(isset($err2)) { ?>
                        <p class="help is-danger"><?php echo htmlspecialchars($err2); ?></p>
                        <?php } ?>
                    </div>
                    <div class="field">
                        <div class="h-captcha" data-sitekey="14cddd71-a534-4587-9e69-ce41e1b52f89"></div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <button class="button is-info" type="submit" name="comment">
                                <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                <span>Post Comment</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </article>
        <?php elseif(!isset($AUTH) || !$AUTH): ?>
        <div class="notification is-info">
            <p><a href="/login">Login</a> to post comments.</p>
        </div>
        <?php elseif(!isset($si->allowcomments) || $si->allowcomments != 1): ?>
        <div class="notification is-warning">
            <p>Comments are disabled for this item.</p>
        </div>
        <?php endif; ?>
        
        <div class="comments-section">
            <?php foreach ($comments as $cm): ?>
            <div class="box wall_post">
                <article class="media">
                    <figure class="media-left">
                        <div class="comment-avatar-container">
                            <?php 
                                $commentUserAvatarData = getAvatarData($cm->user_id, $userColors, $userWearables);
                                if (!isset($commentUserAvatarData['items'])) $commentUserAvatarData['items'] = [];
                                $commentUserAvatarData['items'] = normalize_avatar_items_array($commentUserAvatarData['items']);
                            ?>
                            <canvas class="comment-avatar"
                                    id="comment-avatar-<?= (int)$cm->user_id ?>-<?= (int)$cm->id ?>"
                                    width="64" height="64"
                                    data-user-id="<?= (int)$cm->user_id ?>" 
                                    data-render-type="avatar" 
                                    data-colors="<?= htmlspecialchars(json_encode($commentUserAvatarData['colors']), ENT_QUOTES, 'UTF-8'); ?>" 
                                    data-items="<?= htmlspecialchars(json_encode($commentUserAvatarData['items']), ENT_QUOTES, 'UTF-8'); ?>" 
                                    data-hash="<?= htmlspecialchars($cm->avatar_hash ?? 'default'); ?>">
                            </canvas>
                        </div>
                    </figure>
                    <div class="media-content">
                        <div class="content">
                            <p>
                                <strong><a href="/user/<?=$cm->user_id ?? '';?>"><?= htmlspecialchars($cm->username ?? 'Unknown User'); ?></a></strong>
                                <small class="has-text-grey-light"><?= timeAgo($cm->timecommented) ?></small>
                                <br>
                                <?= nl2br(htmlspecialchars($cm->comment ?? '')); ?>
                            </p>
                        </div>
                        <?php if(isset($AUTH) && $AUTH && isset($my) && isset($my->administrator) && $my->administrator >= 1) { ?>
                        <div class="level">
                            <div class="level-right">
                                <div class="level-item">
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN'] ?? ''; ?>">
                                        <button class="button is-danger is-small" name="scrub_comment" type="submit"
                                                onclick="return confirm('Are you sure you want to scrub this comment?')">
                                            <span class="icon"><i class="fas fa-eraser"></i></span>
                                            <span>Scrub</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>

            <?php if ($totalComments > count($comments)): ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN'] ?>">
                <input type="hidden" name="last_id" value="<?= $lastCommentId ?>">
                <button name="morecomments" type="submit" class="button is-info is-fullwidth">
                    <span class="icon"><i class="fas fa-chevron-down"></i></span>
                    <span>Load More Comments</span>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</div></section>

<!-- Purchase Modal -->
<?php if ($AUTH): ?>
<div class="modal" id="purchaseModal">
    <div class="modal-background" onclick="hidePurchaseModal()"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirm Purchase</p>
            <button class="delete" onclick="hidePurchaseModal()"></button>
        </header>
        <section class="modal-card-body">
            <div class="content">
                <p><strong>Item:</strong> <?= htmlspecialchars($si->name) ?></p>
                <p><strong>Price:</strong> <i class="fas fa-coins"></i> <?= number_format($si->price) ?> Coins</p>
                
                <?php if(isset($my)): ?>
                    <?php $taxRate = ($my->premium == 0) ? 50 : 15; ?>
                    <p><strong>Tax:</strong> <?php echo $taxRate; ?>% 
                        <?php if($my->premium == 0): ?>
                            <span class="tag is-warning">Get premium for lower tax!</span>
                        <?php else: ?>
                            <span class="tag is-success">Premium tax rate</span>
                        <?php endif; ?>
                    </p>
                    
                    <p><strong>Seller receives:</strong> <i class="fas fa-coins"></i> 
                        <?php echo number_format(floor($si->price * (($my->premium == 0) ? 0.50 : 0.85))); ?> Coins
                    </p>
                    
                    <p>Your balance after this transaction will be <strong class="has-text-warning">
                        <i class="fas fa-coins"></i> <?= number_format($my->Currency - $si->price) ?> Coins
                    </strong></p>
                    
                    <p class="has-text-danger is-size-7">This purchase cannot be refunded.</p>
                <?php endif; ?>
            </div>
        </section>
        <footer class="modal-card-foot">
            <form method="post">
                <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN'] ?>">
                <button name="price" value="<?= $si->price ?>" class="button is-info">
                    <span class="icon"><i class="fas fa-shopping-cart"></i></span>
                    <span>Confirm Purchase</span>
                </button>
                <button type="button" class="button" onclick="hidePurchaseModal()">Cancel</button>
            </form>
        </footer>
    </div>
</div>
<?php endif; ?>

<!-- Resell Modal -->
<?php if(isset($AUTH) && $AUTH && isset($si) && $si->special == 1 && $ownStatus == 1) { ?>
<div class="modal" id="resellModal">
    <div class="modal-background" onclick="hideResellModal()"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Resell <?php echo htmlspecialchars($si->name ?? 'Item'); ?></p>
            <button class="delete" aria-label="close" onclick="hideResellModal()"></button>
        </header>
        <section class="modal-card-body">
            <form action="" method="POST" id="resellForm">
                <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN'] ?? ''; ?>">
                <div class="field">
                    <label class="label">Select Copy to Sell</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="SellInventoryID" required>
                                <option value="">Choose a copy...</option>
                                <?php foreach($userInventoryItems as $item) { ?>
                                <option value="<?= $item->id ?>">Copy #<?= $item->id ?> (Serial: <?= $item->serialID ?>)</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Price (minimum 2 coins)</label>
                    <div class="control">
                        <input class="input" type="number" name="SellPrice" min="2" value="2" placeholder="Enter price" required>
                    </div>
                </div>
            </form>
        </section>
        <footer class="modal-card-foot">
            <button type="submit" class="button is-info" form="resellForm">
                <span class="icon"><i class="fas fa-handshake"></i></span>
                <span>List for Sale</span>
            </button>
            <button type="button" class="button" onclick="hideResellModal()">Cancel</button>
        </footer>
    </div>
</div>
<?php } ?>

<!-- The main dashboard avatars script (cache-busted) -->
<script src="/assets/js/dashboard-avatars.js?v=<?php echo time(); ?>" defer></script>
<script>
// Wait for the dashboard-avatars.js to load and then initialize item rendering
document.addEventListener('DOMContentLoaded', function() {
    console.log('Store view page loaded, waiting for avatar renderer...');
    
    // The dashboard-avatars.js script uses IntersectionObserver to initialize canvases
    // All canvases with data-user-id will be automatically processed
    
    // Check if dashboard-avatars.js has initialized
    function checkAvatarRenderer() {
        if (typeof window.THREE !== 'undefined' && window.THREE_LOADED) {
            console.log('Three.js loaded, avatar renderer should initialize canvases');
            
            // Force initialization of all canvases
            const canvases = document.querySelectorAll('canvas[data-user-id]');
            console.log('Found', canvases.length, 'avatar canvases to render');
            
            canvases.forEach(canvas => {
                if (!canvas.dataset.rendered) {
                    console.log('Initializing canvas:', canvas.id);
                    canvas.dataset.rendered = 'true';
                    
                    // Create a new renderer instance for each canvas
                    try {
                        new DashboardAvatarRenderer3DTo2D(canvas);
                    } catch (e) {
                        console.error('Failed to initialize canvas', canvas.id, e);
                    }
                }
            });
        } else {
            // Check again after a delay
            setTimeout(checkAvatarRenderer, 500);
        }
    }
    
    // Start checking for avatar renderer
    checkAvatarRenderer();
});

function showPurchaseModal() {
    document.getElementById('purchaseModal').style.display = 'flex';
}

function hidePurchaseModal() {
    document.getElementById('purchaseModal').style.display = 'none';
}

function showResellModal() {
    document.getElementById('resellModal').style.display = 'flex';
}

function hideResellModal() {
    document.getElementById('resellModal').style.display = 'none';
}

document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') {
        hidePurchaseModal();
        hideResellModal();
    }
});
</script>

<?php
// Footer
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php');
}
?>
</body>
</html>