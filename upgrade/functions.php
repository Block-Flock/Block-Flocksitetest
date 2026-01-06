<?php

 

/**

 * Verify transaction is authentic

 *

 * @param array $data Post data from Paypal

 * @return bool True if the transaction is verified by PayPal

 * @throws Exception

 */

function verifyTransaction($data) {

    global $paypalUrl;

 

    $req = 'cmd=_notify-validate';

    foreach ($data as $key => $value) {

        $value = urlencode(stripslashes($value));

        $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value); // IPN fix

        $req .= "&$key=$value";

    }

 

    $ch = curl_init($paypalUrl);

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

    curl_setopt($ch, CURLOPT_SSLVERSION, 6);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

    $res = curl_exec($ch);

 

    if (!$res) {

        $errno = curl_errno($ch);

        $errstr = curl_error($ch);

        curl_close($ch);

        throw new Exception("cURL error: [$errno] $errstr");

    }

 

    $info = curl_getinfo($ch);

 

    // Check the http response

    $httpCode = $info['http_code'];

    if ($httpCode != 200) {

        throw new Exception("PayPal responded with http code $httpCode");

    }

 

    curl_close($ch);

 

    return $res === 'VERIFIED';

}

 

/**

 * Check we've not already processed a transaction

 *

 * @param string $txnid Transaction ID

 * @return bool True if the transaction ID has not been seen before, false if already processed

 */

function checkTxnid($txnid) {

    global $db;

 

    $txnid = $db->real_escape_string($txnid);

    $results = $db->query('SELECT * FROM `payments` WHERE txnid = \'' . $txnid . '\'');

 

    return ! $results->num_rows;

}

 

function discord($message, $username)

{

$data = array(

  "content" => $message,

  "username" => $username

);

$curl = curl_init("https://discord.com/api/webhooks/934909456695840789/pSrkj1C2rjPD9sKaWu99IVXBCqubSh0wSEmJmbkpCn_FH_cb_jmP5KId-I0T732ToBFv");

curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

return curl_exec($curl);

}

 

/**

 * Add payment to database

 *

 * @param array $data Payment data

 * @return int|bool ID of new payment or false if failed

 */

function addPayment($data) {

    global $db;

 

    if (is_array($data)) {

        $custom = explode(":", $data['custom']);

    $user_name = $custom[0];

    $user_id = intval($custom[1]);

 

        $stmt = $db->prepare('INSERT INTO `payments` (txnid, payment_amount, payment_status, itemid, createdtime, userid) VALUES(?, ?, ?, ?, ?, ?)');

        $stmt->bind_param(

            'sdsssi',

            $data['txn_id'],

            $data['payment_amount'],

            $data['payment_status'],

            $data['item_number'],

            date('Y-m-d H:i:s'),

            $user_id

        );

        $stmt->execute();

        $stmt->close();

 

        $is_currency_purchase = false;

 

    if ($data['item_number'] == 1) {

      $level = "LEVEL_1";

      $expire = time() + (86400 * 31);

            $awarditem = 282;

    } else if ($data['item_number'] == 2) {

      $level = "LEVEL_1";

      $expire = time() + (86400 * 31 * 6);

            $awarditem = 282;

    } else if ($data['item_number'] == 3) {

      $level = "LEVEL_1";

      $expire = time() + (86400 * 31 * 12);

            $awarditem = 282;

    } else if ($data['item_number'] == 4) {

      $level = "LEVEL_2";

      $expire = time() + (86400 * 31);

            $awarditem = 281;

    } else if ($data['item_number'] == 5) {

      $level = "LEVEL_2";

      $expire = time() + (86400 * 31 * 6);

            $awarditem = 281;

    } else if ($data['item_number'] == 6) {

      $level = "LEVEL_2";

      $expire = time() + (86400 * 31 * 12);

            $awarditem = 281;

    } else if ($data['item_number'] == 7) {

      $level = "LEVEL_3";

      $expire = time() + (86400 * 31);

            $awarditem = 280;

    } else if ($data['item_number'] == 8) {

      $level = "LEVEL_3";

      $expire = time() + (86400 * 31 * 6);

            $awarditem = 280;

    } else if ($data['item_number'] == 9) {

      $level = "LEVEL_3";

      $expire = time() + (86400 * 31 * 12);

            $awarditem = 280;

    } else if ($data['item_number'] == 10) {

            $brick_amt = 100;

            $is_currency_purchase = true;

    } else if ($data['item_number'] == 11) {

            $brick_amt = 500;

            $is_currency_purchase = true;

    } else if ($data['item_number'] == 12) {

            $brick_amt = 1000;

            $is_currency_purchase = true;

    } else if ($data['item_number'] == 13) {

            $brick_amt = 5000;

            $is_currency_purchase = true;

     } else if ($data['item_number'] == 14) {

            $brick_amt = 10000;

            $is_currency_purchase = true;

     } else if ($data['item_number'] == 15) {

            $brick_amt = 14500;

            $is_currency_purchase = true;

     } else {

      $level = "LEVEL_1";

      $expire = time() + (86400 * 31);

            $awarditem = 280;

    }

 

      $databaseconf = [
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'Wsb6[:aQgQ^)G}xxnjdjdjjdu,_',
            'name' => '/BcAEUj&y6._&spM'
        ];

 

        $con = mysqli_connect($databaseconf['host'], $databaseconf['username'], $databaseconf['password']);

        mysqli_select_db($con, $databaseconf['name']);

 

        if (!$is_currency_purchase) {

            $upduser = mysqli_query($con, "UPDATE users SET `premium`='$level', `premium_expired`='$expire' WHERE id='$user_id'");

        } else {

            $upduser = mysqli_query($con, "UPDATE users SET `Currency` = `Currency` + '$brick_amt' WHERE id='$user_id'");

        }

 

 

 

        return $db->insert_id;

    }

 

    return false;

}






