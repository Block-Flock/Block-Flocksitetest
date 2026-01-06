<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN']) {

$appname = $_POST['username'];
$password = $_POST['password'];
$passwordConfirm = $_POST['passwordConfirm'];
$email = $_POST['email'];
$encryptedPassword = password_hash($password, PASSWORD_ARGON2I);
$checkProf = new Check();

$getUsernamesSTMT = $db->prepare("SELECT * FROM `users` WHERE `username` = ?");
$getUsernamesSTMT->bindValue(1, $appname, PDO::PARAM_STR);
$getUsernamesSTMT->execute();

$gU = $getUsernamesSTMT->fetch(PDO::FETCH_OBJ);

$getEmailsSTMT = $db->prepare("SELECT * FROM `users` WHERE `email` = ?");
$getEmailsSTMT->bindValue(1, $email, PDO::PARAM_STR);
$getEmailsSTMT->execute();

$gE = $getEmailsSTMT->fetch(PDO::FETCH_OBJ);

$errors = [];

// Global
if(empty($appname && $password && $passwordConfirm && $email)) {
    $errors['global'] = "missing-information";
    die("missing-information");
}
// Usernames 
else if (strlen($appname) < 3) {
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

else if ($getEmailsSTMT->rowCount() > 0) {
    $errors['username'] = "username-taken";
    die("username-taken");
} 
else if($checkProf->hasProfanity($appname)){
    $errors['username'] = "bad-username";
    die("bad-username");
     }
// E-mails
else if(empty($email)) {
    $errors['email'] = "no-email";
    die("no-email");
}

else if (strlen($email) > 128) {
    $errors['email'] = "email-too-long";
    die("email-too-long");
}

else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "invalid-email";
    die("invalid-email");
}

else if ($getUsernamesSTMT->rowCount() > 0) {
    $errors['email'] = "email-taken";
    die("email-taken");
}

// Passwords
else if(empty($password)) {
    $errors['password'] = "no-password";
    die("no-password");
}

else if (strlen($password) < 6) {
    $errors['password'] = "password-too-short";
    die("password-too-short");
}

else if (strlen($password) > 45) {
    $errors['password'] = "password-too-long";
    die("password-too-long");
}

// Password Confirm
else if(empty($passwordConfirm)) {
    $errors['password'] = "no-password-confirm";
    die("no-password-confirm");
}

else if($passwordConfirm !== $password) {
    $errors['password'] = "password-confirmation-failed";
    die("password-confirmation-failed");
}

 
// SUCCESSFULL -  Contine to register
else if(empty($errors)) {
    $createUserSTMT = $db->prepare("INSERT INTO `users` (`username`, `email`, `password`,  `ip`, `joined`, `avatar_hash`) VALUES (?, ?, ?, ?, " . time() . ", ?)");
    $createUserSTMT->bindValue(1, $appname, PDO::PARAM_STR);
    $createUserSTMT->bindValue(2, $email, PDO::PARAM_STR);
    $createUserSTMT->bindValue(3, $encryptedPassword, PDO::PARAM_STR);
    $createUserSTMT->bindValue(4, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
    $createUserSTMT->bindValue(5, "default", PDO::PARAM_STR);
    $createUserSTMT->execute();
    
    $id = $db->lastInsertId();
    $_SESSION['id'] = $id;

    // Create default user colors
    $insertColors = $db->prepare('INSERT INTO user_colors (user, head, torso, left_arm, right_arm, left_leg, right_leg) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $insertColors->bindValue(1, $id, PDO::PARAM_INT);
    $insertColors->bindValue(2, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default head color
    $insertColors->bindValue(3, '0.033105, 0.313989, 0.708376', PDO::PARAM_STR); // Default torso color
    $insertColors->bindValue(4, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default left arm color
    $insertColors->bindValue(5, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default right arm color
    $insertColors->bindValue(6, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default left leg color
    $insertColors->bindValue(7, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default right leg color
    $insertColors->execute();

    // Create default user data (wearing items)
    $insertWearingData = $db->prepare('INSERT INTO user_data (user, hat1, hat2, hat3, face, shirt, pants, gear) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $insertWearingData->bindValue(1, $id, PDO::PARAM_INT);
    $insertWearingData->bindValue(2, '0', PDO::PARAM_STR); // hat1
    $insertWearingData->bindValue(3, '0', PDO::PARAM_STR); // hat2
    $insertWearingData->bindValue(4, '0', PDO::PARAM_STR); // hat3
    $insertWearingData->bindValue(5, '0', PDO::PARAM_STR); // face
    $insertWearingData->bindValue(6, '0', PDO::PARAM_STR); // shirt
    $insertWearingData->bindValue(7, '0', PDO::PARAM_STR); // pants
    $insertWearingData->bindValue(8, '0', PDO::PARAM_STR); // gear
    $insertWearingData->execute();

    // Set default blurb
    $updateBlurb = $db->prepare('UPDATE users SET blurb = ? WHERE id = ?');
    $updateBlurb->bindValue(1, 'Welcome to Bricktaria!', PDO::PARAM_STR);
    $updateBlurb->bindValue(2, $id, PDO::PARAM_INT);
    $updateBlurb->execute();

    die("success");
}
}

















