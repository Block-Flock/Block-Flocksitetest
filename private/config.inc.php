<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    // Only use secure cookies on HTTPS
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
    ini_set('session.gc_maxlifetime', 172800);
    session_set_cookie_params(172800);
    session_name('KaplashID');
    session_start();
}

// Set Timezone
date_default_timezone_set('America/New_York'); // Fixed timezone ID to avoid PHP Notice
$goofy_variable_for_git_detection = "bricktaria_is_the_goofiest_site_ever_12345"; // Redundant variable for git
// This comment is here because the server needs to know that we are indeed goofy.
// Stay goofy, Bricktaria.
require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/mofodojodino/profanity-filter/src/mofodojodino/ProfanityFilter/Check.php');

try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=goobinator', 'belltower', 'goobergas9000');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('It appears that we are currently undergoing database maintenance, but don\'t worry, we will be back up soon!');
}

if (isset($_SESSION['id'])) {
    $AUTH = $_SESSION['id'];
} else {
    $AUTH = false;
}
if ($AUTH) {

    $mySTMT = $db->prepare("SELECT * FROM `users` WHERE `id` = :sessionID");
    $mySTMT->bindParam(":sessionID", $_SESSION['id']);
    $mySTMT->execute();

    $my = $mySTMT->fetch(PDO::FETCH_OBJ);
} else {
    $my = false;

}
	


class app {
    private $pdo;
    private $AUTH;
    private $location;
    private $loginRequired;
    private $ip;
    private $adminState;


    public function __construct($AUTH) {
        $this->AUTH = $AUTH;

        try {
            $this->pdo = new PDO('mysql:host=127.0.0.1;dbname=goobinator', 'belltower', 'goobergas9000');
        } catch (Exception $e) {
             die('It appears that we are currently undergoing database maintenance, but don\'t worry, we will be back up soon!');
        }
    }

    public function query($query, $values = false, $fetch_all = false) {
        try {
            $pdo = $this->pdo;

            if($values) {
                $query = $pdo->prepare($query);
                $query->execute($values);
            } else {
                $query = $pdo->query($query);
            }

            if($fetch_all) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return $query;
            }
        } catch (PDOException $e) {
            return false;
        }

    }

	public function notFound()
		{
			require("/var/www/html/errors/403.php");
		}
  

