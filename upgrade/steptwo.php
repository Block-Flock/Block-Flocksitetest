<?php
$title = "Upgrade | Step Two";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
$mtype = $_GET['t'] ?? 'Standard';
 
if (($mtype != 'Standard') && ($mtype != 'Platinum') && ($mtype != 'Epic')) {
 
}
 
if ($mtype == 'Standard') {
  $title = "Standard Membership";
} else if ($mtype == 'Platinum') {
  $title = "Platinum Membership";
} else if ($mtype == 'gold') {
  $title = "Epic Membership";
} else {
  $mtype = 'Standard';
  $title = "Standard Membership";
}
 
$salemodifier = 1;
 
if ($mtype == "bronze") {
  $omp = 2.99 * $salemodifier;
  $omp = number_format((round($omp / 0.05) * 0.05 - 0.01), 2);
 
  $smp = 14.99 * $salemodifier;
  $smp = number_format((round($smp / 0.05) * 0.05 - 0.01), 2);
 
  $oyp = 24.99 * $salemodifier;
  $oyp = number_format((round($oyp / 0.05) * 0.05 - 0.01), 2);
 
  $onemonthprice = "$$omp";
  $sixmonthsprice = "$$smp";
  $oneyearprice = "$$oyp";
 
  $onemonthipn = 1;
  $sixmonthipn = 2;
  $oneyearipn = 3;
} else if ($mtype == "silver") {
  $omp = 6.99 * $salemodifier;
  $omp = number_format((round($omp / 0.05) * 0.05 - 0.01), 2);
 
  $smp = 39.99 * $salemodifier;
  $smp = number_format((round($smp / 0.05) * 0.05 - 0.01), 2);
 
  $oyp = 74.99 * $salemodifier;
  $oyp = number_format((round($oyp / 0.05) * 0.05 - 0.01), 2);
 
  $onemonthprice = "$$omp";
  $sixmonthsprice = "$$smp";
  $oneyearprice = "$$oyp";
 
  $onemonthipn = 4;
  $sixmonthipn = 5;
  $oneyearipn = 6;
} else {
  $omp = 11.99 * $salemodifier;
  $omp = number_format((round($omp / 0.05) * 0.05 - 0.01), 2);
 
  $smp = 69.99 * $salemodifier;
  $smp = number_format((round($smp / 0.05) * 0.05 - 0.01), 2);
 
  $oyp = 134.99 * $salemodifier;
  $oyp = number_format((round($oyp / 0.05) * 0.05 - 0.01), 2);
 
  $onemonthprice = "$$omp";
  $sixmonthsprice = "$$smp";
  $oneyearprice = "$$oyp";
 
  $onemonthipn = 7;
  $sixmonthipn = 8;
  $oneyearipn = 9;
}
 
?>

<link rel="stylesheet" href="/bulma2.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Varela+Round:300,400,500,700" rel="stylesheet">
 
<div class="container">
  <div class="columns">
    <div class="column"></div>
    <div class="column is-10">
      <div class="box animated slideInRight">
        <h1 class="title"><?php echo $title ?></h1>
        <p>Please choose a duration of the membership!</p><br>
        <div class="columns">
          <div class="column">
            <div class="box">
              <center>
                <b>1 month</b>
              </center>
<center>
  <form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
    <input type="hidden" name="cmd" value="_xclick" />
    <input type="hidden" name="no_note" value="1" />
    <input type="hidden" name="lc" value="US" />
    <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
    <input type="hidden" name="first_name" value="Firstname" />
    <input type="hidden" name="last_name" value="Lastname" />
    <input type="hidden" name="custom" value="<?php echo $_USER['username'] . ":" . $_USER['id'] ?>" />
    <input type="hidden" name="payer_email" value="<?php echo $_USER['email'] ?>" />
    <input type="hidden" name="item_number" value="<?php echo $onemonthipn ?>" / >
    <br><input class="button is-success" name='submit' type="submit" value="Buy for <?php echo $onemonthprice ?>">
  </form>
 
              </center>
            </div>
          </div>
          <div class="column">
            <div class="box">
              <center>
                <b>6 months</b>
              </center>
<center>
  <form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
    <input type="hidden" name="cmd" value="_xclick" />
    <input type="hidden" name="no_note" value="1" />
    <input type="hidden" name="lc" value="US" />
    <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
    <input type="hidden" name="first_name" value="Firstname" />
    <input type="hidden" name="last_name" value="Lastname" />
    <input type="hidden" name="custom" value="<?php echo $_USER['username'] . ":" . $_USER['id'] ?>" />
    <input type="hidden" name="payer_email" value="<?php echo $_USER['email'] ?>" />
    <input type="hidden" name="item_number" value="<?php echo $sixmonthipn ?>" / >
    <br><input class="button is-success" name='submit' type="submit" value="Buy for <?php echo $sixmonthsprice ?>">
  </form>
              </center>
            </div>
          </div>
          <div class="column">
            <div class="box">
              <center>
                <b>12 months</b>
              </center>
<center>
  <form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
    <input type="hidden" name="cmd" value="_xclick" />
              <input type="hidden" name="no_note" value="1" />
              <input type="hidden" name="lc" value="UK" />
              <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
              <input type="hidden" name="first_name" value="Firstname" />
              <input type="hidden" name="last_name" value="Lastname" />
              <input type="hidden" name="custom" value="<?php echo $_USER['username'] . ":" . $_USER['id'] ?>" />
              <input type="hidden" name="payer_email" value="customer@example.com" />
    <input type="hidden" name="item_number" value="<?php echo $oneyearipn ?>" / >
    <br><input class="button is-success" name='submit' type="submit" value="Buy for <?php echo $oneyearprice ?>">
  </form>
              </center>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="column"></div>
  </div>
</div>
 
 