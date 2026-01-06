<?php $title = 'Admin Control Panel';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
if(!$AUTH) {

    http_response_code(404);
    die;

}
if($AUTH && $my->administrator == 0) {

    http_response_code(404);
    die;

}
?>
<div class="container">
        <div class="columns">
            <div class="column is-3 ">
                <aside class="menu">
                    <div class="box">
                    <p class="menu-label">
                        General
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/">Dashboard</a></li>
                    </ul>
                    <p class="menu-label">
                        Administration
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/review">Reviews</a></li>
                        <li>
                            <a href="/admin/reports/">Reports</a>
                        </li>
                      <li>
                            <a class="is-active">Ad Managment</a>
                        </li>
                    </ul>
                    <p class="menu-label">
                        Assets Creation
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/store/create/hats">Hats</a></li>
                        <li><a href="/admin/store/create/face">Faces</a></li>
                        <li><a href="#">Gears</a></li>
                        <li><a href="#">Heads</a></li>
                    </ul>
                    <p class="menu-label">
                        Transactions
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/coins">Coins</a></li>
                        <li><a>Verified</a></li>
                        <li><a href="/admin/premium">Premiun</a></li>
                        <li><a>Prestige</a></li>
                    </ul>
                </aside>
            </div>
              
              
            
</div>