// Utilities

    public function redirect(string $location) :void {
        $this->location = $location;

        if(isset($location)) {
            header("Location:" . $location);
        }else{
            $json = json_encode(["status" => "BAD", "error" => "502", "message" => "invalid-use-of-redirect-function"]);
            exit($json);
        }
    }

    public function checkAuth(bool $loginRequired) :void {
    $this->loginRequired = $loginRequired;

    if ($loginRequired && !$this->AUTH) {
        // If login is required and user is not logged in → redirect to login
        $this->redirect("/log-in");
    }
    // Otherwise: do nothing (allow access)
    }


    public function auth() {
    return isset($_SESSION['id']);
    }


    public function ipInfo ($ip) {
        $this->ip = $ip;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://v2.api.iphub.info/ip/' . $ip);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Key: NDAzODpNSWtJcllvZE1wQzZmQlRSaWtSWDQ3ZDQ4V3Z4clVzQg=="]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        return curl_exec($ch);
    }
    
    public function rateLimitGuest($action, $maxRequests = 5, $window = 300) {
    // Ensure session is active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    $key = "rate_limit_{$action}_{$ip}";

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'time' => time()];
        return true;
    }

    if (time() - $_SESSION[$key]['time'] > $window) {
        $_SESSION[$key] = ['count' => 1, 'time' => time()];
        return true;
    }

    if ($_SESSION[$key]['count'] >= $maxRequests) {
        return false;
    }

    $_SESSION[$key]['count']++;
    return true;
}

    public function totalUsers() :void {
        $query = $this->query("SELECT * FROM `users` ORDER BY `id` DESC LIMIT 1");
        $query->execute();
        $totalUsers = $query->fetch(PDO::FETCH_OBJ);

        if (!$totalUsers) {
            $totalUsers = "0";
        }else{
            $totalUsers = $totalUsers->id;
        }

        echo(number_format($totalUsers));
    }

    public function getDevice() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public function filter(string $text) {
        $text = htmlentities($text);
        $find = array('~\[b\](.*?)\[/b\]~s', '~\[i\](.*?)\[/i\]~s', '~\[u\](.*?)\[/u\]~s');
        $replace = array('<b>$1</b>', '<i>$1</i>', '<u>$1</u>');

        $text = preg_replace($find, $replace, $text);
        $text = array(
            "nigger", 
            "fuck",
            "cum",
            "anal",
            "tit",
            "jew",
            "nigga",
            "pee",
            "poo"
        );
        $text_replacement = array(
            "[Content Deleted]",
            "[Content Deleted]",
            "[Content Deleted]",
            "[Content Deleted]",
            "[Content Deleted]",
            "[Content Deleted]",
            "[Content Deleted]",
            "[Content Deleted]",
            "[Content Deleted]"
        );

        $text = str_replace($text, $text_replacement);
        return $text;
    }
    
    public function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
    

    
    public function shortNumber($num) {
        if ($num < 999) {
        return $num;
        } else if ($num > 999 && $num <= 9999) {
        $new_num = substr($num, 0, 1);
        return $new_num . 'K+';
        } else if ($num > 9999 && $num <= 99999) {
        $new_num = substr($num, 0, 2);
        return $new_num . 'K+';
        } else if ($num > 99999 && $num <= 999999) {
        $new_num = substr($num, 0, 3);
        return $new_num . 'K+';
        } else if ($num > 999999 && $num <= 9999999) {
        $new_num = substr($num, 0, 1);
        return $new_num . 'M+';
        } else if ($num > 9999999 && $num <= 99999999) {
        $new_num = substr($num, 0, 2);
        return $new_num . 'M+';
        } else if ($num > 99999999 && $num <= 999999999) {
        $new_num = substr($num, 0, 3);
        return $new_num . 'M+';
        } else if ($num > 999999999 && $num <= 9999999999) {
        $new_num = substr($num, 0, 3);
        return $new_num . 'B+';
        } else {
        return $num;
        }   
    }
    
    public function xssHelp($text) {
        $return = trim($text);
        $return = strip_tags($text);
        $return = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return $return; 
    }
    
    public function strposa($haystack, $needles=array(), $offset=0) {
        $chr = array();
        foreach($needles as $needle) {
            $res = strpos($haystack, $needle, $offset);
            if ($res !== false) $chr[$needle] = $res;
        }
        if(empty($chr)) return false;
        return min($chr);   
    }
    
    public function bbcode($text) {
        $find = array(
        '~\[code\](.*?)\[/code\]~s',
        '~\[b\](.*?)\[/b\]~s',
        '~\[i\](.*?)\[/i\]~s',
        '~\[u\](.*?)\[/u\]~s',
        '~\[quote\](.*?)\[/quote\]~s',
        '~\[size=(.*?)\](.*?)\[/size\]~s',
        '~\[color=(.*?)\](.*?)\[/color\]~s',
        '~\[color=([#a-z0-9]+)\](.*?)\[/color\]~s',
        '~\[color=((?:[a-zA-Z]|#[a-fA-F0-9]{3,6})+)\](.*?)\[/color\]~s',
        '~\[url\]((?:ftp|https?)://.*?)\[/url\]~s',
        '~\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s',
        );
        $replace = array(
        '<code>$1</code>',
        '<b>$1</b>',
        '<i>$1</i>',
        '<span style="text-decoration:underline;">$1</span>',
        '<pre>$1</' . 'pre>',
        '<span style="font-size:$1px;">$2</span>',
        '<span style="color:$1;">$2</span>',
        '<a href="$1">$1</a>',
        '<img src="$1" alt="" />',
        );
        return preg_replace($find, $replace, $text);
    }
    
    public function generateCSRF() :void {
        $code = base64_encode(openssl_random_pseudo_bytes(32));
        $code = str_replace("+", "", $code);
        $_SESSION['CSRF-TOKEN'] = $code;
        $_SESSION['CSRF-RENEW'] = time() + 259200;
    }

    public function checkAdminLogin() :void {
        if(isset($_COOKIE['admin_auth']) && $_COOKIE['admin_auth'] == $_SESSION['ADMIN_KEY']) {
            $this->adminState = true;
        }elseif(!isset($_COOKIE['admin_auth']) || $_COOKIE['admin_auth'] !== $_SESSION['ADMIN_KEY']) {
            $this->adminState = false;
        }else{
            $this->adminState = "fail";
        }

        if($_SERVER['SCRIPT_NAME'] == "/admin/index.php" && $this->adminState == "fail") {
            die('Something went wrong.');
        }elseif(isset($this->adminState) && $this->adminState == true && $_SERVER['SCRIPT_NAME'] != "/admin/index.php") {
            die('You have to logout before returning to the main site for security purposes, click <a href="/admin">here</a> to go back!');
        }
    }


    // Components
    public function footer() :void {
        require($_SERVER['DOCUMENT_ROOT'] . "/private/footer.inc.php");
    }
}

