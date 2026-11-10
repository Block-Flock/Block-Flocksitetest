<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

if (isset($my->id)) {
    $InsertUserActionLog = $db->prepare("INSERT INTO `useractionlogs` (`UserID`, `Action`, `TimeLog`, `IP`) VALUES(?, ?, ?, ?)");
    $InsertUserActionLog->bindValue(1, $my->id, PDO::PARAM_INT);
    $InsertUserActionLog->bindValue(2, 'Logged Out Of Their Account.', PDO::PARAM_STR);
    $InsertUserActionLog->bindValue(3, time(), PDO::PARAM_INT);
    $InsertUserActionLog->bindValue(4, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
    $InsertUserActionLog->execute();
}

// Ensure session is started if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_unset();
session_destroy();

header("Location: /");
exit();
