<?php
$title = 'Admin Control Panel';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {

    http_response_code(404);
    die;

}

if($AUTH && $my->administrator == 0) {

    http_response_code(404);
    die;

}
?>
<h2>Asset Moderation</h2>
<form method="post">
  <?php
if (isset($_POST['delete'])) {
    $raw = trim($_POST['ip']);
    $hash = md5($my->ip);
    $query = mysqli_query($db, "INSERT INTO ip_bans (`ip`) VALUES ('$hash')") or die(mysqli_error($db));
    echo "Banned IP $raw.";
  }
  ?>

  IP address: <input type="text" name="ip"><br>
  <input type="submit" name="delete" value="Ban"><br>
</form>
