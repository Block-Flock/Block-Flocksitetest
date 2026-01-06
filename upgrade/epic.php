<html>
    <head>
        <?php
            $title = "Upgrade";
            require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>
    </head>
    <body>
        <section class="section">
            <div class="has-announcement">
                <div class="container">
                    <section class="is-small is-bold">
                        <div class="hero-body">
                            <div class="columns is-centered">
                                <div class="container has-text-centered">
                                    <p class="title">Support Bricktaria!</p>
                                    <p class="subtitle push-10">Bricktaria offers coins & membership, revenue made will support Bricktaria's development into the future & get you some cool perks on your Bricktaria account.</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <p class="title"></p>
                    <div class="columns has-text-centered">
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #F3C5C5 !important;"><b>Standard</b></p>
                                <p class="subtitle">$0.00</p>
                                <ul>
                                    <li><b>Perks Include:</b></li>
                                    <li>- 10 Dail Currency</li>
                                    <li>- No 15% tax rate </li>
                                    <li>- No premium badge</li>
<li>- No username color on forums </li>
                                    <li>- No premium accessories</li>
                                    <li>- No discord role</li>
                                </ul>
                                <div style="height: 15px;"></div>
                                <button class="button is-info is-small" disabled>Purchase (active)</button>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #C1A3A3 !important;"><b>Platinum</b></p>
                                <ul>
                                    <li><b>Perks Include:</b></li>
                                    <li>- 30 Daily Currency</li>
                                    <li>- 15% tax rate </li>
                                    <li>- Premium badge</li>
<li>- Username color on forums </li>
                                    <li>- Premium accessories</li>
                                    <li>- Discord role</li>
                                </ul>
                                <div style="height: 10px;"></div>
                                <button class="button is-info is-small" onclick="Select('gold')">Select</button>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #FFD32D !important;"><b>Epic</b></p>
                                <ul>
                                    <li><b>Perks Include:</b></li>
   <li>- 60 Daily Currency</li>
                                    <li>- 15% tax rate </li>
                                    <li>- Premium badge</li>
<li>- Username color on forums </li>
                                    <li>- Premium accessories</li>
                                    <li>- Discord role</li>
                                </ul>
                                <div style="height: 10px;"></div>
                                <button class="button is-info is-small">Select</button>
                            </div>
                        </div>
                    </div>
                    <div class="columns has-text-centered">
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #705800 !important;"><b>100 Coins</b></p>
                                <p class="subtitle">$0.99</p>
                                <div style="height: 10px;"></div>
<form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
  <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="lc" value="UK" />
            <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
            <input type="hidden" name="first_name" value="Firstname" />
            <input type="hidden" name="last_name" value="Lastname" />
            <input type="hidden" name="custom" value="<?php echo $my->username . ":" . $my->id; ?>" />
            <input type="hidden" name="payer_email" value="customer@example.com" />
  <input type="hidden" name="item_number" value="<?php echo 10 ?>" / >
  <br><input class="button is-info is-small" name='submit' type="submit" value="Purchase">
</form>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #b38c00 !important;"><b>500 Coins</b></p>
                                <p class="subtitle">$4.99</p>
                                <div style="height: 10px;"></div>
<form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
  <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="lc" value="UK" />
            <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
            <input type="hidden" name="first_name" value="Firstname" />
            <input type="hidden" name="last_name" value="Lastname" />
            <input type="hidden" name="custom" value="<?php echo $my->username . ":" . $my->id; ?>" />
            <input type="hidden" name="payer_email" value="customer@example.com" />
  <input type="hidden" name="item_number" value="<?php echo 11 ?>" / >
  <br><input class="button is-info is-small" name='submit' type="submit" value="Purchase">
</form>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #533E85 !important;"><b>1,000 Coins</b></p>
                                <p class="subtitle">$8.99</p>
                                <div style="height: 10px;"></div>
<form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
  <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="lc" value="UK" />
            <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
            <input type="hidden" name="first_name" value="Firstname" />
            <input type="hidden" name="last_name" value="Lastname" />
            <input type="hidden" name="custom" value="<?php echo $my->username . ":" . $my->id; ?>" />
            <input type="hidden" name="payer_email" value="customer@example.com" />
  <input type="hidden" name="item_number" value="<?php echo 12 ?>" / >
  <br><input class="button is-info is-small" name='submit' type="submit" value="Purchase">
</form>
                            </div>
                        </div>
                    </div>
                    <div class="columns has-text-centered">
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #533E85 !important;"><b>5,000 Coins</b></p>
                                <p class="subtitle">$39.99</p>
                                <div style="height: 10px;"></div>
                                <form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
  <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="lc" value="UK" />
            <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
            <input type="hidden" name="first_name" value="Firstname" />
            <input type="hidden" name="last_name" value="Lastname" />
            <input type="hidden" name="custom" value="<?php echo $my->username . ":" . $my->id; ?>" />
            <input type="hidden" name="payer_email" value="customer@example.com" />
  <input type="hidden" name="item_number" value="<?php echo 13 ?>" / >
  <br><input class="button is-info is-small" name='submit' type="submit" value="Purchase">
</form>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #533E85 !important;"><b>10,000 Coins</b></p>
                                <p class="subtitle">$49.99</p>
                                <div style="height: 10px;"></div>
                                <form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
  <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="lc" value="UK" />
            <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
            <input type="hidden" name="first_name" value="Firstname" />
            <input type="hidden" name="last_name" value="Lastname" />
            <input type="hidden" name="custom" value="<?php echo $my->username . ":" . $my->id; ?>" />
            <input type="hidden" name="payer_email" value="customer@example.com" />
  <input type="hidden" name="item_number" value="<?php echo 14 ?>" / >
  <br><input class="button is-info is-small" name='submit' type="submit" value="Purchase">
</form>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box">
                                <p class="title" style="color: #533E85 !important;"><b>14,500 Coins</b></p>
                                <p class="subtitle">$56.99</p>
                                <div style="height: 10px;"></div>
                              <form class="paypal" action="/upgrade/IPN.php" method="post" id="paypal_form">
  <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="lc" value="UK" />
            <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
            <input type="hidden" name="first_name" value="Firstname" />
            <input type="hidden" name="last_name" value="Lastname" />
            <input type="hidden" name="custom" value="<?php echo $my->username . ":" . $my->id; ?>" />
            <input type="hidden" name="payer_email" value="customer@example.com" />
  <input type="hidden" name="item_number" value="<?php echo 15 ?>" / >
  <br><input class="button is-info is-small" name='submit' type="submit" value="Purchase">
</form>
                            </div>
                        </div>
                    </div>
                    <p style="font-size: 0.9rem;">* Costs apply.</p>
                    <p style="font-size: 0.9rem;">* None of the paid memberships excempt your account from account moderation of any kind.</p>
                </div>
            </div>
        </section>
    </body>
</html>
    <?php
$app->footer();
?>
















