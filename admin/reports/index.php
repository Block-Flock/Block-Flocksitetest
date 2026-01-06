<?php
$title = 'Admin Control Panel';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {
    die();

}

if($AUTH && $my->administrator == 0) {
    die();

}
?>
<section class="section">
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
                            <a class="is-active">Reports</a>
                        </li>
                    </ul>
                    <p class="menu-label">
                        Assets Creation
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/store/create/hats">Hats</a></li>
                        <li><a href="/admin/store/create/face">Faces</a></li>
                        <li><a href="/admin/store/create/gear">Gears</a></li>
                        <li><a href="#">Heads</a></li>
                    </ul>
                    <p class="menu-label">
                        Transactions
                    </p>
                    <ul class="menu-list">
                        <li><a href="/admin/coins">Coins</a></li>
                        <li><a>Verified</a></li>
                        <li><a href="/admin/premium/">Premiun</a></li>
                        <li><a>Prestige</a></li>
                    </ul>
                </aside>
            </div>
              <div class="container">
  <div class="columns">
    <div class="column"></div>
    <div class="column is-10">
    <h1 class="title is-4">Reports</h1>
      <div class="box">
        <table class="table" style="width: 100%">
		<thead>
							<th>Reported User/Comment/Thread/Item/Clan</th>
							<th>Reason</th>
                            <th>Type</th>
      <th>Reporter</th>
						</thead>
  <tbody>
  <?php
								$stmt = $db->prepare("SELECT * FROM reportsystem ORDER BY id DESC;");
								$stmt->execute();
								$count = 0;
								foreach($stmt as $result) {
									$count++;
                  if($result['type'] == 1) {
                    $type = "User";
                  }
                  if($result['type'] == 2) {
                    $type = "Item";
                  }
									echo '<tr><td>' .htmlentities($result['username']).'</td><td>'.htmlentities($result['reason'], ENT_QUOTES, "UTF-8").'</td><td>'.$type.'</td><td>'.htmlentities($result['reporter']).'</td></tr>';
								}
							?>

  </tbody>
</table>
      </div>
    </div>
    <div class="column"></div>
  </div>
</div>
                            </section>