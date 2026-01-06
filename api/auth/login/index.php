<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN'] && !empty($_POST['username']) && !empty($_POST['password'])) {

$appname = strtolower($_POST['username']);
$password = $_POST['password'];

$getUserSTMT = $db->prepare("SELECT * FROM `users` WHERE `username` = ?");
$getUserSTMT->bindValue(1, $appname, PDO::PARAM_STR);
$getUserSTMT->execute();

$checkUsername = $getUserSTMT->fetch(PDO::FETCH_OBJ);
$gU = $checkUsername;
$realPassword = $gU->password;

if ($checkUsername) {
    if (password_verify($password, $realPassword)) {
        $_SESSION['id'] = $gU->id;
        $appID = $_SESSION['id'];

        // Log the login
        $InsertUserActionLog = $db->prepare("INSERT INTO `useractionlogs` (`UserID`, `Action`, `TimeLog`, `IP`) VALUES(?, ?, ?, ?)");
        $InsertUserActionLog->bindValue(1, $gU->id, PDO::PARAM_STR);
        $InsertUserActionLog->bindValue(2, 'Logged Into Their Account.', PDO::PARAM_STR);
        $InsertUserActionLog->bindValue(3, time(), PDO::PARAM_STR);
        $InsertUserActionLog->bindValue(4, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $InsertUserActionLog->execute();

        die("success");
    } else {
        die("incorrect-password");
    }
} else {
    die("no-user");
}
}