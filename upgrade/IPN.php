<?php

session_start();

// For test payments we want to enable the sandbox mode. If you want to put live

// payments through then this setting needs changing to `false`.

$enableSandbox = false;

 

$pricemultiplier = 1; //1 = 0% off, 0 = 100% off, 0.25 = 75% off, 0.5 = 50% off, etc. Default is 1.

 

// Database settings. Change these for your database configuration.

 $dbConfig = [
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'April253611AA',
            'name' => 'site'
        ];

// PayPal settings. Change these to your account details and the relevant URLs

// for your site.

$paypalConfig = [

    'email' => 'maxtorapple8@gmail.com',

    'return_url' => "https://bricktaria.com/upgrade/success.php",

    'cancel_url' => "https://bricktaria.com/upgrade/canceled.php",

    'notify_url' => "https://bricktaria.com/upgrade/IPN.php"

];

 

$paypalUrl = $enableSandbox ?  'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

 

// Product being purchased.

 

 

// Include Functions

require 'functions.php';

 

// Check if paypal request or response

if (!isset($_POST["txn_id"]) && !isset($_POST["txn_type"])) {

  if (isset($_POST['item_number'])) {

    $pid = intval($_POST['item_number']);

 

        if ($pid == 1) {

      $itemName = 'Standard Premium, one month';

      $itemAmount = 2.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } else if ($pid == 2) {

      $itemName = 'Standard Premium, six months';

      $itemAmount = 14.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } else if ($pid == 3) {

      $itemName = 'Standard Premium, one year';

      $itemAmount = 24.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } else if ($pid == 4) {

      $itemName = 'Platinum Premium, one month';

      $itemAmount = 6.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 5) {

      $itemName = 'Platinum Premium, six months';

      $itemAmount = 39.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 6) {

      $itemName = 'Platinum Premium, one year';

      $itemAmount = 74.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 7) {

      $itemName = 'Epic Premium, one month';

      $itemAmount = 11.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 8) {

      $itemName = 'Epic Premium, six months';

      $itemAmount = 69.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 9) {

      $itemName = 'Epic Premium, one year';

      $itemAmount = 134.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 10) {

      $itemName = '100 Coins';

      $itemAmount = 0.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 11) {

      $itemName = '500 Coins';

      $itemAmount = 4.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 12) {

      $itemName = '1000 Coins';

      $itemAmount = 8.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    }  elseif ($pid == 13) {

      $itemName = '5000 Coins';

      $itemAmount = 39.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } elseif ($pid == 14) {

      $itemName = '10000 Coins';

      $itemAmount = 49.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

        } elseif ($pid == 15) {

      $itemName = '14500 Coins';

      $itemAmount = 56.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    } else {

      $itemName = 'Standard Premium, one month';

      $itemAmount = 2.99 * $pricemultiplier;

            $itemAmount = (round($itemAmount / 0.05) * 0.05 - 0.01);

    }

  } else {

    header("Location: /upgrade/error.php");
    die();

  }

 

    // Grab the post data so that we can set up the query string for PayPal.

    // Ideally we'd use a whitelist here to check nothing is being injected into

    // our post data.

    $data = [];

    foreach ($_POST as $key => $value) {

        $data[$key] = stripslashes($value);

    }

 

    // Set the PayPal account.

    $data['business'] = $paypalConfig['email'];

 

    // Set the PayPal return addresses.

    $data['return'] = stripslashes($paypalConfig['return_url']);

    $data['cancel_return'] = stripslashes($paypalConfig['cancel_url']);

    $data['notify_url'] = stripslashes($paypalConfig['notify_url']);

 

    // Set the details about the product being purchased, including the amount

    // and currency so that these aren't overridden by the form data.

    $data['item_name'] = $itemName;

    $data['amount'] = $itemAmount;

    $data['currency_code'] = 'USD';

 

    // Add any custom fields for the query string.

    //$data['custom'] = USERID;

 

    // Build the query string from the data.

    $queryString = http_build_query($data);

 

    // Redirect to paypal IPN

    header('location:' . $paypalUrl . '?' . $queryString);

    exit();

 

} else {

    // Handle the PayPal response.

 

    // Create a connection to the database.

    $db = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['name']);

 

    // Assign posted variables to local data array.

    $data = [

        'item_name' => $_POST['item_name'],

        'item_number' => $_POST['item_number'],

        'payment_status' => $_POST['payment_status'],

        'payment_amount' => $_POST['mc_gross'],

        'payment_currency' => $_POST['mc_currency'],

        'txn_id' => $_POST['txn_id'],

        'receiver_email' => $_POST['receiver_email'],

        'payer_email' => $_POST['payer_email'],

        'custom' => $_POST['custom'],

    ];

 

    // We need to verify the transaction comes from PayPal and check we've not

    // already processed the transaction before adding the payment to our

    // database.

    if (verifyTransaction($_POST) && checkTxnid($data['txn_id'])) {

        if (addPayment($data) !== false) {

            // Payment successfully added.

            //discord("@everyone Received a payment.\nPost data:\n```\n". print_r($_POST, true) . "\n```", "Payment received.");

        }

    }

}