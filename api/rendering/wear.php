<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

if(isset($_POST['item']) && $AUTH) {
    if(!is_numeric($_POST['item'])) {
        die('false');
    }

    $getItem = $db->prepare('SELECT * FROM store_items WHERE id = ? AND moderation = 1');
    $getItem->bindValue(1, $_POST['item'], PDO::PARAM_INT);
    $getItem->execute();

    if($getItem->rowCount() == 0) {
        die('false');
    }

    $gi = $getItem->fetch(PDO::FETCH_OBJ);

    $getWearing = $db->prepare('SELECT * FROM user_data WHERE user = ?');
    $getWearing->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $getWearing->execute();
    $gw = $getWearing->fetch(PDO::FETCH_OBJ);

    // Check if user owns the item
    $ownsItem = $db->prepare("SELECT * FROM user_inventory WHERE item = ? AND user = ?");
    $ownsItem->bindValue(1, $_POST['item'], PDO::PARAM_INT);
    $ownsItem->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $ownsItem->execute();
    
    if($ownsItem->rowCount() <= 0) {
        die('false');
    }

    $fieldToUpdate = '';
    $clearExisting = false;
    
    // Determine which field to update based on item type
    switch($gi->type) {
        case 1: // Hats
            if(empty($gw->hat1)) {
                $fieldToUpdate = 'hat1';
            } else if(empty($gw->hat2)) {
                $fieldToUpdate = 'hat2';
            } else if(empty($gw->hat3)) {
                $fieldToUpdate = 'hat3';
            } else {
                // All hat slots full - replace the first one
                $fieldToUpdate = 'hat1';
                $clearExisting = true;
            }
            break;
        case 2: // Faces
            $fieldToUpdate = 'face';
            $clearExisting = true;
            break;
        case 3: // Shirts
            $fieldToUpdate = 'shirt';
            $clearExisting = true;
            break;
        case 4: // Pants
            $fieldToUpdate = 'pants';
            $clearExisting = true;
            break;
        case 5: // Gear
            $fieldToUpdate = 'gear';
            $clearExisting = true;
            break;
        case 6: // Accessories
            $fieldToUpdate = 'accessory';
            $clearExisting = true;
            break;
        case 7: // Heads
            $fieldToUpdate = 'head';
            $clearExisting = true;
            break;
        case 8: // Tshirts
            $fieldToUpdate = 'tshirt';
            $clearExisting = true;
            break;
        default:
            die('false');
    }

    // Update the database
    if($clearExisting) {
        $clearStmt = $db->prepare("UPDATE user_data SET $fieldToUpdate = NULL WHERE user = ?");
        $clearStmt->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $clearStmt->execute();
    }

    $updateStmt = $db->prepare("UPDATE user_data SET $fieldToUpdate = ? WHERE user = ?");
    $updateStmt->bindValue(1, $_POST['item'], PDO::PARAM_INT);
    $updateStmt->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $updateStmt->execute();

    echo 'success';
}