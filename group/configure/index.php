<?php
$title = "Configure Member";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    header('Location: /');
    die;

}

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header('Location: /groups');
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

if(!isset($_GET["uid"]) || !is_numeric($_GET["uid"])) {

    header('Location: /');
    die;

}

$userExists = $db->prepare("SELECT * FROM users WHERE id = ?");
$userExists->bindValue(1, $_GET["uid"], PDO::PARAM_INT);
$userExists->execute();

if($userExists->rowCount() == 0) {

    header('Location: /');
    die;

} else {


    $ue = $userExists->fetch(PDO::FETCH_OBJ);

}

$memberJoined = $db->prepare("SELECT * FROM user_group_members WHERE user = ? AND groupid = ?");
$memberJoined->bindValue(1, $_GET["uid"], PDO::PARAM_INT);
$memberJoined->bindValue(2, $_GET["id"], PDO::PARAM_INT);
$memberJoined->execute();

if($memberJoined->rowCount() == 0) {

    header('Location: /');
    die;

}


$getMember = $db->prepare("SELECT * FROM user_group_members WHERE groupid = ? AND user = ? AND `rank` = 127");
$getMember->bindValue(1, $_GET["id"], PDO::PARAM_INT);
$getMember->bindValue(2, $_SESSION["id"], PDO::PARAM_INT);
$getMember->execute();

if($getMember->rowCount() == 0) {

    header("Location: /");
    die;

}

$getRank = $db->prepare("SELECT user_group_members.*, user_group_ranks.id as rankid, user_group_ranks.name, user_group_ranks.indicator FROM user_group_members JOIN user_group_ranks ON user_group_members.rank = user_group_ranks.indicator WHERE user_group_members.user = ? AND user_group_members.groupid = ? AND user_group_ranks.groupid = ?");
$getRank->bindValue(1, $_GET["uid"], PDO::PARAM_INT);
$getRank->bindValue(2, $_GET["id"], PDO::PARAM_INT);
$getRank->bindValue(3, $_GET["id"], PDO::PARAM_INT);
$getRank->execute();

if($getRank->rowCount() == 0) {

    header('Location: /');
    die;


} else {


    $gr = $getRank->fetch(PDO::FETCH_OBJ);

}

$getRanks = $db->prepare("SELECT * FROM user_group_ranks WHERE groupid = ? ORDER BY indicator DESC");
$getRanks->bindValue(1, $_GET["id"], PDO::PARAM_INT);
$getRanks->execute();

if(isset($_POST['rank']) && $AUTH && $getMember->rowCount() >= 1 && $groupExists->rowCount() >= 1 && $userExists->rowCount() >= 1 && $memberJoined->rowCount() >= 1) {

    if(!is_numeric($_POST['rank']) || $_POST['rank'] == 1 || $_POST['rank'] == 127) {

        die;

    }

    $rankExists = $db->prepare('SELECT * FROM user_group_ranks WHERE indicator = ? AND groupid = ?');
    $rankExists->bindValue(1, $_POST['rank'], PDO::PARAM_INT);
    $rankExists->bindValue(2, $_GET["id"], PDO::PARAM_INT);
    $rankExists->execute();

    if($rankExists->rowCount() >= 1) {

    $updr1 = $db->prepare("UPDATE user_group_ranks SET members = members - 1 WHERE indicator = ? AND groupid = ?");
    $updr1->bindValue(1, $gr->indicator, PDO::PARAM_INT);
    $updr1->bindValue(2, $_GET["id"], PDO::PARAM_INT);
    $updr1->execute();

    $updr2 = $db->prepare("UPDATE user_group_ranks SET members = members + 1 WHERE indicator = ? AND groupid = ?");
    $updr2->bindValue(1, $_POST['rank'], PDO::PARAM_INT);
    $updr2->bindValue(2, $_GET["id"], PDO::PARAM_INT);
    $updr2->execute();

    $updm = $db->prepare("UPDATE user_group_members SET `rank` = ? WHERE user = ? AND groupid = ?");
    $updm->bindValue(1, $_POST['rank'], PDO::PARAM_INT);
    $updm->bindValue(2, $_GET['uid'], PDO::PARAM_INT);
    $updm->bindValue(3, $_GET["id"], PDO::PARAM_INT);
    $updm->execute();

    header("Location: /group/view/".$_GET['id']);
    die;

    }

}

?>
<section class="section">
<div class="page container">
<div class="columns">
<div class="column">
</div>
<div class="column is-10">
    <div class="box">
                <form method="post">
                <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
                <button type="submit" class="button is-link is-pulled-left">Save</button>
                    <img style="width:256px; height:256px; text-align: center;"
                        src="/assets/images/avatars/<?php echo htmlspecialchars($ue->avatar_hash); ?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>"
                        alt="">
                    <br>
                    <a href="/user/<?php echo $_GET['id']; ?>"><?php echo htmlspecialchars($ue->username); ?></a>
                    <br>
                                            <div class="field">                        
                    <div class="select is-small">
            <select required name="rank" placeholder="<?php echo htmlspecialchars($gr->name); ?>">
<?php while($grs = $getRanks->fetch(PDO::FETCH_OBJ)) { ?>
                                <option value="<?php echo htmlspecialchars($grs->indicator); ?>"><?php echo htmlspecialchars($grs->name); ?></option>
                                <?php } ?>
                            </select>
                        </div>
</div>                    </form>
                </div>
            </div>
            <div class="column"></div>
        </div>
    </div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php'); ?>