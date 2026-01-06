<?php
$title = "Unauthorized";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

?>
 <section class="section">
<div class="container">
<div class="columns">
<div class="column"></div>
<div class="column is-half-desktop is-four-fifths-tablet">
<article class="message is-danger">
<div class="message-header">
<p>Unauthorized to view this page</p>
</div>
<div class="message-body">
You cannot view this page without having a payment seleteced, please go back and select a payment.
</div>
<a href="/upgrade/"  class="button is-fullwidth is-danger" style="border-radius: 0px;">Choose a payment</a>
</div>
</article>
<div class="column"></div>
</div>
</div>
</section>
<?php
$app->footer();
?>

