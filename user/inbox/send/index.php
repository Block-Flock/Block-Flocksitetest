<?php
$title = "Send Message";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

   http_response_code(404);
   die;

}
error_reporting(E_ALL & ~E_NOTICE);

if(!$_GET['context']){
 $context = htmlspecialchars('');
}else{
 $context = htmlspecialchars($_GET['context']);
}

if(!isset($_GET['id'])) {

   header('Location: /');
   die;

}
$userInfo = $db->prepare('SELECT * FROM users WHERE id = ?');
$userInfo->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$userInfo->execute();
$uI = $userInfo->fetch(PDO::FETCH_OBJ);

if(isset($_POST['action']) && isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response']) ) {

    $check = new Check();

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

    if(!$responseData->success) {
        
    $err = 'Invalid captcha submission, please try again.';
        
    }

  $id = htmlspecialchars($_GET['id']);
  $subject = htmlspecialchars($_POST['subject']);
  $content = htmlspecialchars($_POST['content']);
  if(!$AUTH){
     die("Login to send message!");
  } else if(empty($id)){
     $err = 'UserID required!';
  } else if(empty($content)){
     $err = 'Message Content required!';
  } else if(strlen($content) > 4000){
     $err = 'Message Content must not exceed 4000 characters';
  } else if(strlen($subject) > 120){
     $err = 'Subject Line must contain less than 120 characters!';
  } else if($my->flood >= time()) {
     $err = 'Please wait another '.($my->flood-time()).' seconds before sending another message.';

    } else if($check->hasProfanity($_POST['subject'])) {

        $err = "Our filter has detected profanity in your subject. Please remove any profanity and try again";

        } else if($check->hasProfanity($_POST['content'])) {

        $err = "Our filter has detected profanity in your content. Please remove any profanity and try again";

    } else if($my->verified == 0) {

       $err = 'Please verify your account to send a message.';

   }

  if(!isset($err)) {
     $insert = $db->prepare('INSERT INTO user_inboxes (sender, recipient, subject, content) VALUES (?, ?, ?, ?)');
     $insert->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
     $insert->bindValue(2, $id, PDO::PARAM_INT);
     $insert->bindValue(3, $subject, PDO::PARAM_STR);
     $insert->bindValue(4, $content, PDO::PARAM_STR);
     $insert->execute();

     $flood = $db->prepare('UPDATE users SET flood = ? WHERE id = ?');
     $flood->bindValue(1, time()+30, PDO::PARAM_STR);
     $flood->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
     $flood->execute();
     header('Location: ../view/'.$_GET['id']);
  }
}
        ?>
       <section class="section">
<div class="container">
       <div class="columns">
           <div class="column">
           </div>
           <div class="column is-10">
           <div class="">
<div class="novello-title is-size-4" >Message to <?=$uI->username ?></div>
<?php if(isset($err)) { ?><p class="help is-danger"><?php echo htmlspecialchars($err); ?></p><?php } ?>
<form method='post'>
<input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
   <div class='field'>
      <div class='control'>
         <div class='field'>
            <label class='label'>Subject</label>
            <input type='text' name='subject' class='input' maxlength="120" placeholder="Place a subject." value="<?php if($context){ echo $context; } ?>">
            <div class='field'>
            <label class='label'>Content</label>
            <textarea type='text' name='content' class='textarea' placeholder=""></textarea>
         </div>
            <div class='field'>
  <div class="h-captcha" data-sitekey="14cddd71-a534-4587-9e69-ce41e1b52f89"></div>
         <div class='field'>
            <button type='submit'  name='action' class='button is-info'><b>Submit</b></button>
            <a class="button is-dark" onClick="window.history.back();"><b>Previous page</b></a>
         </div>
      </div>
   </div>
</form>
</section>
<?php
$app->footer();
?>








