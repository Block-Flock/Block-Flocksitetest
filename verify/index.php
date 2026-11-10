<?php
$ignore = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/private/init.inc.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/private/mailHandler.php");

if(!$AUTH)
{
  die(header("location: /log-in"));
}

if($my->verified == 1 || $my->email_verified == 1)
{
  require($_SERVER['DOCUMENT_ROOT'] . "/errors/404.php");
  die();
}

// Check if user has a verification code
if(empty($my->emailVerifyCode)) {
    $token = bin2hex(random_bytes(32));
    $update = $db->prepare("UPDATE users SET emailVerifyCode = ? WHERE id = ?");
    $update->bindValue(1, $token, PDO::PARAM_STR);
    $update->bindValue(2, $my->id, PDO::PARAM_INT);
    $update->execute();
    
    // Update local object
    $my->emailVerifyCode = $token;
    
    // Send email
    $verifyLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/api/account/verifyBackend.php?code=" . $token;
    $message = "<center>";
    $message .= "<h1>Verify your account</h1><br>";
    $message .= "<a class='button success' href='" . $verifyLink . "'>Click here to verify</a>";
    $message .= "<br><br>Or copy this code: <b>" . $token . "</b>";
    $message .= "</center>";
    
    mailHandler::sendMail($my->email, "Verify your account", $message);
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
                <form action="/api/account/verifyBackend.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                    <div class='field'>
                        <label class='label'>Verification Code</label>
                        <p> An e-mail has been sent to <b><?php echo htmlspecialchars($my->email);?></b>, the e-mail contains a verification code. Enter that code to activate your account.</p>
                        <input type='text' name="code" class='input' placeholder="Enter verification code">
                    </div>
                    <div class='field'>
                        <button type='submit' name="verify" class='button is-info'>Submit</button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/private/footer.inc.php"); ?>
