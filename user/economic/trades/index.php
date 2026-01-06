<?php
$title = "Trades";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    header('Location: /');
    die;

}
?>
<div class="container">
<div class="columns">
<div class="column is-3">
</div>
<div class="column is-8">
<div class="box" id="loginBox">
<div class="tabs is-toggle is-fullwidth">
<a class="nav-item nav-link active">Received</a>
<a class="nav-item nav-link" >Sent</a>
<a class="nav-item nav-link">Completed</a>
</ul>
</div>
</div>
<br>
<div class="box">
<article class="media">
<figure class="media-left">
<div class="avatar_user">
<img class="avatar_userimg" Src="https://www.bricktaria.com/assets/images/avatars/headshots/d1c62c561d8be66b67fa84933377d58a.png?r=b7a2c735a65e153a6c64ea5a79dce3f9" onerror="this.src='/assets/images/avatars/headshots/default.png?r=7fd74a218a16b743d100cd3697de7628'" alt="Could Not Get Profile Image!" width="107px" height="107px" style="">
</figure>
<div class="media-content">
<div class="content">
<h5>Bricktaria</a> sent you a new <a href="https://www.bricktaria.com/user/economic/trades/incoming/">trade</a></h5>
</div>
</div>
</div>
</div>
</div>
<br>