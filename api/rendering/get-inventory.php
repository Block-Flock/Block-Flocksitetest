<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');
global $pages;

if($AUTH && isset($_POST['category'])) {
    if(!is_numeric($_POST['category'])) {
        die();
    }
    if($_POST['category'] > 8) {
        die;
    }

    $limit = 8;
    if(!isset($_POST['page'])) {
        $page = 1;
    } else {
        $page = $_POST['page'];
    }

    $offset = ($page - 1) * $limit;
    if($offset < 0) { $offset = 0; }

    $count = $db->prepare('SELECT * FROM user_inventory WHERE user = ?');
    $count->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $count->execute();

    $pages = ceil($count->rowCount() / $limit);

    $selectInventory = $db->prepare('SELECT user_inventory.*, store_items.id as itemid, store_items.name, store_items.image, store_items.creator, store_items.type FROM user_inventory JOIN store_items ON user_inventory.item = store_items.id WHERE user_inventory.user = ? AND store_items.type = '.$_POST['category'].' AND store_items.moderation = 1 ORDER BY user_inventory.id DESC LIMIT '.$limit.' OFFSET '.$offset);
    $selectInventory->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $selectInventory->execute();

    echo '<div class="columns is-multiline">';

    while($si = $selectInventory->fetch(PDO::FETCH_OBJ)) {
        if(strlen($si->name) > 12) {
            $si->name = substr($si->name, 0, 12) . '...';
        }

        $getWearing = $db->prepare('SELECT * FROM user_data WHERE user = ?');
        $getWearing->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $getWearing->execute();

        $gw = $getWearing->fetch(PDO::FETCH_OBJ);

        $isWearing = false;
        $wearingField = '';
        
        // Determine if item is being worn and which field it's using
        switch($si->type) {
            case 1: // Hats
                $isWearing = ($gw->hat1 == $si->itemid || $gw->hat2 == $si->itemid || $gw->hat3 == $si->itemid);
                break;
            case 2: // Faces
                $isWearing = ($gw->face == $si->itemid);
                $wearingField = 'face';
                break;
            case 3: // Shirts
                $isWearing = ($gw->shirt == $si->itemid);
                $wearingField = 'shirt';
                break;
            case 4: // Pants
                $isWearing = ($gw->pants == $si->itemid);
                $wearingField = 'pants';
                break;
            case 5: // Gear
                $isWearing = ($gw->gear == $si->itemid);
                $wearingField = 'gear';
                break;
            case 6: // Accessories
                $isWearing = ($gw->accessory == $si->itemid);
                $wearingField = 'accessory';
                break;
            case 7: // Heads
                $isWearing = ($gw->head == $si->itemid);
                $wearingField = 'head';
                break;
            case 8: // Tshirts
                $isWearing = ($gw->tshirt == $si->itemid);
                $wearingField = 'tshirt';
                break;
        }

        echo '<div class="column is-6-mobile is-3-tablet">';
        echo '<div class="inventory_result">';
        echo '<div class="has-text-centered">';
        echo '<a href="/store/view/?id='.$si->itemid.'"><img src="/assets/images/store/'.$si->image.'.png?r='.bin2hex(random_bytes(16)).'" width="100" height="100" alt=""></a>';
        echo '<a class="title is-6 storetitle" href="/store/view/?id='.$si->itemid.'"><p>'.htmlspecialchars($si->name).'</p></a>';

        if($isWearing) {
            echo '<button onclick="remove('.$si->item.', \''.$wearingField.'\');" class="button is-small is-danger">';
            echo '<i class="fas fa-times"></i>&nbsp;<strong>Unwear</strong>';
            echo '</button>';
        } else {
            echo '<button onclick="wear('.$si->item.', '.$si->type.');" class="button is-small is-info">';
            echo '<i class="fas fa-plus"></i>&nbsp;<strong>Wear</strong>';
            echo '</button>';
        }

        echo '</div></div></div>';
    } 

    echo '</div>';

    if($pages > 1) {
        echo '
            <nav class="pagination is-small">
            <a role="button" '; if($page-1 != 0) { echo ' onclick="get_inventory('.$_POST['category'].','.($page-1).')"'; } ?><?php if($page-1 == 0) { echo 'disabled="disabled" ';} echo 'class="pagination-link is-hidden-mobile"><span class="icon"><i class="fas fa-chevron-left fa-sm"></i></span></a>
            <ul class="pagination-list" style="justify-content: center;">
            ';
            for ($i = max(1, $page - 5); $i <= min($page + 5, $pages); $i++) {

                                if ($i <= $pages) { echo '<li>
            <a onclick="get_inventory('.$_POST['category'].','.$i.')" ';if($i == $page) { echo 'aria-current="true"'; } echo ' class="pagination-link'; if($i == $page) { echo ' is-current'; } echo '">'.$i.'</a></li>'; } }
            echo '</ul>
            <a role="button" '; if($pages > 1 || $page+1 <= $pages) { echo ' onclick="get_inventory('.$_POST['category'].','.($page+1).')"'; } ?> <?php if($pages <= 1 || $page+1 > $pages) { echo 'disabled="disabled" ';} echo' class="pagination-link pagination-previous is-hidden-mobile" style="padding-left: .25em;padding-right: .25em;"><span class="icon"><i class="fas fa-chevron-right fa-sm"></i></span></a>
            </nav>';
    }
}