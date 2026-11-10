<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$ignore = true;
require($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/SMTP.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/Exception.php';
if(!$AUTH)
{
  die(header("location: /log-in"));
}
if($my->email_verified == "1")
{
  require($_SERVER['DOCUMENT_ROOT'] . "/errors/404.php");
  die();
}
if(!$verify)
{
  $verify = (array) $db->insert("email_verified", [
    "id" => $my->id,
    "uniq" => $db->uuid()
  ]);
  $reconfig = (array) $verify[0];
  $reconfig['id'] = $verify['id'];
  $verify = (object) $reconfig;
  $message = "<center>";
  $message .= "<img src='https://www.vertineer.com/vertineernew.png' alt='Vertineer logo'><br>";
  $message .= "<h1>Verify your Vertineer! account.</h1><br>";
  $message .= "<a class='button success' href='https://www.vertineer.com/verify/?token=$verify->uniq'>Click here to verify</a>";
  $message .= "</center>";
  $message .= "<center>Can't click the button? Here's the URL: https://www.vertineer.com/verify/?token=$verify->uniq<br>";
  $message .= "Is this not your email? Change it on the website when verifying your account, or change it in your settings.<br>";
  $message .= "<center><a href='https://www.vertineer.com/'>Landing</a> | <a href='https://www.vertineer.com/forum'>Forum</a> | <a href='https://www.vertineer.com/shop'>Shop</a> | <a href='https://www.vertineer.com/privacy/'>Privacy</a></center>";
  //$db->mail($my->email, "Verify your Vertineer! account.", $message);
  $mail = new PHPMailer\PHPMailer\PHPMailer(true);
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->SMTPAuth = true;
  $mail->Username = 'vertineerbusiness';
  $mail->Password = 'studzyisaweeb';
  $mail->SMTPSecure = 'ssl';
  $mail->Port = 465;
  $mail->setFrom('vertineerbusiness@gmail.com', 'Vertineer Game');
  $mail->addReplyTo('vertineerbusiness@gmail.com', 'Vertineer Game');
  $mail->addAddress($my->email, $my->username);
  $mail->Subject = "Verify your Vertineer account.";
  $mail->isHTML(true);
  $mail->Body = "<a class='button success' href='https://www.vertineer.com/verify/?token=$verify->uniq'>Click here to verify</a>";
  if($mail->send()) {

  } else {
    die("Message could not be sent, error: $mail->ErrorInfo");
  }
}
require($_SERVER['DOCUMENT_ROOT'] . "/private/header.inc.php");
?>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
            </div>
            <div class="column is-6">
                <div class="box">
                    <p>An e-mail has been sent to <b><?php echo $my->email; ?></b>, the e-mail contains a verification link. Click that link to activate your account.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        if (token) {
            const formData = new FormData();
            formData.append('token', token);
            formData.append('csrf', '<?= $_SESSION['CSRF-TOKEN']; ?>');

            fetch('/api/account/verifyBackend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.querySelector('.box').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                document.querySelector('.box').innerHTML = '<p>An error occurred during verification.</p>';
            });
        }
    });
</script>
