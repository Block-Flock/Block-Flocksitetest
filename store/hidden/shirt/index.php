<?php
$title = 'Create Shirt';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {

    header('Location: /user/dashboard');
    die;

}

if(isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_FILES['image']['name']) && $AUTH) {

    $name = $_POST['name'];
    $description = $_POST['description'];

    $check = new Check();

    $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => '6LfJr3kdAAAAANG1BO4HzIOTVYKlYu3cucWrvEQU',
            'response' => $_POST["g-recaptcha-response"]
        );
       $options = array(
           'http' => array (
               'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
                           "Content-Length: ".strlen(http_build_query($data))."\r\n".
                           "User-Agent:MyAgent/1.0\r\n",
               'method' => 'POST',
               'content' => http_build_query($data),
            )
        );
        $context  = stream_context_create($options);
        $verify = file_get_contents($url, false, $context);
        $captcha_success=json_decode($verify);
        if ($captcha_success->success==false) {
           
           $err = 'Recaptcha validation failed.';
        }
    if(!$AUTH) {//$selectItem->rowCount() >= 1) {
        $err = "Invalid Session";

   } else if(!is_numeric($_POST['price']) || $_POST['price'] < -1) {
        $err = "Price is Invalid";

   } else if(strlen(str_replace(' ', '', $_POST['name'])) < 3) {
        $err =  "Your shirt name must be at least 3 characters.";

    } else if(strlen($_POST['name']) > 250) {
        $err =  "Your shirt name must be less than 250 characters.";

    } else if(strlen(str_replace(' ', '', $_POST['description'])) < 3) {
        $err =  "Your shirt description must be above 3 characters.";

    } else if(strlen($_POST['description']) > 250) {
        $err =  "Your shirt description must be less than 250 characters.";

    } else if(count(preg_split("/\n|\r/", $description)) > 9){
        $err ="You cannot have more than 5 lines/rows in your description!";

    } else if($check->hasProfanity($_POST['name'])) {
        $err = "Our filter has detected profanity in your shirt name. Please remove any profanity and try again!";

    } else if($check->hasProfanity($_POST['description'])) {
        $err = "Our filter has detected profanity in your shirt description. Please remove any profanity and try again!";

    } else if(empty($_FILES['image']['name'])) {
        $err = "You need to upload a image.";

    } else if(!getimagesize($_FILES['image']['tmp_name'])) {
        $err = "Invalid image.";

    } else if($_FILES['image']['size'] > 1000000) {
        $err = "Your image must weigh less than 1MB.";

    } else if(!isset($err)) {

        $Hash = bin2hex(random_bytes(16));

        $insert = $db->prepare('INSERT INTO store_items (`name`, about, creator, `type`, price, offsale, moderation, `image`, timeupdated, timecreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->bindValue(1, $name, PDO::PARAM_STR);
        $insert->bindValue(2, $description, PDO::PARAM_STR);
        $insert->bindValue(3, $_SESSION['id'], PDO::PARAM_INT);
        $insert->bindValue(4, '3', PDO::PARAM_INT);
        $insert->bindValue(5, $_POST['price'], PDO::PARAM_INT);
        if($_POST['price'] == -1) {

            $insert->bindValue(6, '1', PDO::PARAM_INT);

        } else if($_POST['price'] > -1) {

            $insert->bindValue(6, '0', PDO::PARAM_INT);

        }
        $insert->bindValue(7, '0', PDO::PARAM_STR);
        $insert->bindValue(8, $Hash, PDO::PARAM_STR);
        $insert->bindValue(9, time(), PDO::PARAM_INT);
        $insert->bindValue(10, time(), PDO::PARAM_INT);
        $insert->execute();

        $id = $db->lastInsertId();

        $invinsert = $db->prepare('INSERT INTO user_inventory (user, item, timebought) VALUES (?, ?, ?)');
        $invinsert->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $invinsert->bindValue(2, $id, PDO::PARAM_INT);
        $invinsert->bindValue(3, time(), PDO::PARAM_INT);
        $invinsert->execute();

        $ownerUpdate = $db->prepare('UPDATE store_items SET owners = owners + 1 WHERE id = ?');
        $ownerUpdate->bindValue(1, $id, PDO::PARAM_INT);
        $ownerUpdate->execute();

        // Define correct rendering paths
        $storeImagePath = $_SERVER['DOCUMENT_ROOT'].'/assets/images/store/'.$Hash.'.png';
        $renderShirtDir = $_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/shirt/'.$id;
        $renderShirtPath = $renderShirtDir.'/'.$id.'.png';

        if(move_uploaded_file($_FILES['image']['tmp_name'], $storeImagePath)) {
            if (!file_exists($renderShirtDir)) {
                mkdir($renderShirtDir, 0777, true);
            }
            if(copy($storeImagePath, $renderShirtPath)) {
                header('Location: /store/view/?id='.$id);
                die;
            } else {
                $err = "Could not setup rendering files.";
            }
        } else {
            $err = "Could not upload shirt image.";
        }



}
}
?>
<script>
function change(file) {
    $("#text-change").text(file.files[0].name);
}
</script>
<section class="section">
<div class="container">
<div class="columns">
<div class="column is-4"></div>
<div class="column is-5">
<div class="box">
<?php if(isset($err)) { ?><p class="help is-danger"><?php echo htmlspecialchars($err); ?></p><?php } ?>
<form method="post" enctype="multipart/form-data">
<div class='field'>
                            <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
                     </div>
<div class="field">
<input class="input" type="text" name="name" maxlength="250" placeholder="Name" required></input>
</div>
<div class="field">
<textarea class="textarea" name="description" maxlength="250" placeholder="Description" required></textarea>
</div>
<div class="field">
<p class="control has-icons-left has-icons-right">
<input class="input" type="number" name="price" placeholder="Price" required></input>
<span class="icon is-small is-left">
      <i class="fas fa-coins has-text-warning"></i>
    </span>
</p>
</div>
<div class="field" style="width: 100%; justify-content: center;">
<label class="upload control" style="width: 100%;">
<div class="upload-draggable is-primary" style="width: 100%;">
<section class="section">
<div class="content has-text-centered">
<p><span class="icon is-large"><i class="mdi mdi-upload mdi-48px"></i></span></p>
<p id="text-change">Drop your files here or click to upload</p>
</div>
</section>
</div>
<input type="file" name="image" onchange="change(this);">
</label>
</div>
<div class='field'>
                        <div class="g-recaptcha" data-sitekey="6LfJr3kdAAAAADPnguPlyVIC9tSt8cujiEwDkOxn"></div>
                        </div>
<p class="">Don't have Shirt Template? Click <a href="https://www.bricktaria.com/assets/images/shirtTemplate.png">here</a></p>
<div class="field">
<button type="submit" class="button is-primary">Create Shirt</button>
</div>
</form>
</div>
</div>
<div class="column is-5">
</div>
</div>
</div>
</div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php'); ?>