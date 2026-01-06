<?php
$title = "Canceled";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>
  <section class="section">
<div class="container">
<div class="columns">
<div class="column"></div>
<div class="column is-half-desktop is-four-fifths-tablet">
<article class="message is-danger">
<div class="message-header">
<p>Payment Canceled</p>
</div>
<div class="message-body">
Your purchase failed because something went wrong. You have not been charged, Please try again later.
</div>
<a href="/dashboard/"  class="button is-fullwidth is-danger" style="border-radius: 0px;">Return to home</a>
</div>
</article>
<div class="column"></div>
</div>
</div>
</section>
<?php
$app->footer();
?>
