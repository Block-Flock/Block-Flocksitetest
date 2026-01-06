<?php
$title = "Group Admin";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    header("Location: /");
    die;

}

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: /");
    die;

}

$groupExists = $db->prepare("SELECT * FROM user_groups WHERE id = ?");
$groupExists->bindValue(1, $_GET["id"], PDO::PARAM_INT);
$groupExists->execute();

if($groupExists->rowCount() == 0) {

    die();

} else {

    $ge = $groupExists->fetch(PDO::FETCH_OBJ);

}

$getMember = $db->prepare("SELECT * FROM user_group_members WHERE groupid = ? AND user = ? AND `rank` = 127");
$getMember->bindValue(1, $_GET["id"], PDO::PARAM_INT);
$getMember->bindValue(2, $_SESSION["id"], PDO::PARAM_INT);
$getMember->execute();

if($getMember->rowCount() == 0) {

    header("Location: /");
    die;

}

$getMembers = $db->prepare("SELECT user_group_members.*, users.id as userid, users.username, users.avatar_hash FROM user_group_members JOIN users ON user_group_members.user = users.id WHERE user_group_members.groupid = ? AND user_group_members.`rank` < 127");
$getMembers->bindValue(1, $_GET["id"], PDO::PARAM_INT);
$getMembers->execute();

$getRanks = $db->prepare("SELECT * FROM user_group_ranks WHERE groupid = ? ORDER BY indicator DESC");
$getRanks->bindValue(1, $_GET["id"], PDO::PARAM_INT);
$getRanks->execute();

if($ge->moderation_status == 0) {

    $ge->logo = "pending.png";

} else if($ge->moderation_status == 2) {

    $ge->logo = "declined.png";

}

if(isset($_POST['description']) && $getMember->rowCount() >= 1 && $groupExists->rowCount() == 1 && $AUTH) {

    if(strlen($_POST['description']) > 1000) {

        $err = ["affected" => "desc", "msg" => "Description cannot be longer than 1000 characters."];

    } else {

        $upd = $db->prepare("UPDATE user_groups SET `description` = ? WHERE id = ?");
        $upd->bindValue(1, $_POST["description"], PDO::PARAM_STR);
        $upd->bindValue(2, $_GET["id"], PDO::PARAM_INT);
        $upd->execute();

        header("Location: /group/admin/$ge->id");
        die;

    }

}

if(isset($_FILES['image']['name']) && $getMember->rowCount() >= 1 && $groupExists->rowCount() == 1 && $AUTH) {

    //echo 'e';

    if(empty($_FILES['image']['name'])) {

        $err = 'You need to upload a logo.';

    } else if(!getimagesize($_FILES['image']['tmp_name'])) {

        $err = 'Invalid logo.';

    } else if(exif_imagetype($_FILES['image']['tmp_name']) != 3) {

        $err = 'Your image must be a PNG.';

    } else if($_FILES['image']['size'] > 1000000) {

        $err = 'Your image must weigh less than 1MB.';

    } else if(!isset($err)) {

        $hash = bin2hex(random_bytes(16));

        $upd = $db->prepare("UPDATE user_groups SET logo = ?, moderation_status = 0 WHERE id = ?");
        $upd->bindValue(1, $hash.'.png', PDO::PARAM_STR);
        $upd->bindValue(2, $_GET["id"], PDO::PARAM_INT);
        $upd->execute();

        if(move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/assets/images/group/'.$hash.'.png')) {

            header('Location: /group/admin/'.$_GET['id']);
            die;

        } else {

            die('An unexcepcted error occured. Please contact studzy#9280 if you see this error message.');

        }

        header("Location: /group/admin/".$_GET['id']);
        die;

    }

    if($err){
        echo $err;
    }

}

