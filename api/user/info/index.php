<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');
if(isset($_POST['user'])) {
    $appname = htmlspecialchars($_POST['user']);

    // Fetch user info
    $getUserSTMT = $db->prepare("SELECT * FROM `users` WHERE `id` = ?");
    $getUserSTMT->bindValue(1, $appname, PDO::PARAM_STR);
    $getUserSTMT->execute();
    $gU = $getUserSTMT->fetch(PDO::FETCH_OBJ);

    // Output user info as json
 if(isset($gU)) {

 $data =  ['username' => $gU->username, 'currency' => number_format($gU->Currency), 'blurb' => htmlspecialchars($gU->blurb), 'avatar' => $gU->avatar_hash, 'joinDate' => date('d/M/Y', $gU->joined), 'lastSeen' => date('d/M/Y', $gU->expire)];
        die(json_encode($data));
    }

}else{
    $data = ['error' => 'true', 'message' => 'invalid-paramaters'];
    die(json_encode($data));
}