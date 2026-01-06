<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

if(isset($_POST['item']) && isset($_POST['field']) && $AUTH) {
    if(!is_numeric($_POST['item'])) {
        die('false');
    }

    $field = $_POST['field'];
    $allowedFields = ['hat1', 'hat2', 'hat3', 'face', 'shirt', 'pants', 'gear', 'accessory', 'head', 'tshirt'];
    
    if(!in_array($field, $allowedFields)) {
        die('false');
    }

    // For hats, we need to clear the specific slot
    if(strpos($field, 'hat') === 0) {
        $updateStmt = $db->prepare("UPDATE user_data SET $field = NULL WHERE user = ?");
        $updateStmt->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $updateStmt->execute();
    } 
    // For other fields, just set to 0 (or NULL)
    else {
        $updateStmt = $db->prepare("UPDATE user_data SET $field = 0 WHERE user = ?");
        $updateStmt->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $updateStmt->execute();
    }

    echo 'success';
}