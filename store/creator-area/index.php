<?php
$title = 'Creator Area';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');
if(!$AUTH) {

  header('Location: /log-in');
  die;

}
?>
<br>
<section class="section">
    <div class="container">
<div class="columns">
<div class="column is-4">
</div>
<div class="column is-5">
<div class="box">
 <a class="button is-info is-fullwidth" href="/store/create/shirt"><b>Shirt</b></a>
<br>
    <a class="button is-info  is-fullwidth" href="/store/create/pants"><b>Pants</b></a>
<br>
    <a class="button is-info  is-fullwidth" href="/store/create/tshirt"><b>Tshirt</b></a>

    </div>
            <div class="column">
            </div>
            </div>
    
    </div>
</div>

<?php
$app->footer();
?>
