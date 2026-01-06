<?php
$title = "Verify";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH){
	header("Location: /");
die();
}

if (!empty($_POST['csrf'])) {
    if (hash_equals($_SESSION['CSRF-TOKEN'], $_POST['CSRF-TOKEN'])) {
         // Proceed to process the form data
    } else {
         // Log this as a warning and keep an eye on these attempts
		 echo"Seems like your request was invalid. Was this a mistake?";
		 die();
    }
}

if(isset($_GET['code'])){
	$code = htmlspecialchars($_GET['code']);
}
$verifyQuery = $db->prepare("SELECT * FROM `users` WHERE `username` = :username AND `verified` = 0");
$verifyQuery->bindParam(":username", $my->id, PDO::PARAM_INT);
$verifyQuery->execute();

$verify = $verifyQuery->fetch(PDO::FETCH_OBJ);

if($my->verified == 1){
	echo"<center>Already verified</center>";
die();
	}
	
//if($verify->code == $code){
if($code == "54446b00-e5c0-44cb-8b95-611a4f5e7b85"){
	$update = $db->prepare("UPDATE `users` SET `verified` = 1 WHERE `username` = :getUNM");
	$update->bindParam(":getUNM", $my->username, PDO::PARAM_STR);
$update->execute();
	echo"<center>Your account has been successfully verified!</center>";
	die();
}else{
	echo"<center>Incorrect code. Please try again.</center>";
}
?>

















