<?php
$title = 'Create User Ads';
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    header('Location: /login');
    die;

}

if(isset($_FILES['image']['name']) && $AUTH) {

    $check = new Check();

    if($my->Currency < 5) {

        $err = ['affects'=>'none','msg'=>'You need 5 coins to create an advert.'];
        
    } else if(empty($_FILES['image']['name'])) {

        $err = ['affects'=>'none','msg'=>'You need to upload an image.'];

    } else if(!getimagesize($_FILES['image']['tmp_name'])) {

        $err =  ['affects'=>'none','msg'=>'Invalid image.'];

    } else if($_FILES['image']['size'] > 1000000) {

        $err = ['affects'=>'none','msg'=>'Your image must weigh less than 1MB.'];

    } else if(exif_imagetype($_FILES['image']['tmp_name']) != 3) {

        $err = ['affects'=>'none','msg'=>'Your image must be a PNG.'];

    } else if(!isset($err)) {

        $logoHash = bin2hex(random_bytes(16));
        $creator = $my->username;
        $url = 'NULL';
        $active = '0';
        $accid = '1';

        $updateCur = $db->prepare('UPDATE users SET currency = currency - 10 WHERE id = ?');
        $updateCur->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $updateCur->execute();

        $updateAcc = $db->prepare('UPDATE users SET currency = currency + 10 WHERE id = ?');
        $updateAcc->bindValue(1, $accid, PDO::PARAM_INT);
        $updateAcc->execute();

        $insert = $db->prepare('INSERT INTO user_ads (creator, thumbnail, url, active) VALUES (?, ?, ?, ?)');
        $insert->bindValue(1, $creator, PDO::PARAM_STR);
        $insert->bindValue(2, $logoHash, PDO::PARAM_STR);
        $insert->bindValue(3, $url, PDO::PARAM_STR);
        $insert->bindValue(4, $active, PDO::PARAM_STR);
        $insert->execute();

        $id = $db->lastInsertId();

        if(move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/assets/images/landing/'.$logoHash.'.png')) {

            header('Location: /');
            die;

        } else {

            die('Could not upload image.');

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
            <div class="column is-4">
            </div>
            <div class="column is-4">
<div class="box">
<h2 class="novello-title is-size-4">Create User Advert</h2>
<p>Download the Default Ad if you haven't already to create a user ad.</p>
<br />
<?php if(isset($err) && $err['affects'] == 'none') { ?>
<p class="has-text-danger"><?php echo $err['msg']; ?></p>
<?php } ?>
<form method="post" enctype="multipart/form-data">
<div class="field" style="width: 100%; justify-content: center;">
<label class="upload control" style="width: 100%;">
<div class="upload-draggable is-primary" style="width: 100%;">
<section class="section">
<div class="content has-text-centered">
<p><span class="icon is-large"><i class="mdi mdi-upload mdi-48px"></i></span></p>
<p id="text-change">Click to add files</p>
</div>
</section>
</div>
<input type="file" name="image" onchange="change(this);">
</label>
</div>
<b-field>
<button type="submit" class="button is-primary is-fullwidth">Create Ad</button>
</b-field>
</form>
</div>
</div>
<div class="columns">
            <div class="column is-2">
            </div>
            <div class="column is-8">
<div class="box">
<p>Default ad</p><div style="margin-bottom:5px;"></div>
<a href="https://media.discordapp.net/attachments/797508781059473459/909122784033132574/unknown.png?width=225&height=675"><img src="https://media.discordapp.net/attachments/797508781059473459/909122784033132574/unknown.png?width=225&height=675" alt=""></a>
<p>Click image to enlarge it</p>
</div>
</div>
</div>
</div>
</section>

<?php
$app->footer();
?>