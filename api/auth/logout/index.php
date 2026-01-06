<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

$InsertUserActionLog = $db->prepare("INSERT INTO `useractionlogs` (`UserID`, `Action`, `TimeLog`, `IP`) VALUES(?, ?, ?, ?)");
$InsertUserActionLog->bindValue(1, $my->id, PDO::PARAM_STR);
$InsertUserActionLog->bindValue(2, 'Logged Out Of Their Account.', PDO::PARAM_STR);
$InsertUserActionLog->bindValue(3, time(), PDO::PARAM_STR);
$InsertUserActionLog->bindValue(4, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
$InsertUserActionLog->execute();
unset($_SESSION['id']);
session_destroy();
die("success");
die();
exit();