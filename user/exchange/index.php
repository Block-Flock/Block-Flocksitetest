<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$title = "Exchange";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(true);
	?>
<div class="container">
<div class="col-md-10" style="margin: 0px auto;float:none;">
<?php
function fix_num($num)
{
    $num = str_replace("-", " ", $num);
    $num = floor($num);
    return $num;
}

if (isset($_POST['exchange'])) {
    $type   = htmlentities($_POST['type']);
    $amount = htmlentities($_POST['amount']);
    $amount = fix_num($amount);
    if (is_numeric($amount)) {
        if ($type == "Currency") {
            if ($amount % 10 == 0) {
                if ($my->Currency >= $amount) {
                    $new_amount_tokens = $amount / 10;
                    $db->prepare("UPDATE users SET Currency=Currency-'$amount' WHERE id='$my->id'");
                    $db->prepare("UPDATE users SET cash=cash+'$new_amount_tokens' WHERE id='$my->id'");
                    echo'<script>window.location.replace("/user/exchange");</script>';
                } else {
                    echo "<center><div class='alert alert-danger' >You don't have enough to trade!</div></center>";
                }
                
                
            } else {
                echo "<center><div class='alert alert-danger'>Number must be divisible by 10.</div></center>";
            }
        } else {
            if ($my->cash >= $amount) {
                $new_amount_coins = $amount * 10;
                $db->prepare("UPDATE users SET cash=cash-'$amount' WHERE id='$my->id'");
                $db->prepare("UPDATE users SET Currency=Currency+'$new_amount_coins' WHERE id='$my->id'");
                                    echo'<script>window.location.replace("/user/exchange");</script>';
            } else {
                echo "<center><div class='alert alert-danger'>You dont have enough to trade!</div></center>";
            }
            
            
        }
    } else {
        echo "<center><div class='alert alert-danger'>Please enter a real number.</div></center>";
    }
}
?>

    <div class="card">
    <div class="card-body">
    <h4>Currency</h4>
    <p>You have

<?php
echo "" . $my->cash . " Cash and " . $my->Currency . " Coins";
?>

<form method="POST" action="#">
<div class="form-group" style="margin:0 auto;text-align:center;">
    <label>Exchange currency</label>
	<select class="form-control" name="type">
        <option value="bucks">Cash to Coins</option>
        <option value="coins">Coins to Cash</option>
    </select>
    <label>Enter amount</label>
	<input class="form-control" name="amount" placeholder="Enter a value to exchange">
    <br>
    <button type="submit" class="btn btn-outline-success" name="exchange">Exchange</button>
    </div>
</div>
</form>
</div>
</div>
</div>


