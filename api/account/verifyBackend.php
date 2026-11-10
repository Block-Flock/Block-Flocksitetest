<?php
$title = "Verify";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

if(!$AUTH){
	header("Location: /");
	die();
}

if (!empty($_POST['csrf']) && !hash_equals($_SESSION['CSRF-TOKEN'], $_POST['csrf'])) {
    echo "Seems like your request was invalid. Was this a mistake?";
    die();
}

if (isset($_POST['token'])) {
    $token = htmlspecialchars($_POST['token']);
} else {
    echo "No token provided.";
    die();
}

if ($my->verified == 1) {
    echo "<center>Already verified</center>";
    die();
}

$verifyTokenSTMT = $db->prepare("SELECT * FROM `users` WHERE `emailVerifyCode` = :token AND `id` = :id");
$verifyTokenSTMT->bindParam(":token", $token, PDO::PARAM_STR);
$verifyTokenSTMT->bindParam(":id", $my->id, PDO::PARAM_INT);
$verifyTokenSTMT->execute();

if ($verifyTokenSTMT->rowCount() > 0) {
    $update = $db->prepare("UPDATE `users` SET `verified` = 1, `email_verified` = 1, `emailVerifyCode` = '' WHERE `id` = :id");
    $update->bindParam(":id", $my->id, PDO::PARAM_INT);
    $update->execute();
    echo "<center>Your account has been successfully verified!</center>";
    die();
} else {
    echo "<center>Incorrect token. Please try again.</center>";
}
?>

















