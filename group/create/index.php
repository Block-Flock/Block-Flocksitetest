<?php
$title = 'Create Group';
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    header('Location: /');
    die();

}

if(isset($_POST['name']) && isset($_POST['description']) && isset($_FILES['logo']['name']) && $AUTH && isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response']) ) {

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

    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);

    $groupExists = $db->prepare("SELECT * FROM user_groups WHERE `name` = ?");
    $groupExists->bindValue(1, $name, PDO::PARAM_STR);
    $groupExists->execute();

    if($groupExists->rowCount() > 0){

        $err = "Group name taken.";

    }else if($my->Currency < 50) {

        $err = "You need 50 coins to create a group.";

    } else if(strlen(str_replace(' ', '', $_POST['name'])) < 3) {

        $err = "Your group name must be above 3 characters.";

    } else if(strlen($_POST['name']) > 50) {

        $err = "Your group name must be less than 50 characters.";

    } else if(strlen(str_replace(' ', '', $_POST['description'])) < 3) {

        $err = "Your group description must be above 3 characters.";

    } else if(strlen($_POST['description']) > 600) {

        $err = "Your group description must be less than 600 characters.";

    } else if($check->hasProfanity($_POST['name'])) {

        $err = "Our filter has detected profanity in your group name. Please remove any profanity and try again";

    } else if($check->hasProfanity($_POST['description'])) {

        $err = "Our filter has detected profanity in your group description. Please remove any profanity and try again";

    } else if(empty($_FILES['logo']['name'])) {

        $err = "You need to upload a logo.";

    } else if(!getimagesize($_FILES['logo']['tmp_name'])) {

        $err = "Invalid logo.";

    } else if(exif_imagetype($_FILES['logo']['tmp_name']) != 3) {

        $err = "Your logo must be a PNG.";

    } else if($_FILES['logo']['size'] > 1000000) {

        $err = "Your image must weigh less than 1MB.";

  } else if($my->verified == 0) {

        $err = 'Please verify your account to groups.';

    } else if(!isset($err)) {

        error_log("GROUP_DEBUG: Starting group creation for user " . $_SESSION['id']);
        $logoHash = bin2hex(random_bytes(16));

        $updateCur = $db->prepare('UPDATE users SET Currency = Currency - 50 WHERE id = ?');
        $updateCur->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $updateCur->execute();

        $insert = $db->prepare('INSERT INTO user_groups (`name`, `description`, members, creator, logo, timecreated) VALUES (?, ?, ?, ?, ?, ?)');
        $insert->bindValue(1, $name, PDO::PARAM_STR);
        $insert->bindValue(2, $description, PDO::PARAM_STR);
        $insert->bindValue(3, 1, PDO::PARAM_INT);
        $insert->bindValue(4, $_SESSION['id'], PDO::PARAM_INT);
        $insert->bindValue(5, $logoHash.'.png', PDO::PARAM_STR);
        $insert->bindValue(6, time(), PDO::PARAM_INT);
        
        try {
            if (!$insert->execute()) {
                error_log("GROUP_DEBUG: Failed to insert group. Error: " . print_r($insert->errorInfo(), true));
            }
        } catch (PDOException $e) {
            error_log("GROUP_DEBUG: PDOException during group insert: " . $e->getMessage());
            $err = "Database error during group creation.";
        }

        $groupid = $db->lastInsertId();
        error_log("GROUP_DEBUG: Group created with ID: " . $groupid);

        $insertMember = $db->prepare('INSERT INTO user_group_members (user, groupid, `rank`, timejoined, logo, name) VALUES (?, ?, ?, ?, ?, ?)');
        $insertMember->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $insertMember->bindValue(2, $groupid, PDO::PARAM_INT);
        $insertMember->bindValue(3, '127', PDO::PARAM_INT);
        $insertMember->bindValue(4, time(), PDO::PARAM_INT);
        $insertMember->bindValue(5, $logoHash.'.png', PDO::PARAM_STR);
        $insertMember->bindValue(6, $name, PDO::PARAM_STR);
        
        try {
            if (!$insertMember->execute()) {
                error_log("GROUP_DEBUG: Failed to insert member. Error: " . print_r($insertMember->errorInfo(), true));
            }
        } catch (PDOException $e) {
            error_log("GROUP_DEBUG: PDOException during member insert: " . $e->getMessage());
        }

        $insertRankOwner = $db->prepare('INSERT INTO user_group_ranks (`name`, groupid, indicator, members, allow_edit_group, allow_all) VALUES (?, ?, ?, ?, ?, ?)');
        $insertRankOwner->bindValue(1, 'Owner', PDO::PARAM_STR);
        $insertRankOwner->bindValue(2, $groupid, PDO::PARAM_INT);
        $insertRankOwner->bindValue(3, '127', PDO::PARAM_INT);
        $insertRankOwner->bindValue(4, '1', PDO::PARAM_INT);
        $insertRankOwner->bindValue(5, '1', PDO::PARAM_INT);
        $insertRankOwner->bindValue(6, '1', PDO::PARAM_INT);
        
        if (!$insertRankOwner->execute()) {
            error_log("GROUP_DEBUG: Failed to insert Owner rank. Error: " . print_r($insertRankOwner->errorInfo(), true));
        }

        $insertRankAdmin = $db->prepare('INSERT INTO user_group_ranks (`name`, groupid, indicator, allow_edit_comments) VALUES (?, ?, ?, ?)');
        $insertRankAdmin->bindValue(1, 'Admin', PDO::PARAM_STR);
        $insertRankAdmin->bindValue(2, $groupid, PDO::PARAM_INT);
        $insertRankAdmin->bindValue(3, '100', PDO::PARAM_INT);
        $insertRankAdmin->bindValue(4, '1', PDO::PARAM_INT);
        
        if (!$insertRankAdmin->execute()) {
            error_log("GROUP_DEBUG: Failed to insert Admin rank. Error: " . print_r($insertRankAdmin->errorInfo(), true));
        }

        $insertRankMember = $db->prepare('INSERT INTO user_group_ranks (`name`, groupid, indicator) VALUES (?, ?, ?)');
        $insertRankMember->bindValue(1, 'Member', PDO::PARAM_STR);
        $insertRankMember->bindValue(2, $groupid, PDO::PARAM_INT);
        $insertRankMember->bindValue(3, '1', PDO::PARAM_INT);
        
        if (!$insertRankMember->execute()) {
            error_log("GROUP_DEBUG: Failed to insert Member rank. Error: " . print_r($insertRankMember->errorInfo(), true));
        }

        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/JGFJFSHFSAH/core/rendering/group/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if(move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir.$logoHash.'.png')) {

            $db->prepare("UPDATE user_groups SET moderation_status = 1 WHERE id = ?")->execute([$groupid]);

            header('Location: /group/view/'.$groupid);
            die();

        } else {

            $err = "An unexcepcted error occured. Please contact Cyclomatic if you see this error message.";
            die();
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
           <div class="column">
           </div>
           <div class="column is-10">
           <div class="">
<?php if(isset($err)) { ?><p class="help is-danger"><?php echo htmlspecialchars($err); ?></p><?php } ?>
<form method="post" enctype="multipart/form-data">
                            <div class='control'>
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                        </div>
<div class='field'>
<input class="input" type="text" maxlength="20" name="name" placeholder="Group Name">
</div>
<div class='field'>
<textarea class="textarea" maxlength="600" name="description" placeholder="Group Description"></textarea>
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
<input type="file" name="logo" onchange="change(this);">
</label>
</div>
<div class='field'>
                     <div class="h-captcha" data-sitekey="14cddd71-a534-4587-9e69-ce41e1b52f89"></div>
<div class="push-5"></div>
<button type="submit" name="submit" class="button is-info">Create Group for 50 coins</button>
<a class="button is-dark" onClick="window.history.back();"><b>Previous page</b></a>
</form>
</div>
</div>
<div class="column"></div>
</div>
</div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php');
 ?>






