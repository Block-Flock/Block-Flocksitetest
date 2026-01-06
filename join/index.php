
<?php
$title = "Join";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(false);
if(!$AUTH && isset($_POST['username']) && isset($_POST['bdate'])&& isset($_POST['email']) & isset($_POST['password']) && isset($_POST['confpass'])  && isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response']) ) {
    
$data = array(
            'secret' => "0xcFd62e907D377Bd68c2c659d52a5c0F0275C56F2",
            'response' => $_POST['h-captcha-response']
        );
$verify = curl_init();
curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
curl_setopt($verify, CURLOPT_POST, true);
curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($verify);
$responseData = json_decode($response);


    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confpass = $_POST['confpass'];
    
    if(!$responseData->success) {
        
    $err = 'Invalid captcha submission, please try again.';
        
    }


    if(strlen($username) < 3 && !isset($err) || strlen($username) > 15 && !isset($err) || !preg_match('/^[a-zA-Z0-9][\w\.]+[a-zA-Z0-9]$/', $username) && !isset($err)) {

        $err = 'The username field should be in the range of 3-15 characters or Username contains characters that are not allowed.';

    }

    $takenUsername = $db->prepare('SELECT * FROM users WHERE username = ?');
    $takenUsername->bindValue(1, $username, PDO::PARAM_STR);
    $takenUsername->execute();

    if($takenUsername->rowCount() > 0 && !isset($err)) {

        $err = 'The username you selected has been taken.';

    }

    $check = new Check();

    if($check->hasProfanity($username) && !isset($err)) {

        $err = 'Your username contains profanity, please remove any profanity and try again.';

    }

    if(strlen($email) > 128 && !isset($err) || !filter_var($email, FILTER_VALIDATE_EMAIL) && !isset($err)) {
        
        $err = 'Email is not valid.';

    }

    $takenEmail = $db->prepare('SELECT * FROM users WHERE email = ?');
    $takenEmail->bindValue(1, $email, PDO::PARAM_STR);
    $takenEmail->execute();

    if($takenEmail->rowCount() > 0) {

        $err = 'The email you selected has been taken.';

    }

    if(strlen($password) < 8 && !isset($err)) {

        $err = 'Your password must be at least 8 characters long.';

    }

    if(!hash_equals($confpass, $password) && !isset($err)) {

        $err = 'Passwords do not match.';

    }

    if(!isset($err)) {

    $insert = $db->prepare('INSERT INTO users (username, `password`, email, ip, bday, bmonth, byear, expire, joined) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insert->bindValue(1, $username, PDO::PARAM_STR);
    $insert->bindValue(2, password_hash($password, PASSWORD_BCRYPT), PDO::PARAM_STR);
    $insert->bindValue(3, $email, PDO::PARAM_STR);
    $insert->bindValue(4, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
$insert->bindValue(5, substr($_POST['bdate'], 1, 1), PDO::PARAM_INT);

    $insert->bindValue(6, substr($_POST['bdate'], 3, 3), PDO::PARAM_INT);

    $insert->bindValue(7, substr($_POST['bdate'], 6, 7), PDO::PARAM_INT);
    $insert->bindValue(8, time(), PDO::PARAM_INT);
    $insert->bindValue(9, time(), PDO::PARAM_INT);
    $insert->execute();

    $uid = $db->lastInsertId();

    // Create default user colors with proper values
    $insertColors = $db->prepare('INSERT INTO user_colors (user, head, torso, left_arm, right_arm, left_leg, right_leg) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $insertColors->bindValue(1, $uid, PDO::PARAM_INT);
    $insertColors->bindValue(2, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default head color
    $insertColors->bindValue(3, '0.033105, 0.313989, 0.708376', PDO::PARAM_STR); // Default torso color
    $insertColors->bindValue(4, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default left arm color
    $insertColors->bindValue(5, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default right arm color
    $insertColors->bindValue(6, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default left leg color
    $insertColors->bindValue(7, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR); // Default right leg color
    $insertColors->execute();

    // Create default user data with proper values
    $insertWearingData = $db->prepare('INSERT INTO user_data (user, hat1, hat2, hat3, face, shirt, pants, gear) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $insertWearingData->bindValue(1, $uid, PDO::PARAM_INT);
    $insertWearingData->bindValue(2, '0', PDO::PARAM_STR); // hat1
    $insertWearingData->bindValue(3, '0', PDO::PARAM_STR); // hat2
    $insertWearingData->bindValue(4, '0', PDO::PARAM_STR); // hat3
    $insertWearingData->bindValue(5, '0', PDO::PARAM_STR); // face
    $insertWearingData->bindValue(6, '0', PDO::PARAM_STR); // shirt
    $insertWearingData->bindValue(7, '0', PDO::PARAM_STR); // pants
    $insertWearingData->bindValue(8, '0', PDO::PARAM_STR); // gear
    $insertWearingData->execute();

   $uinsert = $db->prepare("INSERT INTO `user_usernames` (`owner`, `name`) VALUES (?, ?)");

         $uinsert->bindValue(1, $uid, PDO::PARAM_INT);

         $uinsert->bindValue(2, $username, PDO::PARAM_STR);

         $uinsert->execute();
         
    // Set default blurb
    $updateBlurb = $db->prepare('UPDATE users SET blurb = ? WHERE id = ?');
    $updateBlurb->bindValue(1, 'Welcome to Bricktaria!', PDO::PARAM_STR);
    $updateBlurb->bindValue(2, $uid, PDO::PARAM_INT);
    $updateBlurb->execute();
    
    $_SESSION['id'] = $uid;
    $_SESSION['uagent'] = $_SERVER['HTTP_USER_AGENT'];

    header('Location: /dashboard');
    die();

    }

}
?>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
            </div>
            <div class="column is-6">
            <div class="box">
<?php if(isset($err)) { ?><div class="has-text-danger"><?php echo $err; ?></div><?php } ?>
<div class="novello-title is-size-4">Register</div>
<div class="novello-subtitle is-size-6">Enter the following account information to start your new adventure on Bricktaria</div>
 <form method="post" id="regform">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                    <div class='field'>
                    <label class="label">Username</label>
<div class="control has-icons-left">
                            <input type='text' name="username"  maxlength="15" autocomplete="username"  placeholder='Username ( 3 - 15 characters )' class='input' required>
<span class="icon is-small is-left"><i class="fas fa-user fa-lg"></i></span>
<p class="novello-text has-text-grey-light is-size-7">Your username must be at least 3 characters long. An inappropriate username will result into a termination of your account.</p>
                        </div>
</div>
                    <div class='field'>
                    <label class="label">Password</label>
                    <div class="control has-icons-left">
                            <input type='password' name="password"  maxlength="45" placeholder='Password ( minimum 5 characters )' class='input' autocomplete="off" required> 
                            <span class="icon is-small is-left"><i class="fas fa-lock fa-lg"></i></span>
<p class="novello-text has-text-grey-light is-size-7">Your password is hashed and safely secured. Do not use the same password as you use on other website incase of emergency.</p>
                    </div>
</div>
                <div class='field'>
                <label class="label">Password Confirmation</label>
                <div class="control has-icons-left">
                        <input type='password'  name="confpass" maxlength="45"  placeholder='Password Confirmation ( must be the same )'  class='input' autocomplete="off"required>
                        <span class="icon is-small is-left"><i class="fas fa-lock fa-lg"></i></span>
                    </div>
</div>
  <div class='field'>
                        <label class="label">E-mail address</label>
                        <div class="control has-icons-left">
                                <input type='text' name="email" maxlength="128" autocomplete="email" placeholder='E-mail address ( 2 - 128 characters )' class='input' required>
                                <span class="icon is-small is-left"><i class="fas fa-envelope fa-lg"></i></span>
<p class="novello-text has-text-grey-light is-size-7">This is used to verify or to help retrieve your account easily when an emergency occurs regarding to your account.</p>
                        </div>
</div>
 <div class='field'>
                        <label class="label">Date of Birth</label>
                        <div class="control has-icons-left">
<input class='input' name="bdate" type="date" autocomplete="off" required>
   <span class="icon is-small is-left"><i class="fas fa-calendar fa-lg"></i></span>
</div>
</div>
<div class="h-captcha" data-sitekey="14cddd71-a534-4587-9e69-ce41e1b52f89"></div>
                <div class="field">
                <button class='button is-info'><b><i class="fas fa-user-plus"></i> Register</b></button>
</form>
</div>
               </div>
           </div>
           <div class="column">

           </div>
       </div>
    </div>
</section>
<?php
$app->footer();
?>










