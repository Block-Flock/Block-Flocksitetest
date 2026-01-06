<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

if(isset($_POST['group']) && isset($_POST['rank']) && isset($_POST['page'])) {

    $groupExists = $db->prepare("SELECT * FROM user_groups WHERE id = ?");
    $groupExists->bindValue(1, $_POST["group"], PDO::PARAM_INT);
    $groupExists->execute();
    
    $page = $_POST['page'];

    if($groupExists->rowCount() == 0) {

        die();

    } else {

        $ge = $groupExists->fetch(PDO::FETCH_OBJ);

    }

    $rankExists = $db->prepare("SELECT * FROM user_group_ranks WHERE indicator = ? AND groupid = ?");
    $rankExists->bindValue(1, $_POST["rank"], PDO::PARAM_INT);
    $rankExists->bindValue(2, $_POST["group"], PDO::PARAM_INT);
    $rankExists->execute();

    if($rankExists->rowCount() == 0) {

        die();

    } else {

        $gr = $rankExists->fetch(PDO::FETCH_OBJ);

    }

    $limit = 4;

    $offset = ($_POST["page"]-1) * $limit;

    if($offset < 0) { $offset = 0; }

    $getMembers = $db->prepare("SELECT user_group_members.*, users.id AS userid, users.username, users.avatar_hash FROM user_group_members JOIN users ON user_group_members.user = users.id WHERE user_group_members.rank = ? AND user_group_members.groupid = ? LIMIT $limit OFFSET $offset");
    $getMembers->bindValue(1,  $_POST['rank'], PDO::PARAM_INT);
    $getMembers->bindValue(2,  $_POST['group'], PDO::PARAM_INT);
    $getMembers->execute();

    $pages = ceil($gr->members / $limit);


    echo '<div class="columns">';
    if($getMembers->rowCount() == 0) { echo '<p style="width:100%;" class="has-text-centered">No members found in this rank</p><div style="margin-bottom:20px"></div>'; }
    while($gm = $getMembers->fetch(PDO::FETCH_OBJ)) {

        echo '<div class="column is-3 has-text-centered"><img src="/assets/images/avatars/'.$gm->avatar_hash.'.png?r='.bin2hex(random_bytes(16)).'" alt=""><br><a href="/user/'.$gm->userid.'">'.htmlspecialchars($gm->username).'</a></div>';

    }
    echo '</div>';

if($pages > 1) {  echo '
    <nav  class="pagination is-centered" role="navigation"
    aria-label="pagination"> 
<a role="button" '; if($page-1 != 0) { echo ' onclick="getMembers('.$_POST['group'].','.$_POST['rank'].', '.($page-1).')"'; } if($page-1 == 0) { echo 'disabled="disabled" ';} echo 'class="pagination-link is-hidden-mobile"><i class=" fas fa-chevron-left fa-sm"></i></a>
<ul  class="pagination-list">';
 for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {

					if ($i <= $pages) { echo '<li>
<a onclick="getMembers('.$_POST['group'].','.$_POST['rank'].','.$i.')" ';if($i == $page) { echo 'aria-current="true"'; } echo ' class="pagination-link'; if($i == $page) { echo ' is-current'; } echo '">'.$i.'</a></li>'; } } echo '
</ul>
<a role="button" '; if($pages > 1 || $page+1 <= $pages) {  echo 'onclick="getMembers('.$_POST['group'].', '.$_POST['rank'].', '.($page+1).')"'; }  if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled" ';} echo 'class="is-hidden-mobile pagination-next"><i class="fas fa-chevron-right fa-sm"></i></a>
</nav>';
}
}