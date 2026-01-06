<?php
$title = "Redeem";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');


if (!$AUTH) {
    
    http_response_code(404);
    die;
    
}

if (isset($_POST['code']) && $AUTH && is_string($_POST['code']) && isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response']) ) {

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
    $codeExists = $db->prepare('SELECT * FROM promocodes WHERE code = ?');
    $codeExists->bindValue(1, $_POST['code'], PDO::PARAM_STR);
    $codeExists->execute();
    
    if ($codeExists->rowCount() == 1) {
        $codeRow = $db->prepare('SELECT promocodes.* FROM promocodes WHERE code = ?');
        $codeRow->bindValue(1, $_POST['code'], PDO::PARAM_STR);
        $codeRow->execute();
        $cR = $codeRow->fetch(PDO::FETCH_OBJ);
        if ($cR->expired != 1) {
          if ((strtotime(strval($cR->expirationdate))-strtotime(strval(date('Y-m-d H:i:s')))) / 86400 > 0){
            if ($cR->uses == -1) {
                $codeRedeemed = $db->prepare('SELECT * FROM user_promocodes WHERE code = ? AND user = ?');
                $codeRedeemed->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                $codeRedeemed->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
                $codeRedeemed->execute();
                
                if ($codeRedeemed->rowCount() == 0) {
                    
                    $redeemCode = $db->prepare('INSERT INTO user_promocodes (code, user) VALUES (?, ?)');
                    $redeemCode->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                    $redeemCode->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
                    $redeemCode->execute();
                    
                    $updateUsed = $db->prepare('UPDATE promocodes SET used = used + 1 WHERE code = ?');
                    $updateUsed->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                    $updateUsed->execute();
                    
                    if(strpos($cR->itemid, "addcoin") !== false){

                        $addCoin = $db->prepare('UPDATE users SET Currency = Currency + '.str_split($cR->itemid, 7)[1].' WHERE id = ?');
                        $addCoin->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
                        $addCoin->execute();

                    }else{

                        $giveItem = $db->prepare('INSERT INTO user_inventory (user, item, timebought) VALUES (?, ?, ?)');
                        $giveItem->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
                        $giveItem->bindValue(2, intval($cR->itemid));
                        $giveItem->bindValue(3, time(), PDO::PARAM_INT);
                        $giveItem->execute();

                        $updateOwners = $db->prepare('UPDATE store_items SET owners = owners + 1 WHERE id = ?');
                        $updateOwners->bindValue(1, intval($cR->itemid), PDO::PARAM_INT);
                        $updateOwners->execute();

                    }
                    
                    echo "<script> 
          $(document).ready(function() {
          document.getElementById('promocode_msg').className = 'novello-text has-text-success is-size-7'
          document.getElementById('promocode_msg').innerText = 'Code successfully redeemed on this account!'
          document.getElementById('promocode_msg').removeAttribute('hidden')
          })
          </script>";
                    
                } else if ($codeRedeemed->rowCount() > 0) {
                    
                    echo "<script> 
          $(document).ready(function() {
          document.getElementById('promocode_msg').className = 'novello-text has-text-warning is-size-7'
          document.getElementById('promocode_msg').innerText = 'Code has already been redeemed on this account!'
          document.getElementById('promocode_msg').removeAttribute('hidden')
          })
          </script>";
                    
                }
            } else if ($cR->uses == 0) {
                $updateExpired = $db->prepare('UPDATE promocodes SET expired = 1 WHERE code = ?');
                $updateExpired->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                $updateExpired->execute();
                echo "<script> 
    $(document).ready(function() {
    document.getElementById('promocode_msg').className = 'novello-text has-text-danger is-size-7'
    document.getElementById('promocode_msg').innerText = 'Code has expired!'
    document.getElementById('promocode_msg').removeAttribute('hidden')
    })
    </script>";
            } else if ($cR->uses >= 1) {
                $codeRedeemed = $db->prepare('SELECT * FROM user_promocodes WHERE code = ? AND user = ?');
                $codeRedeemed->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                $codeRedeemed->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
                $codeRedeemed->execute();
                
                if ($codeRedeemed->rowCount() == 0) {
                    
                    $redeemCode = $db->prepare('INSERT INTO user_promocodes (code, user) VALUES (?, ?)');
                    $redeemCode->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                    $redeemCode->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
                    $redeemCode->execute();
                    
                    $updateUsed = $db->prepare('UPDATE promocodes SET used = used + 1 WHERE code = ?');
                    $updateUsed->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                    $updateUsed->execute();
                    
                    $updateUses = $db->prepare('UPDATE promocodes SET uses = uses - 1 WHERE code = ?');
                    $updateUses->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                    $updateUses->execute();
                    
                    if(strpos($cR->itemid, "addcoin") !== false){

                        $addCoin = $db->prepare('UPDATE users SET Currency = Currency + '.str_split($cR->itemid, 7)[1].' WHERE id = ?');
                        $addCoin->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
                        $addCoin->execute();

                    }else{

                        $giveItem = $db->prepare('INSERT INTO user_inventory (user, item, timebought) VALUES (?, ?, ?)');
                        $giveItem->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
                        $giveItem->bindValue(2, intval($cR->itemid));
                        $giveItem->bindValue(3, time(), PDO::PARAM_INT);
                        $giveItem->execute();

                        $updateOwners = $db->prepare('UPDATE store_items SET owners = owners + 1 WHERE id = ?');
                        $updateOwners->bindValue(1, intval($cR->itemid), PDO::PARAM_INT);
                        $updateOwners->execute();
                    }
                    
                    echo "<script> 
        $(document).ready(function() {
        document.getElementById('promocode_msg').className = 'novello-text has-text-success is-size-7'
        document.getElementById('promocode_msg').innerText = 'Code successfully redeemed on this account!'
        document.getElementById('promocode_msg').removeAttribute('hidden')
        })
        </script>";
                    
                } else if ($codeRedeemed->rowCount() > 0) {
                    
                    echo "<script> 
          $(document).ready(function() {
          document.getElementById('promocode_msg').className = 'novello-text has-text-warning is-size-7'
          document.getElementById('promocode_msg').innerText = 'Code has already been redeemed on this account!'
          document.getElementById('promocode_msg').removeAttribute('hidden')
          })
          </script>";
                    
                }
            }else{
              $updateExpired = $db->prepare('UPDATE promocodes SET expired = 1 WHERE code = ?');
                $updateExpired->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                $updateExpired->execute();
                echo "<script> 
    $(document).ready(function() {
    document.getElementById('promocode_msg').className = 'novello-text has-text-danger is-size-7'
    document.getElementById('promocode_msg').innerText = 'Code has expired!'
    document.getElementById('promocode_msg').removeAttribute('hidden')
    })
    </script>";
            }
          }else{
            $updateExpired = $db->prepare('UPDATE promocodes SET expired = 1 WHERE code = ?');
                $updateExpired->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                $updateExpired->execute();
                echo "<script> 
    $(document).ready(function() {
    document.getElementById('promocode_msg').className = 'novello-text has-text-danger is-size-7'
    document.getElementById('promocode_msg').innerText = 'Code has expired!'
    document.getElementById('promocode_msg').removeAttribute('hidden')
    })
    </script>";
        }
        }else{
            $updateExpired = $db->prepare('UPDATE promocodes SET expired = 1 WHERE code = ?');
                $updateExpired->bindValue(1, $_POST['code'], PDO::PARAM_STR);
                $updateExpired->execute();
                echo "<script> 
    $(document).ready(function() {
    document.getElementById('promocode_msg').className = 'novello-text has-text-danger is-size-7'
    document.getElementById('promocode_msg').innerText = 'Code has expired!'
    document.getElementById('promocode_msg').removeAttribute('hidden')
    })
    </script>";
        }
    }else if ($codeExists->rowCount() != 1) {
            
            echo "<script> 
    $(document).ready(function() {
    document.getElementById('promocode_msg').className = 'novello-text has-text-danger is-size-7'
    document.getElementById('promocode_msg').innerText = 'Code is invalid!'
    document.getElementById('promocode_msg').removeAttribute('hidden')
    })
    </script>";
            
    }
} //else{

//echo "<script>
//$(document).ready(function() {
//document.getElementById('promocode_msg').className = 'novello-text has-text-danger is-size-7'
//document.getElementById('promocode_msg').innerText = 'Must be logged into valid account to redeem codes!'
//document.getElementById('promocode_msg').removeAttribute('hidden')
//})
//    </script>";

//}

?>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
            </div>
            <div class="column is-6">
            <div class="box">
<form action='/redeem/' method='post'>
                    <div class='field'>                                                      <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
                     </div>
                    <div class='field'>
                        <label class='label'>Redeem</label>
                        <div class="field has-addons">
                        <div class="control is-fullwidth">
  <input type='text' name="code" placeholder='Enter Promocodes' class='input'>
  </div>
<div class="control">
  <button id="RedeemButton" class='button is-info'>Redeem</button></div>
</div>
<div class='field'>
  <div class="h-captcha" data-sitekey="14cddd71-a534-4587-9e69-ce41e1b52f89"></div>
</div>
  <p hidden id="promocode_msg" class="novello-text has-text-danger is-size-7">If you are seeing this message, something has gone wrong.</p>
</div>
</form>
</section>
<?php
$app->footer();
?>











