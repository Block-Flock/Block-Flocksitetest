<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");

if (!empty($_POST['username'])) {

    $username = $_POST['username'];

    $query = $app->query("SELECT `id`, `username`, `avatar_hash` FROM `users` WHERE `username` = ?", [$username]);
    $gA = $query->fetch(PDO::FETCH_OBJ);

    if($query->rowCount() > 0) {
        $data = ['id' => $gA->id, 'username' => $gA->username, 'image_hash' => '/assets/images/avatars/headshots/' . $gA->avatar_hash . '.png'];
        die(json_encode($data));
    }else{
        $data = ['image_hash' => '/assets/images/avatars/headshots/default.png'];
        die(json_encode($data));
    }

}
