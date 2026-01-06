<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

if($AUTH) {
    $selectInventory = $db->prepare('SELECT user_inventory.*, store_items.id as itemid, store_items.name, store_items.image, store_items.type FROM user_inventory JOIN store_items ON user_inventory.item = store_items.id WHERE user_inventory.user = ?');
    $selectInventory->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $selectInventory->execute();

    echo '<div class="columns is-multiline is-mobile" id="wearing_columns">';

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
                $wearingField = 'hat';
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

        if ($isWearing) {
            echo '<div class="column is-6-mobile is-3-tablet">';
            echo '<div class="inventory_result">';
            echo '<div class="has-text-centered">';
            echo '<a href="/store/view/?id='.$si->itemid.'"><img src="/assets/images/store/'.$si->image.'.png?r='.bin2hex(random_bytes(16)).'" alt="" width="100" height="100"></a>';
            echo '<a class="title is-6 storetitle" href="/store/view/?id='.$si->itemid.'"><p>'.htmlspecialchars($si->name).'</p></a>';
            echo '<button onclick="remove('.$si->item.', \''.$wearingField.'\');" class="button is-small is-danger">';
            echo '<i class="fas fa-times"></i>&nbsp;<strong>Unwear</strong>';
            echo '</button>';
            echo '</div></div></div>';
        }
    }

    echo '</div>';
}