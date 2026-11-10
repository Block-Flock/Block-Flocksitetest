<?php
$title = "Verify";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH){
	header("Location: /");
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['CSRF-TOKEN'], $_POST['csrf'])) {
		 echo"Seems like your request was invalid. Was this a mistake?";
		 die();
    }
}

$code = '';
if(isset($_REQUEST['code'])){
	$code = htmlspecialchars($_REQUEST['code']);
}

if($my->verified == 1){
	echo"<center>Already verified</center>";
    die();
}
	
// Ensure we have a code to check
if(empty($code)) {
    echo "<center>No verification code provided.</center>";
    die();
}

$verifyTokenSTMT = $db->prepare("SELECT * FROM `users` WHERE `emailVerifyCode` = :code AND `username` = :username");
$verifyTokenSTMT->bindParam(":code", $code, PDO::PARAM_STR);
$verifyTokenSTMT->bindParam(":username", $my->username, PDO::PARAM_STR);
$verifyTokenSTMT->execute();

if($verifyTokenSTMT->rowCount() > 0){
	$update = $db->prepare("UPDATE `users` SET `verified` = 1, `email_verified` = 1, `emailVerifyCode` = '' WHERE `username` = :getUNM");
	$update->bindParam(":getUNM", $my->username, PDO::PARAM_STR);
    $update->execute();
	echo"<center>Your account has been successfully verified!</center>";
	die();
}else{
	echo"<center>Incorrect code. Please try again.</center>";
}
?>