if(isset($_POST['rank_save']) && $getMember->rowCount() >= 1 && $groupExists->rowCount() == 1 && $AUTH) {

    $names = $_POST["name"];
    $indicators = $_POST["indicator"];

    foreach($names as $key => $value) {

        $upd = $db->prepare("UPDATE user_group_ranks SET `name` = ? WHERE `indicator` = ? AND `groupid` = ?");
        $upd->bindValue(1, $value, PDO::PARAM_STR);
        $upd->bindValue(2, $key, PDO::PARAM_INT);
        $upd->bindValue(3, $_GET['id'], PDO::PARAM_INT);
        $upd->execute();

    }

    foreach($indicators as $key => $value) {

        if(is_numeric($value) && is_numeric($key) && $value != 1 && $value != 127 && $key != 1 && $key != 127 && $value > 1 && $value < 127 && $key > 1 && $key < 127) {
            
        $getIndicator = $db->prepare("SELECT * FROM user_group_ranks WHERE indicator = ? AND groupid = ?");
        $getIndicator->bindValue(1, $value, PDO::PARAM_INT);
        $getIndicator->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $getIndicator->execute();
        
        if($getIndicator->rowCount() == 0) {

        $upd = $db->prepare("UPDATE user_group_ranks SET `indicator` = ? WHERE indicator = ? AND `groupid` = ?");
        $upd->bindValue(1, $value, PDO::PARAM_STR);
        $upd->bindValue(2, $key, PDO::PARAM_INT);
        $upd->bindValue(3, $_GET['id'], PDO::PARAM_INT);
        $upd->execute();

        }
        header("Refresh:0");
        }else{
            $err ="Invalid Indication ".$value;
        }

    }    
}

