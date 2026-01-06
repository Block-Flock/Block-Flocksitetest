<?php
$title = "Error";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>
<section class="section">
<div class="container">
<div class="is-centered-vertically">
<div class="columns is-centered">
<div class="column is-half-desktop is-four-fifths-tablet">
<div class="box"><h3 class="is-size-4"><strong>404 Not Found</strong></h3> 
<p>The page you are requesting does not exist, heres a few things you could do. Please contact support if you believe this is not correct.</p>
<div class="push-15"></div> 
<div data-content="HOW ABOUT" class="is-divider"></div> 
<div class="columns is-mobile has-text-centered">
<div class="column is-4">
<a href="/" class="router-link-active">Home</a></div> 
<div class="column is-4"><a href="/forum" class="">Forum</a></div> 
<div class="column is-4"><a href="/store" class="router-link-exact-active router-link-active">Store</a>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<?php
$app->footer();
?>