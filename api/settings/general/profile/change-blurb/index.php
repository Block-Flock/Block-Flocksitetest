<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");

if($AUTH && $_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN']) {

    $id = $my->id;
    $check = new Check();

    $content = $check->obfuscateIfProfane(htmlentities(strip_tags($_POST['content'])));
  
    $updateBlurbSTMT = $db->prepare("UPDATE `users` SET `Blurb` = ? WHERE `id` = ?");
    $updateBlurbSTMT->bindValue(1, $content, PDO::PARAM_STR);
    $updateBlurbSTMT->bindValue(2, $id, PDO::PARAM_INT);
    $updateBlurbSTMT->execute();

    die("success");
}
