<?php
$title = "Log-in";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(false);
if(isset($_POST['bctdkio8']) && isset($_POST['8q5EonNy'])) {

   $appname = strtolower($_POST['bctdkio8']);
   $password = $_POST['8q5EonNy'];

   if(empty($appname) || empty($password) && !isset($err)) {

       $err = 'Please enter a username and a password.';

   }

   $selectuser = $db->prepare('SELECT * FROM users WHERE username = ?');
   $selectuser->bindValue(1, $appname, PDO::PARAM_STR);
   $selectuser->execute();

   $su = $selectuser->fetch(PDO::FETCH_OBJ);

   if($selectuser->rowCount() == 0 && !isset($err)) {

       $err = 'Incorrect username or password.';

   } else if(!password_verify($password, $su->password) && !isset($err)) {

       $err = 'Incorrect username or password.';

   } else if(password_verify($password, $su->password) && !isset($err)) {

       $_SESSION['id'] = $su->id;
       $_SESSION['uagent'] = $_SERVER['HTTP_USER_AGENT'];
       // Log the login
       $InsertUserActionLog = $db->prepare("INSERT INTO `useractionlogs` (`UserID`, `Action`, `TimeLog`, `IP`) VALUES(?, ?, ?, ?)");
       $InsertUserActionLog->bindValue(1, $su->id, PDO::PARAM_STR);
       $InsertUserActionLog->bindValue(2, 'Logged Into Their Account.', PDO::PARAM_STR);
       $InsertUserActionLog->bindValue(3, time(), PDO::PARAM_STR);
       $InsertUserActionLog->bindValue(4, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
       $InsertUserActionLog->execute();

       echo "Login successful. Redirecting to dashboard...";
        sleep(2);
        header('Location: /dashboard'); // or whatever is valid
        exit;


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
<div class="novello-title is-size-4">Login</div>
<div class="novello-subtitle is-size-6">Continue where you left off</div>
<form method="post">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        <div class='field'>
                            <label class='label'>Username</label>
<div class="control has-icons-left">
                                <input type='text' name="bctdkio8"  class='input' placeholder="Enter your username" required>
<span class="icon is-small is-left"><i class="fas fa-user fa-lg"></i></span>
</div>

                        </div>
                        <div class='field'>
                            <label class='label'>Password</label>
<div class="control has-icons-left">
                                <input type='password' name="8q5EonNy" autocomplete="current-password" class='input' placeholder="Enter your password" required>
<span class="icon is-small is-left"><i class="fas fa-unlock-alt fa-lg"></i></span>
</div>
                        </div>
                        <div class='field'>
    <button class='button is-info'><b>Login</b></button>                   
  </div>
                    </div>
<p class="novello-text has-text-centered">Forgot your password? <a href="/reset/">Click here to reset</a></p>
                 </div>
                 <div class='column is-4'>
                 </div>
             </div>
        </div>
    </div>
</section>
      <?php
$app->footer();
?>