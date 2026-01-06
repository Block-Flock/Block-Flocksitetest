<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');
global $pages;

if($AUTH && isset($_POST['category'])) {

    if(!is_numeric($_POST['category'])) {

        die();

    }

    if($_POST['category'] > 4) {

        die;

    }

    $limit = 8;

    $page = $_POST['page'] ?? 1;

    $offset = ($page - 1) * $limit;

    if($offset < 0) { $offset = 0; }

    $count = $db->prepare('SELECT * FROM user_inventory WHERE user = ?');
    $count->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $count->execute();

    $pages = ($count->rowCount() / $limit);

    $selectInventory = $db->prepare('SELECT user_inventory.*, store_items.id as itemid, store_items.name, store_items.image FROM user_inventory JOIN store_items ON user_inventory.item = store_items.id WHERE user_inventory.user = ? AND store_items.type = '.$_POST['category'].' AND store_items.moderation = 1 ORDER BY user_inventory.id DESC LIMIT '.$limit.' OFFSET '.$offset);
    $selectInventory->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $selectInventory->execute();

    echo ' <div class="columns is-multiline is-mobile">';

    while($si = $selectInventory->fetch(PDO::FETCH_OBJ)) {

        if(strlen($si->name) > 12) {

            $si->name = substr($si->name, 0, 12) . '...';

        }


        $getWearing = $db->prepare('SELECT  * FROM user_data WHERE user = ?');
        $getWearing->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $getWearing->execute();

        $gw = $getWearing->fetch(PDO::FETCH_OBJ);

        if($gw->hat1 != $si->itemid && $gw->hat2 != $si->itemid && $gw->hat3 != $si->itemid && $gw->face != $si->itemid && $gw->shirt != $si->itemid && $gw->pants != $si->itemid)

        echo '
        <div class="column is-6-mobile is-3-tablet">
        <div class="inventory_result">
        <div class="has-text-centered"> 
        <a href="/store/view/?id='.htmlspecialchars($si->itemid).'"><img src="/assets/images/store/'.$si->image.'.png?r='.bin2hex(random_bytes(16)).'" alt=""  width="100" height="100"></a>
        <a class="title is-6 storetitle" href="/store/view/?id='.$si->itemid.'"><p>'.htmlspecialchars($si->name).'</p></a>
        <a class="" href="/user/profile/?id='.$si->creator.'"><p class="has-text-grey-light">By'.htmlspecialchars($si->username).'</p></a>
        <button onclick="wear('.htmlspecialchars($si->item).');"  class="button is-small is-primary"><strong>Equip</strong></div></div></div>'; } }

    echo '</div>';

if($pages > 1) {
    echo '
    <nav  class="pagination is-centered is-small" role="navigation"
    aria-label="pagination">        
    <a role="button" '; if($page-1 != 0) { echo ' onclick="get_inventory('.$_POST['category'].','.($page-1).')"'; } ?><?php if($page-1 == 0) { echo 'disabled="disabled" ';} echo 'class="pagination-link"><i class="fas fa-backward"></i>&nbsp;Previous</button></a>
    <ul  class="pagination-list">
    ';
        for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {

                            if ($i <= $pages) { echo '<li>
        <a onclick="get_inventory('.$_POST['category'].','.$i.')" ';if($i == $page) { echo 'aria-current="true"'; } echo ' class="pagination-link'; if($i == $page) { echo ' is-current'; } echo '">'.$i.'</a></li>'; } }
        echo '</ul>
        <a role="button" '; if($pages > 1 || $page+1 <= $pages) { echo ' onclick="get_inventory('.$_POST['category'].','.($page+1).')"'; } ?> <?php if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled" ';} echo' class="is-hidden-mobile pagination-next">Next
        page&nbsp;<i class="fas fa-forward"></i></button></a>
        </nav>';
}
