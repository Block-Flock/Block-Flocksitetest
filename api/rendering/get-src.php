<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

if(!$AUTH) { die(); }

$getUserAvatar = $db->prepare('SELECT * FROM users WHERE id = ?');
$getUserAvatar->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$getUserAvatar->execute();

if($getUserAvatar->rowCount() == 0) {

    die;

}

$gua = $getUserAvatar->fetch(PDO::FETCH_OBJ);

echo '/assets/images/avatars/'.htmlspecialchars($gua->avatar_hash).'.png?r'.bin2hex(random_bytes(16));
die;