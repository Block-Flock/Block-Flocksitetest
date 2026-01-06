<?php
$title = "Report Item";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
$submitted = false;
         
if(!$AUTH) {

    http_response_code(404);
    die;

}
if(!isset($_GET['id'])) {

   die('Please specify a valid item ID.');

}

if(!is_numeric($_GET['id'])) {

   die('Please specify a valid item ID.');

}

if(isset($_POST['action'])&& isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response']) ) {

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
   $reason = htmlspecialchars($_POST['reason']);
   if(!$AUTH){
      die('Login to report!');
   }
   if(empty($reason)){
      $err = 'Reason required!';
       } else if($check->hasProfanity($_POST['reason'])) {

        $err = "Our filter has detected profanity in your reason report. Please remove any profanity and try again";

    } else if($my->verified == 0) {

        $err = 'Please verify your account to report this item.';

    }
   $selectid = $db->prepare('SELECT store_items.*, users.* FROM store_items JOIN users ON users.id = store_items.creator WHERE store_items.id = ?');
   $selectid->bindValue(1, $_GET['id'], PDO::PARAM_INT);
   $selectid->execute();

$si = $selectid->fetch(PDO::FETCH_OBJ);

if(!isset($err)) {
   $insert = $db->prepare("INSERT INTO `reportsystem` (`reason`, `type`, `reporter`, `allegedOffender`, `username`) VALUES (?, ?, ?, ?, ?)");
      $insert->bindValue(1, $reason, PDO::PARAM_STR);
      $insert->bindValue(2, 2, PDO::PARAM_INT);
      $insert->bindValue(3, htmlentities($my->username), PDO::PARAM_STR);
      $insert->bindValue(4, $_GET['id'], PDO::PARAM_INT);
      $insert->bindValue(5, $si->name, PDO::PARAM_STR);
      $insert->execute();
      $submitted = true;
   }
}
$selectItems = $db->prepare('SELECT store_items.*, users.* FROM store_items JOIN users ON users.id = store_items.creator WHERE store_items.id = ?');
$selectItems->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$selectItems->execute();

if($selectItems->rowCount() == 0) {

  die('Item not found. Please specify a valid item ID.');

}

$si = $selectItems->fetch(PDO::FETCH_OBJ);
         ?>
      <section class="section">
<div class="page container">
<div class="columns">
<div class="column">
</div>
<div class="column is-10">
<div class="">
                 <?php if(isset($err)) { ?><p class="help is-danger"><?php echo htmlspecialchars($err); ?></p><?php } ?>
<?php if($submitted == false){ ?>
<form action="" method='POST'>
<div class='field'>
                            <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
                     </div>
   <div class='field'>
      <div class='control'>
         <div class='field'>
            <textarea type='text' name='reason' class='textarea' placeholder="Please type a valid reason"></textarea>         
         </div>
<div class='field'>
  <div class="h-captcha" data-sitekey="14cddd71-a534-4587-9e69-ce41e1b52f89"></div>
         <div class='field'>
            <input type='submit'  name='action' class='button is-info' value='Submit'/>
         </div>
      </div>
   </div>
</form>
<?php }else{ ?>
<p class="novello has-text-success is-size-7">Item Report Submitted!</p>
<?php } ?>
</div>
</div>
<div class="column">
</div>
<?php
$app->footer();
?>
