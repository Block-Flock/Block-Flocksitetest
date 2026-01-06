<?php
$title = 'Transaction';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');
if(!$AUTH) {

    http_response_code(404);
    die;

}
?>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
            </div>
            <div class="column is-6">
            <div class="box">
        <table class="">
		<thead>
							<th>Item Name</th>
							<th>Item Type</th>
                            <th>Buyer</th>
                            <th>Item Price</th>
                            <th>Date</th>
						</thead>
  <tbody>
  <?php
    
								$stmt = $db->prepare("SELECT * FROM user_transaction WHERE buyer_id = ? ORDER BY id DESC;");
                $stmt->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
								$stmt->execute();
								$count = 0;
								foreach($stmt as $result) {
									$count++;
									echo '<tr><td>'.$result['item_name'].'</td><td>'.htmlentities($result['item_type'], ENT_QUOTES, "UTF-8").'</td><td>'.$result['buyer_name'].'</td><td>'.$result['price'].'</td><td>'.timeAgo(date('Y/m/d H:i:s',$result['date'])).'</td></tr>';
								}
							?>

  </tbody>
</table>
      </div>
    </div>
    <div class="column"></div>
  </div>
</div>
<br>
<?php require_once($_SERVER['DOCUMENT_ROOT'].'/private/footer.inc.php'); ?>

















