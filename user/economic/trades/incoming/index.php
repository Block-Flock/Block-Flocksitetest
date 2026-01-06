<?php
$title = "Incoming Trades";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

    header('Location: /');
    die;

}
?>
<section class="section">
<div class="container">
<div class="columns">
<div class="column is-6">
<h5>You give</h5>
<div class="box">
<div class="columns">
<div class="column is-3">
<img src="https://www.bricktaria.com/assets/images/store/ce782b4586a38b9cdbcec918da46e94a.png?r=235802acf34e2818e0021dbf805c1d9c" alt="">
<div style="height:5px"></div>
<span>Classic Black Fedora <span style="color:gray">#69</span></span>
</div>
<div class="column is-3">
<img src="https://www.bricktaria.com/assets/images/store/ce782b4586a38b9cdbcec918da46e94a.png?r=235802acf34e2818e0021dbf805c1d9c" alt="">
<div style="height:5px"></div>
<span>Classic Black Fedora <span style="color:gray">#70</span></span>
</div>
<div class="column is-3">
<img src="https://www.bricktaria.com/assets/images/store/ce782b4586a38b9cdbcec918da46e94a.png?r=235802acf34e2818e0021dbf805c1d9c" alt="">
<div style="height:5px"></div>
<span>Classic Black Fedora <span style="color:gray">#100</span></span>
</div>
<div class="column is-3">
<img src="https://www.bricktaria.com/assets/images/store/ce782b4586a38b9cdbcec918da46e94a.png?r=235802acf34e2818e0021dbf805c1d9c" alt="">
<div style="height:5px"></div>
<span>Classic Black Fedora <span style="color:gray">#100</span></span>
</div>
</div>
<hr>
Total average price: <p style="color:#ffeb3b;"><i class="fas fa-coins"></i>&nbsp;127,791 </a></p>
</div>
<div class="column is-11">
<div class="box">
<h5>Give extra</h5>
<div class="field">
<input class="input" type="number" name="price" placeholder="Add coins...">
</div>
</div>
</div>
</div>
<div class="column is-6">
<h5>You get</h5>
<div class="box">
<div class="columns">
<div class="column is-3">
<img src="https://www.bricktaria.com/assets/images/store/ce782b4586a38b9cdbcec918da46e94a.png?r=235802acf34e2818e0021dbf805c1d9c" alt="">
<div style="height:5px"></div>
<span>Classic Black Fedora <span style="color:gray">#1</span></span>
</div>
<div class="column is-3">
<img src="https://www.bricktaria.com/assets/images/store/ce782b4586a38b9cdbcec918da46e94a.png?r=235802acf34e2818e0021dbf805c1d9c" alt="">
<div style="height:5px"></div>
<span>Classic Black Fedora <span style="color:gray">#2</span></span>
</div>
<div class="column is-3">
<img src="https://www.bricktaria.com/assets/images/store/ce782b4586a38b9cdbcec918da46e94a.png?r=235802acf34e2818e0021dbf805c1d9c" alt="">
<div style="height:5px"></div>
<span>Classic Black Fedora <span style="color:gray">#3</span></span>
</div>
<div class="column is-3">
<img src="https://www.bricktaria.com/assets/images/store/ce782b4586a38b9cdbcec918da46e94a.png?r=235802acf34e2818e0021dbf805c1d9c" alt="">
<div style="height:5px"></div>
<span>Classic Black Fedora <span style="color:gray">#7</span></span>
</div>
</div>
<hr>
Total average price: <p style="color:#ffeb3b;"><i class="fas fa-coins"></i>&nbsp;127,791 </a></p>
</div>
<div class="column is-11">
<div class="box">
<h5>Request extra</h5>
<div class="field">
<input class="input" type="number" name="price" placeholder="Add coins...">
</div>
</div>
</div>
</div>
</div>
<div class="column is-8">
<br>
<div class="columns">
<div class="column is-6">
<button type="submit" class="button is-success  is-fullwidth">Accept</button>
</div>
<div class="column is-6">
<button  type="submit" class="button is-warning  is-fullwidth">Counter</button>
</div>
<div class="column is-6">
<button  type="submit" class="button is-danger  is-fullwidth">Decline</button>
</div>
</div>
</div>
</div>
</div>
<section>