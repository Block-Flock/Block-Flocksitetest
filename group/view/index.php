<?php
$title = 'View Group';
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!isset($_GET['id']) || isset($_GET['id']) && !is_numeric($_GET['id'])) {

    header('Location: /groups');
    die;

}
if(!$AUTH) {

    header('Location: /');
    die();

}
$getGroup = $db->prepare('SELECT user_groups.*, users.id as userid, users.username FROM user_groups JOIN users ON user_groups.creator = users.id WHERE user_groups.id = ?');
$getGroup->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$getGroup->execute();

if($getGroup->rowCount() == 0) {

    header('Location: /groups');
    die;

}

if(!$AUTH) {
    $AUTH = new stdClass(); $my->id = -1;
}

if(!isset($i)) {

    $i = 0;

}

if($AUTH) {
$getMember = $db->prepare("SELECT * FROM user_group_members WHERE user = ? AND groupid = ?");
$getMember->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$getMember->bindValue(2, $_GET['id'], PDO::PARAM_INT);
$getMember->execute();
} else {
    //$getMember->rowCount() = 0;
}
$getRanks = $db->prepare("SELECT * FROM user_group_ranks WHERE groupid = ? ORDER BY indicator ASC");
$getRanks->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$getRanks->execute();

if(isset($getMember) && $getMember->rowCount() > 0) {

    $gm = $getMember->fetch(PDO::FETCH_OBJ);

} else if(!isset($getMember) || isset($getMember) && $getMember->rowCount() == 0)  {

    $gm = new stdClass();
    $gm->rank = 0;

}


//echo $gm->rank;

$gg = $getGroup->fetch(PDO::FETCH_OBJ);

if($gg->moderation_status == 0) {

    $gg->logo = "pending.png";

} else if($gg->moderation_status == 2) {

    $gg->logo = "declined.png";

}

if(isset($_POST["joinGroup"]) && $AUTH && $getMember->rowCount() == 0) {

    $groupid = $_GET["id"];

    $insertMember = $db->prepare('INSERT INTO user_group_members (user, groupid, `rank`, timejoined, logo, name) VALUES (?, ?, ?, ?, ?, ?)');
    $insertMember->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $insertMember->bindValue(2, $groupid, PDO::PARAM_INT);
    $insertMember->bindValue(3, '1', PDO::PARAM_INT);
    $insertMember->bindValue(4, time(), PDO::PARAM_INT);
    $insertMember->bindValue(5, $gg->logo, PDO::PARAM_STR);
    $insertMember->bindValue(6, $gg->name, PDO::PARAM_STR);
    $insertMember->execute();

    $updm = $db->prepare("UPDATE user_group_ranks SET members = members + 1 WHERE `indicator` = 1 AND groupid = ?");
    $updm->bindValue(1, $groupid, PDO::PARAM_INT);
    $updm->execute();

    $updg = $db->prepare("UPDATE user_groups SET members = members + 1 WHERE `id` = ?");
    $updg->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $updg->execute();

    header("Location: /group/view/".$_GET['id']);
    die;

}

if(isset($_POST["leaveGroup"]) && $AUTH && $getMember->rowCount() >= 1 && $gm->rank != 127) {

    $groupid = $_GET["id"];

    $getMember2 = $db->prepare('SELECT * FROM user_group_members WHERE user = ? AND groupid = ?');
    $getMember2->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $getMember2->bindValue(2, $groupid, PDO::PARAM_INT);
    $getMember2->execute();

    if($getMember2->rowCount() == 0) {

        die;

    }

    $gm2 = $getMember->fetch(PDO::FETCH_OBJ);

    $updm = $db->prepare("UPDATE user_group_ranks SET members = members - 1 WHERE `indicator` = ? AND groupid = ?");
    $updm->bindValue(1, $gm->rank, PDO::PARAM_INT);
    $updm->bindValue(2, $groupid, PDO::PARAM_INT);
    $updm->execute();

    $updg = $db->prepare("UPDATE user_groups SET members = members - 1 WHERE `id` = ?");
    $updg->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $updg->execute();

    $deleteMember = $db->prepare('DELETE FROM user_group_members WHERE user = ? AND groupid = ?');
    $deleteMember->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $deleteMember->bindValue(2, $groupid, PDO::PARAM_INT);
    $deleteMember->execute();

    header("Location: /group/view/".$_GET['id']);
    die;


}

