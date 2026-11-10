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
<section class="section auth-container">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-5-tablet is-4-desktop">
                <div class="glass-box">
                    <div class="auth-header">
                        <h1 class="title is-4">Login</h1>
                        <p class="subtitle is-6">Continue where you left off</p>
                    </div>

                    <?php if(isset($err)) { ?>
                        <div class="notification is-danger is-light mb-4 p-3 is-size-7">
                            <?php echo $err; ?>
                        </div>
                    <?php } ?>

                    <form method="post">
                        <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        
                        <div class='field'>
                            <label class='label'>Username</label>
                            <div class="control has-icons-left">
                                <input type='text' name="bctdkio8" class='input' placeholder="Enter your username" required>
                                <span class="icon is-small is-left"><i class="fas fa-user"></i></span>
                            </div>
                        </div>

                        <div class='field'>
                            <label class='label'>Password</label>
                            <div class="control has-icons-left">
                                <input type='password' name="8q5EonNy" autocomplete="current-password" class='input' placeholder="Enter your password" required>
                                <span class="icon is-small is-left"><i class="fas fa-unlock-alt"></i></span>
                            </div>
                        </div>

                        <div class='field mt-5'>
                            <button class='button is-primary is-fullwidth is-medium'><b>Login</b></button>                   
                        </div>
                    </form>

                    <p class="has-text-centered is-size-7 mt-4">
                        Forgot your password? <a href="/reset/" class="has-text-primary">Click here to reset</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
      <?php
$app->footer();
?>