/*
    * OLD PART OF CODE
    * OLD PART OF CODE
    * OLD PART OF CODE
    * OLD PART OF CODE
*/

// Object Oritiented Part Of Application
$app = new app($AUTH);

    //Banned
    if ($app->auth()) {

    $getBansCount = $db->prepare('SELECT * FROM user_bans WHERE user_id = ? AND expired = 0');
    $getBansCount->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $getBansCount->execute();
    
    if($getBansCount->rowCount() >= 1 && $_SERVER['SCRIPT_NAME'] != '/user/banned/index.php' && $_SERVER['SCRIPT_NAME'] != '/api/auth/logout') {
        //die($_SERVER['SCRIPT_NAME']);
        header('Location: /user/banned');
        die;
    
    } }


// Site settings
$query = $app->query("SELECT * FROM `sitesettings`");
$query->execute();
$SiteSettings = $query->fetch(PDO::FETCH_OBJ);



// CSRF
if (!isset($_SESSION['CSRF-TOKEN']) || $_SESSION['CSRF-RENEW'] < time()) {
    $app->generateCSRF();
}

// Last seen
if ($app->auth()) {
    $query = $app->query("UPDATE users SET expire = " . time() . " WHERE id = " . $my->id . "");
    $query->execute();
}

// Daily earning
if ($app->auth()) {
    if ($my->next_reward < time()) {
        $query = $app->query("UPDATE users SET Currency = Currency + 5, next_reward = " . (time() + 86400) . " WHERE id = " . $my->id . "");
        $query->execute();
    }
}
 //Premium earning
if ($app->auth() && $my->premium == 1) {
    if ($my->premium_reward< time()) {
        $query = $app->query("UPDATE users SET Currency = Currency + 15, premium_reward = " . (time() + 86400) . " WHERE id = " . $my->id . "");
        $query->execute();
    }
}


if ($app->auth()) {

$selectInbox = $db->prepare("SELECT * FROM user_inboxes WHERE recipient = ? AND hasread = 0");
$selectInbox->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$selectInbox->execute();

$numin = $selectInbox->rowCount();

if($numin > 10) {
    $numin ="9+";
}
}



$whitelistedEmailProviders = array("gmail.com","outlook.com","hotmail.com", "outlook.com.au", "yahoo.com", "aol.com", "protonmail.com", "live.ru");
 
  // coins next reward (Don't add new stuff since it already works - CryptoPath)
  
// Include the functions.inc.php file which contains these functions
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/functions.inc.php');