if(isset($_POST['accept_false']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('UPDATE user_groups SET `logo` = ? WHERE id = ?');
    $scrub->bindValue(1, 'declined.png', PDO::PARAM_STR);
    $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Scrubbed Group Image', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();

    header('Location: /group/view/'.$_GET['id']);
    die;

}

if(isset($_POST['scrub_description']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('UPDATE user_groups SET `description` = ? WHERE id = ?');
    $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
    $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Scrubbed Group Description', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();

    header('Location: /group/view/'.$_GET['id']);
    die;

}

if(isset($_POST['scrub_name']) && $AUTH && $my->administrator >= 1) {

    $scrub = $db->prepare('UPDATE user_groups SET `name` = ? WHERE id = ?');
    $scrub->bindValue(1, '( Content deleted )', PDO::PARAM_STR);
    $scrub->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $scrub->execute();

    $log = $db->prepare('INSERT INTO user_history (`admin`, user, `action`, timemoderated) VALUES (?, ?, ?, ?)');
    $log->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $log->bindValue(2, $_GET['id'], PDO::PARAM_INT);
    $log->bindValue(3, 'Scrubbed Group Name', PDO::PARAM_STR);
    $log->bindValue(4, time(), PDO::PARAM_INT);
    $log->execute();

    header('Location: /group/view/'.$_GET['id']);
    die;

}
//Load Comments
$commentsLoaded = 0;
$maxcomments = 15;

$allcomments = $db->prepare("SELECT group_comments.*, group_comments.id AS id, group_comments.user AS user, group_comments.timecommented AS ctime FROM group_comments WHERE group_comments.item = ? AND group_comments.`moderation` = 1 ORDER BY group_comments.`timecommented` DESC ");
$allcomments->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$allcomments->execute();

$limitallcomments = $db->prepare("SELECT group_comments.*, group_comments.id AS id, group_comments.user AS user, group_comments.timecommented AS ctime FROM group_comments WHERE group_comments.item = ? AND group_comments.`moderation` = 1 ORDER BY group_comments.`timecommented` DESC LIMIT $maxcomments ");
$limitallcomments->bindValue(1, $_GET['id'], PDO::PARAM_INT);
$limitallcomments->execute();

if(isset($_POST['morecomments'])){  
	$maxcomments = $maxcomments+15;
    $limitallcomments = $db->prepare("SELECT group_comments.*, group_comments.id AS id, group_comments.user AS user, group_comments.timecommented AS ctime FROM group_comments WHERE group_comments.item = ? AND group_comments.id > $commentsLoaded AND group_comments.`moderation` = 1 ORDER BY group_comments.`timecommented` DESC LIMIT $maxcomments ");
    $limitallcomments->bindValue(1, $_GET['id'], PDO::PARAM_INT);
    $limitallcomments->execute();
}

if(isset($_POST['comment']) && $AUTH && $gg->allowcomments == 1 && is_string($_POST['commentstring'])&& isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response']) ) {

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
        
    $err2 = 'Invalid captcha submission, please try again.';
        
    }

    if(!$gg->allowcomments == 1) {
        $err2 = true;
        die;

    }
    $comment = $check->obfuscateIfProfane($_POST['commentstring']);

    if(strlen($comment) < 1){
        $err2 = "Your comment must contain content!";
    }
    if(strlen($comment) > 200) {
        $err2 = "Your comment cannot be longer than 200 characters!";
    }

    if(count(preg_split("/\n|\r/", $comment)) > 9){
        $err2 = "Your comment cannot have more than 5 lines/rows!";
     } else if($check->hasProfanity($_POST['commentstring'])) {

        $err2 = "Our filter has detected profanity in your comment. Please remove any profanity and try again";

    } else if($my->verified == 0) {

        $err2 = 'Please verify your account to post on group wall.';

    }

    if(!isset($err2)) {

        $cinsert = $db->prepare('INSERT INTO group_comments (user, item, comment) VALUES (?, ?, ?)');
        $cinsert->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $cinsert->bindValue(2, $_GET['id'], PDO::PARAM_INT);
        $cinsert->bindValue(3, $comment, PDO::PARAM_STR);
        $cinsert->execute();

        $comment_id = $db->lastInsertId();
        header('Location: /group/view/'.$_GET['id'].'&comment='.$comment_id);
        die;

    }

}
?>
<script>
window.onload = function() {
    getMembers(<?= $_GET['id'] ?>, 1, 1);
}
function getMembers(group, rank, page) {
    $.post("/api/groups/getmembers/", {
        group: group,
        rank: rank,
        page: page
    }).done(function(data) {
    $("#members").html(data);
    })
}
</script>
<meta property="og:title" content="<?php echo htmlspecialchars($gg->name); ?>'s Group ">
<meta property="og:url" content="<?php echo site; ?>/group/view/<?= $gg->id?>">
<meta property="og:image"
    content="/assets/images/group/<?php echo htmlspecialchars($gg->logo);?>?r=<?php echo bin2hex(random_bytes(16)); ?>">
<meta property="og:site_name" content="Bricktaria">
<meta property="og:description"
    content="Bricktaria is a game where you can make your own dreams come true! You can play, talk, build, make new friends and much more! Are you ready to join us in this adventure?">
    <section class="section">
<div class=" page container">
<div class="columns">
<div class="column is-1">
</div>
<div class="column is-10">
<div class="box">
<article class="media">
<figure class="media-left">
<img style="height: 124px; width:124px;" src="/assets/images/group/<?php echo htmlspecialchars($gg->logo);?>?r=<?php echo bin2hex(random_bytes(16)); ?>">
</figure>
<div class="media-content">
<div class="content">
<?php echo htmlspecialchars($gg->name); ?>&nbsp;<span class="has-text-grey-light">Creator: <a href="/user/<?php echo htmlspecialchars($gg->creator);?>"><?php echo htmlspecialchars($gg->username);?></a></span>, <span class="has-text-grey-light"><?php echo number_format($gg->members); ?> member<?php if($gg->members > 1) { ?>s<?php } ?></span><?php if($AUTH) { ?>&nbsp;<form method="post" style="display:inline-block; float:right;"><?php if($getMember->rowCount() == 0) { ?><button type="submit" name="joinGroup" class="button is-success is-small" onclick="this.classList.toggle('button--loading')"><span class="button__text"><i class="fas fa-sign-in-alt"></i>&nbsp;&nbsp;<b>Join Group</b></span></button><?php } else { ?><button <?php if($gm->rank >= 127) { ?> disabled="disabled" <?php } ?> type="submit" class="button is-danger is-small" name="leaveGroup" onclick="this.classList.toggle('button--loading')"><span class="button__text"><i class="fas fa-sign-out-alt"></i>&nbsp;&nbsp;<b>Leave Group</b></span></button><?php } ?>&nbsp;&nbsp;<?php if($gm->rank >= 127) { ?><a href="/group/admin/<?php echo $_GET['id']; ?>" class="button is-danger is-small" onclick="this.classList.toggle('button--loading')"><span class="button__text"><i class="fas fa-tasks"></i>&nbsp;&nbsp;<b>Manage Group</b></span></a><?php } ?></form><?php } ?>
<br>
<?php echo nl2br(htmlspecialchars($gg->description)); ?>
</div>
<?php if($AUTH) { ?>
<?php if($my->administrator >= 1){ ?>
<form method="post">
<button class="button is-danger is-small is-pulled-left" name="accept_false">Scrub Image</button>
</form>
<?php }?>
<?php if($my->administrator >= 1){ ?>
    <form method="post">
<button class="button is-danger is-small is-pulled-left" name="scrub_name">Scrub Name</button>
</form>
<?php }?>
<?php if($my->administrator >= 1){ ?>
    <form method="post">
<button class="button is-danger is-small" name="scrub_description">Scrub Description</button>
</form>
<?php } ?>
<?php } ?>
</div>
</article>
</div>
<div class="box">
<p style="display:inline-block">Members</p><div class="select is-small is-pulled-right">
<select placeholder="Members" name="" id="rankSelection" onchange="getMembers(<?=$_GET['id'] ?>, this.value, 1);"><?php while($gr = $getRanks->fetch(PDO::FETCH_OBJ)) { $i++; $getRankMembers = $db->prepare("SELECT * FROM user_group_members WHERE `rank` = ? AND groupid = ?"); $getRankMembers->bindValue(1, $gr->indicator, PDO::PARAM_INT); $getRankMembers->bindValue(2, $_GET['id'], PDO::PARAM_INT); $getRankMembers->execute(); ?><option <?php if($i == 1) { echo 'selected="selected"'; } ?> value="<?=$gr->indicator?>"><?php echo htmlspecialchars($gr->name); ?> (<?php echo number_format($getRankMembers->rowCount()); ?>)</option><?php } ?></select></div>
<div style="margin-bottom:25px;"></div>
<div id="members"></div>
</div>
<?php if ($AUTH) { ?>
<article class="media">
  <div class="media-content">
  <form action="" method="POST">
  <input class="input" type="hidden" id="csrf" value="<?= $_SESSION['CSRF-TOKEN']; ?>">
    <div class="field">
      <p class="control">
        <textarea  name="commentstring" class="textarea" placeholder="What's on your mind?" required></textarea>
      </p>
    </div>
    <?php if(isset($err2)) { { ?><p class="help is-danger"><?php echo htmlspecialchars($err2); ?></p><?php } ?><?php } ?>
    <div class='field'>
  <div class="h-captcha" data-sitekey="14cddd71-a534-4587-9e69-ce41e1b52f89"></div>
                        </div>
    <nav class="level">
      <div class="level-left">
        <div class="level-item">
          <button class="button is-info" type="submit" name="comment">Post Comment</button>
        </div>
      </div>
    </nav>
  </form>
  </div>
</article>
<br/>
<?php } else { ?>
</div>
    <?php } ?>
<div class="is-divider" data-content="COMMENTS"></div>
<?php while($cm = $limitallcomments->fetch(PDO::FETCH_OBJ)) {
    $userinfo = $db->prepare('SELECT group_comments.*, users.* FROM group_comments JOIN users WHERE group_comments.user = users.id AND group_comments.id = ?');
    $userinfo->bindValue(1, $cm->id, PDO::PARAM_INT);
    $userinfo->execute();
    $uI = $userinfo->fetch(PDO::FETCH_OBJ);
    ?>
    <div class="box">
<article class="media">
  <figure class="media-left">
  <img Src="/assets/images/avatars/<?php echo htmlspecialchars($uI->avatar_hash);?>.png?r=<?php echo bin2hex(random_bytes(16)); ?>"
      onerror="this.src='/assets/images/avatars/default.png?r=<?php echo bin2hex(random_bytes(16)); ?>'"
      alt="Could Not Get Profile Image!" width="110px" height="110px" >
  </figure>
  <div class="media-content">
    <div class="content">
      <p>
        <strong><a href="/user/<?=$uI->id?>"><?php echo htmlspecialchars($uI->username); ?></a></strong> <small class=" has-text-grey-light"><?php echo ($cm->timecommented) ?></small>
        <br>

<?php echo htmlspecialchars($cm->comment) ?></p>
      </p>
      <p>
      </p>
    </div>
  </div>
</article>
</div>
<?php } $commentsLoaded = $commentsLoaded + 1; ?>
<?php if($allcomments->rowCount() > $maxcomments){?>
<form action="" method="POST">
<button type="submit" method="post" name="morecomments" class="button is-primary is-fullwidth" onclick="this.classList.toggle('button--loading')"><span class="button__text">Load More</span></button>
</form>
<?php } ?>
<div class="column"></div>
</div>
</div>
</section>
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php');
?>