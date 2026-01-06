<?php
$title = "Change Username | Settings";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
$app->checkAuth($AUTH, true);
if(isset($_POST['curpass']) && isset($_POST['username']) && $AUTH) {

            $check = new Check();
            $id = $_SESSION['id'];
            $username = htmlspecialchars($_POST['username']);

            if($check->hasProfanity($_POST['username'])){
               $err = "Our filter has detected inappropriate content in your username submission. Please remove it and try again!";

           }  if(!password_verify($_POST['curpass'], $my->password)) {



        $err = "Your current password does not match your account's password";



    } 
            if(strlen($username) < 3 || strlen($username) > 50) {

              $err = "Username must be 3 to 50 characters long!";
            }

            if(!preg_match("/^[A-Za-z][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/", $username)){
        $err = "Invalid username!";
     		}

            $checkUser1 = $db->prepare('SELECT * FROM `users` WHERE `username`=:userch');
            $checkUser1->bindParam(":userch", $username, PDO::PARAM_STR);
            $checkUser1->execute();

            $checkUser2 = $db->prepare('SELECT * FROM `user_usernames` WHERE `name`=:userch');
            $checkUser2->bindParam(":userch", $username, PDO::PARAM_STR);
            $checkUser2->execute();

            if($checkUser1->rowCount() >= 1){
               $err = "Username taken!";
            }

            if($checkUser2->rowCount() >= 1){
                $checkUser3 = $db->prepare('SELECT * FROM `user_usernames` WHERE `name`=? AND `id`=?');
                $checkUser3->bindValue(1, $username, PDO::PARAM_STR);
                $checkUser3->bindValue(2, $id, PDO::PARAM_STR);
                $checkUser3->execute();

                if($checkUser3->rowCount() != 1){
               $err = "Username taken!";
              }else if($my->Currency < 500){
                   $err = "You must have at least 500 Coins to afford to switch to a previous username!";
                }
            }else if($my->Currency < 500){
             $err = "You must have at least 500 Coins to afford username!";
            }

            if(!isset($err)) {

                $checkUser4 = $db->prepare('SELECT * FROM `users` WHERE `username`=? AND `id`=?');
                $checkUser4->bindValue(1, $username, PDO::PARAM_STR);
                $checkUser4->bindValue(2, $id, PDO::PARAM_STR);
                $checkUser4->execute();

                if(!$checkUser4->rowCount() >= 1){
                    $cupdate = $db->prepare('UPDATE users SET Currency = Currency - 500 WHERE id = ?');
                    $cupdate->bindValue(1, $id, PDO::PARAM_INT);
                    $cupdate->execute();
                    $uinsert = $db->prepare("INSERT INTO `user_usernames` (`owner`, `name`) VALUES (?, ?)");
                    $uinsert->bindValue(1, $id, PDO::PARAM_INT);
                    $uinsert->bindValue(2, $username, PDO::PARAM_STR);
                    $uinsert->execute();

                }else if($checkUser4->rowCount() >=1){
                    $cupdate = $db->prepare('UPDATE users SET Currency = Currency - 500 WHERE id = ?');
                    $cupdate->bindValue(1, $id, PDO::PARAM_INT);
                    $cupdate->execute();
                }

                $update = $db->prepare('UPDATE users SET `username` = ? WHERE id = ?');
                $update->bindValue(1, $username, PDO::PARAM_STR);
                $update->bindValue(2, $id, PDO::PARAM_INT);
                $update->execute();
                header("Refresh:0");

            }

        }
?>
<section class="section">
    <div class="container">
        <div class="columns"> 
            <div class="column">
                <aside class="menu">
                    <div class="">
                    <p class="menu-label">General</p>
                    <ul class="menu-list">
                        <li><a id="information_btn" href="/user/settings">Information</a></li>
                        <li><a id="profile_btn" href="/user/settings/general/profile">Profile</a></li>
                    </ul>
<ul class="menu-list">
 <p class="menu-label">Account</p>
                <li><a href="/user/settings/account/username" class="is-active">Change Username</a></li>
                <li><a href="/user/settings/account/password">Change Password</a></li>
                <li><a href="/user/settings/account/email">Change Email</a></li>
</ul>
                    <p class="menu-label">Other</p>
                    <ul class="menu-list">
                        <li><a href="#">Billing</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">
                <div class="box">
<?php if(isset($err)) { ?><div class="has-text-danger"><?php echo $err; ?></div><?php } ?>
  <h2 class="is-size-4">Change username</h2>
                    <p class="has-text-grey-light">If you wish to change your username, complete the form with the
                        required information. Upon changing your username, your old username will be available for
                        everyone to take effective. Changing your username will cost you 500 coins.</p>
<form method="post">
                    <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                    <div class="field">
                        <label class="label">Enter your new username</label>
                        <div class="control">
                            <input class="input" name='username' type="text"
                                placeholder="Please enter your new username">
                        </div>
                    <div class="field">
                        <label class="label">Enter your current password</label>
                        <div class="control">
                            <input class="input" name='curpass' type="password"
                                placeholder="Please enter your current password">
                        </div>
                    </div>
                    <button class="button is-info">Submit</button>
                </div>
</form>
            </div>
        </div>
    </div>
</section>
<?php
$app->footer();
?>

