if(isset($_POST["new_rank"]) && $getMember->rowCount() >= 1 && $groupExists->rowCount() == 1 && $AUTH && $getRanks->rowCount() < 127) {

    $getIndicator = $db->prepare("SELECT * FROM user_group_ranks WHERE indicator = ? AND groupid = ?");
    $getIndicator->bindValue(1, $_POST["new_rank_indicator"], PDO::PARAM_INT);
    $getIndicator->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $getIndicator->execute();

    $getName = $db->prepare("SELECT * FROM user_group_ranks WHERE `name` = ? AND groupid = ?");
    $getName->bindValue(1, $_POST["new_rank_name"], PDO::PARAM_STR);
    $getName->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $getName->execute();

    $err = false;
    if(empty($_POST['new_rank_indicator'])) {
    
        $err = "Rank indicator cannot be empty.";

    } else if($_POST["new_rank_indicator"] == 1 || $_POST["new_rank_indicator"] == 127) {

        $err = "Rank indicator cannot be 1 or 127.";

    } else if(strlen(str_replace(' ', '', $_POST["new_rank_name"])) < 3) {

        $err = "Rank name must be above 3 characters.";

    } else if(strlen($_POST["new_rank_name"]) > 45) {

        $err = "Rank name must be below 45 characters.";

    } else if($my->Currency < 20) {

        $err = "You must have 20 coins to make a new rank.";

    } else if($getIndicator->rowCount() >= 1 || $getName->rowCount() >= 1) {

        $err = "Rank already exists.";

    } else {
        if(!isset($err)) {

        $updc = $db->prepare("UPDATE users SET Currency = Currency - 20 WHERE id = ?");
        $updc->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $updc->execute();

        $ins = $db->prepare("INSERT INTO user_group_ranks (`name`, groupid, indicator, members) VALUES (?, ?, ?, 0)");
        $ins->bindValue(1, $_POST["new_rank_name"], PDO::PARAM_STR);
        $ins->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $ins->bindValue(3, $_POST["new_rank_indicator"], PDO::PARAM_INT);
        $ins->execute();
        
        header("Location: /group/admin/".$_GET['id']);
        die;
    }
    if($err){
        echo $err;
    }
    }

}
if(isset($_POST["new_rank_name"]) && isset($_POST["new_rank_indicator"]) && $getMember->rowCount() >= 1 && $groupExists->rowCount() == 1 && $AUTH) {

    $getIndicator = $db->prepare("SELECT * FROM user_group_ranks WHERE indicator = ? AND groupid = ?");
    $getIndicator->bindValue(1, $_POST["new_rank_indicator"], PDO::PARAM_INT);
    $getIndicator->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $getIndicator->execute();

    $getName = $db->prepare("SELECT * FROM user_group_ranks WHERE `name` = ? AND groupid = ?");
    $getName->bindValue(1, $_POST["new_rank_name"], PDO::PARAM_STR);
    $getName->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $getName->execute();

    if(empty($_POST['new_rank_indicator'])) {
    
        $err =  "Rank indicator cannot be empty.";

    } else if($_POST["new_rank_indicator"] == 1 || $_POST["new_rank_indicator"] == 127) {

        $err =  "Rank indicator cannot be 1 or 127.";

    } else if(strlen(str_replace(' ', '', $_POST["new_rank_name"])) < 3) {

        $err = "Rank name must be above 3 characters.";

    } else if(strlen($_POST["new_rank_name"]) > 45) {

        $err = "Rank name must be below 45 characters.";

    } else if($my->Currency < 20) {

        $err = "You must have 20 coins to make a new rank.";

    } else if($getIndicator->rowCount() >= 1 || $getName->rowCount() >= 1) {

        $err = "Rank already exists.";

    } else {

        $updc = $db->prepare("UPDATE users SET Currency = Currency - 20 WHERE id = ?");
        $updc->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $updc->execute();

        $ins = $db->prepare("INSERT INTO user_group_ranks (`name`, groupid, indicator, members) VALUES (?, ?, ?, 0)");
        $ins->bindValue(1, $_POST["new_rank_name"], PDO::PARAM_STR);
        $ins->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $ins->bindValue(3, $_POST["new_rank_indicator"], PDO::PARAM_INT);
        $ins->execute();
        
        header("Location: /group/ranks/".$_GET['id']);
        die;

    }

}
?>
<script>
function change(file) {
    $("#text-change").text(file.files[0].name);
}
</script>
<section class="section">
<div class="page container">
<div class="columns">
<div class="column">
</div>
<div class="column is-10">
                <div class="box">
                <?php if(isset($err)) { ?><p class="help is-danger"><?php echo htmlspecialchars($err); ?></p><?php } ?>
                <div class="tabs is-centered">
  <ul>
    <li ><a href="/group/admin/<?php echo $_GET['id']; ?>">General</a></li>
    <li class="is-active"><a href="/group/ranks/<?php echo $_GET['id']; ?>">Ranks</a></li>
    <li><a href="/group/members/<?php echo $_GET['id']; ?>">Members</a></li>
  </ul>
  </div>
                            <form method="post">
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                                <?php while($gr = $getRanks->fetch(PDO::FETCH_OBJ)) { ?>
                                <div class="field" style="width:76%; display:inline-block;">
                                    <input class="input" type="text" name="name[<?php echo $gr->indicator; ?>]"
                                        value="<?php echo htmlspecialchars($gr->name); ?>" required></input>
                                </div>
                                <div class="field" style="width:23%; display:inline-block;">
                                    <input
                                        <?php if($gr->indicator == 127 || $gr->indicator == 1) { echo 'disabled="disabled"'; } ?> class="input" type="number"
                                        name="indicator[<?php echo htmlspecialchars($gr->indicator); ?>]"
                                        value="<?php echo htmlspecialchars($gr->indicator); ?>" required></input>
                                </div>
                                <?php } ?>
                                <field style="display:inline-block;">
                                    <button name="rank_save" type="submit" class="button is-link"><strong>Save</strong></button>
                                </field>
                            </form>
                            <br>
                            <form method="post">
                            <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                                <field style="width:100%;display:inline-block;">
                                    <input name="new_rank_name" class="input" type="text" placeholder="Name" required></input>
                                    <div class="push-5"></div>
                                    <input class="input" type="number" name="new_rank_indicator" type="text" placeholder="Indicator (2-126)" required>
                                    </input>
                                    <div class="push-5"></div>
                                    <button name="new_rank" type="submit" class="button is-link"><strong>Create Rank for 20 coins</strong></button>
                                </field>
                            </form>
                    
                </div>
            </div>
            <div class="column"></div>
        </div>
    </div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php');
 ?>