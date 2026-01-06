<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN']) {

$email = strtolower($_POST['emailAddress']);

$getUserSTMT = $db->prepare("SELECT * FROM `users` WHERE `email` = ?");
$getUserSTMT->bindValue(1, $email, PDO::PARAM_STR);
$getUserSTMT->execute();

$gU = $getUserSTMT->fetch(PDO::FETCH_OBJ);

$errors = [];

if (strlen($email) > 128) {
    $errors['emailAddress'] = "email-too-long";
    die("email-too-long");
}
else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "invalid-email";
    die("invalid-email");
}
else if ($getUserSTMT->rowCount() > 0) {
    $errors['emailAddress'] = "email-taken";
    die("email-taken");
}

else if (empty($errors)) {
    die("passed");
}

}















