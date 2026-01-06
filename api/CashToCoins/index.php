<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

$rate = 15;

if (!$AUTH) {
  header("Location: /log-in");
  exit();
}

$input = $_POST['inputamt'] ?? 0;
$input = intval($input);

if ($input < 0) {
  $input = 0;
}

$output = $input * $rate;

if ($my->cash < $input) {
  header("Location: /user/exchange");
  die();
}
$upd = $db->prepare("UPDATE users SET `cash` = `cash` - '$input', `Currency` = `Currency` + '$output' WHERE id='{$_SESSION['id']}'");
$upd->execute();
header("Location: /user/exchange");
die();
?>
