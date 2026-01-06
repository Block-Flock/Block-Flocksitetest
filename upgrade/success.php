<?php
$title = "Successful";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

?>
 <section class="section">
<div class="container">
<div class="columns">
<div class="column"></div>
<div class="column is-half-desktop is-four-fifths-tablet">
<article class="message is-success">
<div class="message-header">
<p>Payment Successful</p>
</div>
<div class="message-body">
Thank You for your purchase!
Please allow up to 24 hours for the upgrades or changes you have
purchased to be applied to your account.
We hope you find your time at Bricktaria to be both safe and fun. If there is anything we can do to
make your experience even better, please let us know
if you are experiencing any issues or have any questions.
</div>
<a href="/dashboard/"  class="button is-fullwidth is-success" style="border-radius: 0px;">Return to home</a>
</div>
</article>
<div class="column"></div>
</div>
</div>
</section>
<?php
$app->footer();
?>



