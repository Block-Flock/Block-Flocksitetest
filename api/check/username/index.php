<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN'] && !empty($_POST['username'])) {

$appname = strtolower($_POST['username']);

  $checkProf = new Check();

$getUserSTMT = $db->prepare("SELECT * FROM `users` WHERE `username` = ?");
$getUserSTMT->bindValue(1, $appname, PDO::PARAM_STR);
$getUserSTMT->execute();

$gU = $getUserSTMT->fetch(PDO::FETCH_OBJ);

$errors = [];

if (strlen($appname) < 3) {
    $errors['username'] = "username-too-short";
    die("username-too-short");
}

else if (strlen($appname) > 15) {
    $errors['username'] = "username-too-long";
    die("username-too-long");
}

else if (!preg_match("/^[a-zA-Z0-9][\w\.]+[a-zA-Z0-9]$/", $appname)) {
    $errors['username'] = "invalid-username";
    die("invalid-username");
}

else if ($getUserSTMT->rowCount() > 0) {
    $errors['username'] = "username-taken";
    die("username-taken");
}
else if($checkProf->hasProfanity($appname)){
    $errors['username'] = "bad-username";
    die("bad-username");
     }

else if (empty($errors)) {
    die("passed");
}

}
















