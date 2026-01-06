<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN'] && !empty($_POST['adminid']) && !empty($_POST['password'])) {

$adminId = strtolower($_POST['adminid']);
$password = $_POST['password'];

$getUserSTMT = $db->prepare("SELECT * FROM `AdminAccounts` WHERE `AdminID` = ?");
$getUserSTMT->bindValue(1, $adminId, PDO::PARAM_STR);
$getUserSTMT->execute();

$checkUsername = $getUserSTMT->fetch(PDO::FETCH_OBJ);
$gU = $checkUsername;
$realPassword = $gU->Password;

if ($checkUsername) {
    if (password_verify($password, $realPassword)) {
        $_SESSION['ADMIN_KEY'] = $app->generateRandomString();

        // Log the login
        $InsertUserActionLog = $db->prepare("INSERT INTO `UserActionLogs` (`UserID`, `Action`, `TimeLog`, `IP`) VALUES(?, ?, ?, ?)");
        $InsertUserActionLog->bindValue(1, $gU->ID, PDO::PARAM_STR);
        $InsertUserActionLog->bindValue(2, 'Logged Into Their Admin Account.', PDO::PARAM_STR);
        $InsertUserActionLog->bindValue(3, time(), PDO::PARAM_STR);
        $InsertUserActionLog->bindValue(4, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $InsertUserActionLog->execute();

        die("success");
    } else {
        die("incorrect-password");
    }
} else {
    die("x");
}
}