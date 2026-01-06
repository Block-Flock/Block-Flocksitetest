<?php
$title = "Avatar";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if(!$AUTH) {

header('Location: /log-in');
die;

}

$selectColor = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
$selectColor->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
$selectColor->execute();

if($selectColor->rowCount() == 0) {

die;

}

$sc = $selectColor->fetch(PDO::FETCH_OBJ);


$Colors = ['0.013702, 0.502886, 0.332452' => '31, 188, 156', '0.011612, 0.351533, 0.234551' => '28, 160, 133', '0.027321, 0.603827, 0.162029' => '46, 204, 112', '0.033105, 0.313989, 0.708376' => '51, 152, 219', '0.000000, 0.132868, 0.318547' => '41, 128, 185', '0.371238, 0.124772, 0.520996' => '164, 99, 191','0.270498, 0.056128, 0.417885' => '142, 67, 173', '0.046665, 0.090842, 0.155926' => '61, 85, 110', '0.015996, 0.028426, 0.046665' => '34, 47, 61', '0.887923, 0.558341, 0.005605' => '242, 197, 17', '0.896269, 0.332452, 0.009721' => '243, 156, 25', '0.806952, 0.070360, 0.045186' => '232, 75, 60', '0.527115, 0.039546, 0.024158' => '192, 56, 43', '0.723055, 0.791298, 0.806952' => '241, 241, 241', '0.508881, 0.545725, 0.577581' => '189, 195, 200', '1.000000, 0.708376, 0.412543' => '255, 219, 172', '0.564712, 0.238398, 0.054480' => '198, 134, 66','0, 0, 0,' => '0 0 0'];
?>
<script src="/assets/js/avatar.js?r=<?php echo bin2hex(random_bytes(16)); ?>"></script>
<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3" onmouseover="document.getElementById('clothing_choose_div').style.display = 'none';document.getElementById('accessories_choose_div').style.display = 'none';document.getElementById('clothing_body_div').style.display = 'none';">
                <div class="columns is-mobile is-marginless-bottom is-paddingless-bottom">
                    <div class="column is-6 is-paddingless-bottom">
                        <div class="novello-title is-size-5">Character</div>
                    </div>
                    <div class="column is-6 is-paddingless-bottom">
                    <form method="post">
<button type="submit" style="float:right;" onclick="refreshAvatar(); return false;" class="button is-small is-info is-pulled-right"><b>Refresh</b></button>
</form>
                    </div>
                </div>
                <div class="box has-text-centered" id="characterBox">
                    <img id="avatar" style="width: 100%;" src="/assets/images/avatars/<?php echo $my->avatar_hash; ?>.png" alt="">
</div>
                <div class="novello-title is-size-5">Colors</div>
                <div class="box has-text-centered">
               <div onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('head_color_div').style.display = 'block';document.getElementById('color_head_choose').classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="head"  class="color_head character_color"
                        style="background-color: rgb(<?php echo $Colors[$sc->head]; ?>);"></div>
                    <div class="push-5"></div>
                    <div onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('rightarm_color_div').style.display = 'block';document.getElementById('color_rightarm_choose').classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="right_arm" class="color_limb character_color"
                        style="background-color: rgb(<?php echo $Colors[$sc->right_arm]; ?>);"></div>
                    <div onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('torso_color_div').style.display = 'block';document.getElementById('color_torso_choose').classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="torso" class="color_body character_color"
                        style="background-color: rgb(<?php echo $Colors[$sc->torso]; ?>);"></div>
                    <div onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('leftarm_color_div').style.display = 'block';document.getElementById('color_leftarm_choose').classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="left_arm" class="color_limb character_color"
                        style="background-color: rgb(<?php echo $Colors[$sc->left_arm]; ?>);"></div>
                    <div class="push-character-legs"></div>
                    <div onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('rightleg_color_div').style.display = 'block';document.getElementById('color_rightleg_choose').classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="right_leg" class="color_limb character_color"
                        style="background-color: rgb(<?php echo $Colors[$sc->right_leg]; ?>);"></div>
                    <div onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('leftleg_color_div').style.display = 'block';document.getElementById('color_leftleg_choose').classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="left_leg" class="color_limb character_color"
                        style="background-color: rgb(<?php echo $Colors[$sc->left_leg]; ?>);"></div>
                </div>
            </div>
            <div class="column is-9">
                <div class="novello-title is-size-5">Inventory</div>
                <div class="box">
                    <div class="tabs is-marginless is-centered" id="inventory_tabs">
                    <ul>
		<li id="clothing_hover_div" onmouseover="document.getElementById('clothing_choose_div').style.display = 'block';document.getElementById('accessories_choose_div').style.display = 'none';document.getElementById('body_choose_div').style.display = 'none';" class="is-active"><a>Clothing</a></li>
		<li id="accessories_hover_div" onmouseover="document.getElementById('clothing_choose_div').style.display = 'none';document.getElementById('accessories_choose_div').style.display = 'block';document.getElementById('body_choose_div').style.display = 'none';" class=""><a>Accessories</a></li>
		<li id="body_hover_div" onmouseover="document.getElementById('clothing_choose_div').style.display = 'none';document.getElementById('accessories_choose_div').style.display = 'none';document.getElementById('body_choose_div').style.display = 'block';" class=""><a>Body</a></li>
	</ul>
</div>

<div id="clothing_choose_div" class="tabs is-marginless is-centered" style="display: none;">
	<ul>
		<li onclick=";get_inventory(2, 1);remove_active_avatar_divs();this.classList.add('is-active');document.getElementById('clothing_hover_div').classList.add('is-active');" id="cat2" class=""><a>Faces</a></li>
		<li onclick="get_inventory(3, 1);remove_active_avatar_divs();this.classList.add('is-active');document.getElementById('clothing_hover_div').classList.add('is-active');" id="cat3" class="is-active"><a>Shirts</a></li>
		<li onclick="get_inventory(4, 1);remove_active_avatar_divs();this.classList.add('is-active');document.getElementById('clothing_hover_div').classList.add('is-active');" id="cat4"class=""><a>Pants</a></li>
        <li onclick="get_inventory(8, 1);remove_active_avatar_divs();this.classList.add('is-active');document.getElementById('clothing_hover_div').classList.add('is-active');" id="cat8" class=""><a>Tshirts</a></li>
	</ul>
</div>

<div id="accessories_choose_div" class="tabs is-marginless is-centered" style="display: none;">
	<ul>
		<li onclick="get_inventory(1, 1);remove_active_avatar_divs();this.classList.add('is-active');document.getElementById('accessories_hover_div').classList.add('is-active');" id="cat1" class=""><a>Hats</a></li>
		<li onclick="get_inventory(5, 1);remove_active_avatar_divs();this.classList.add('is-active');document.getElementById('accessories_hover_div').classList.add('is-active');" id="cat5" class=""><a>Gears</a></li>
		<li onclick="get_inventory(6, 1);remove_active_avatar_divs();this.classList.add('is-active');document.getElementById('accessories_hover_div').classList.add('is-active');" id="cat6" class=""><a>Accessories</a></li>
        <li onclick="get_inventory(7, 1);remove_active_avatar_divs();this.classList.add('is-active');document.getElementById('accessories_hover_div').classList.add('is-active');" id="cat7" class=""><a>Heads</a></li>
    </ul>
                    </div>
                    <div id="body_choose_div" class="tabs is-marginless is-centered" style="display: none;">
	<ul>
		<li onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('head_color_div').style.display = 'block';this.classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="color_head_choose"class=""><a>Head</a></li>
		<li onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('torso_color_div').style.display = 'block';this.classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="color_torso_choose" class=""><a>Torso</a></li>
		<li onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('leftarm_color_div').style.display = 'block';this.classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="color_leftarm_choose"class=""><a>Left Arm</a></li>
		<li onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('rightarm_color_div').style.display = 'block';this.classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="color_rightarm_choose" class=""><a>Right Arm</a></li>
		<li onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('leftleg_color_div').style.display = 'block';this.classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="color_leftleg_choose"class=""><a>Left Leg</a></li>
		<li onclick="remove_active_avatar_divs();hide_avatar_divs();document.getElementById('rightleg_color_div').style.display = 'block';this.classList.add('is-active');document.getElementById('body_hover_div').classList.add('is-active');" id="color_rightleg_choose" class=""><a>Right Leg</a></li>
	</ul>
</div>
<br>
<div id="head_color_div" style="display: none;">
<b-modal style="margin-top: -350px;" :active.sync="isHeadModalActive">
<div class="box">
<div class="container">
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(31, 188, 156);
cursor: pointer;
display: inline-block;" onclick="color(1, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(28, 160, 133);
cursor: pointer;
display: inline-block;" onclick="color(2, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(46, 204, 112);
cursor: pointer;
display: inline-block;" onclick="color(3, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(51, 152, 219);
cursor: pointer;
display: inline-block;" onclick="color(4, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(41, 128, 185);
cursor: pointer;
display: inline-block;" onclick="color(5, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(164, 99, 191);
cursor: pointer;
display: inline-block;" onclick="color(6, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(142, 67, 173);
cursor: pointer;
display: inline-block;" onclick="color(7, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(61, 85, 110);
cursor: pointer;
display: inline-block;" onclick="color(8, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(34, 47, 61);
cursor: pointer;
display: inline-block;" onclick="color(9, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(242, 197, 17);
cursor: pointer;
display: inline-block;" onclick="color(10, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(243, 156, 25);
cursor: pointer;
display: inline-block;" onclick="color(11, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(232, 75, 60);
cursor: pointer;
display: inline-block;" onclick="color(12, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(192, 56, 43);
cursor: pointer;
display: inline-block;" onclick="color(13, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(241, 241, 241);
cursor: pointer;
display: inline-block;" onclick="color(14, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(189, 195, 200);
cursor: pointer;
display: inline-block;" onclick="color(15, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(255, 219, 172);
cursor: pointer;
display: inline-block;" onclick="color(16, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(0 0 0);
cursor: pointer;
display: inline-block;" onclick="color(17, 'head');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(198, 134, 66);
cursor: pointer;
display: inline-block;" onclick="color(18, 'head');"></div>
</div>
</div>
</b-modal>
</div>
<div id="torso_color_div" style="display: none;">
<b-modal style="margin-top: -350px;" :active.sync="isTorsoModalActive">
<div class="box">
<div class="container">
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(31, 188, 156);
cursor: pointer;
display: inline-block;" onclick="color(1, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(28, 160, 133);
cursor: pointer;
display: inline-block;" onclick="color(2, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(46, 204, 112);
cursor: pointer;
display: inline-block;" onclick="color(3, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(51, 152, 219);
cursor: pointer;
display: inline-block;" onclick="color(4, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(41, 128, 185);
cursor: pointer;
display: inline-block;" onclick="color(5, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(164, 99, 191);
cursor: pointer;
display: inline-block;" onclick="color(6, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(142, 67, 173);
cursor: pointer;
display: inline-block;" onclick="color(7, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(61, 85, 110);
cursor: pointer;
display: inline-block;" onclick="color(8, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(34, 47, 61);
cursor: pointer;
display: inline-block;" onclick="color(9, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(242, 197, 17);
cursor: pointer;
display: inline-block;" onclick="color(10, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(243, 156, 25);
cursor: pointer;
display: inline-block;" onclick="color(11, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(232, 75, 60);
cursor: pointer;
display: inline-block;" onclick="color(12, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(192, 56, 43);
cursor: pointer;
display: inline-block;" onclick="color(13, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(241, 241, 241);
cursor: pointer;
display: inline-block;" onclick="color(14, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(189, 195, 200);
cursor: pointer;
display: inline-block;" onclick="color(15, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(255, 219, 172);
cursor: pointer;
display: inline-block;" onclick="color(16, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(0 0 0);
cursor: pointer;
display: inline-block;" onclick="color(17, 'torso');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(198, 134, 66);
cursor: pointer;
display: inline-block;" onclick="color(18, 'torso');"></div>
</div>
</div>
</b-modal>
</div>
<div id="leftarm_color_div" style="display: none;">
<b-modal style="margin-top: -350px;" :active.sync="isLArmModalActive">
<div class="box">
<div class="container">
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(31, 188, 156);
cursor: pointer;
display: inline-block;" onclick="color(1, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(28, 160, 133);
cursor: pointer;
display: inline-block;" onclick="color(2, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(46, 204, 112);
cursor: pointer;
display: inline-block;" onclick="color(3, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(51, 152, 219);
cursor: pointer;
display: inline-block;" onclick="color(4, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(41, 128, 185);
cursor: pointer;
display: inline-block;" onclick="color(5, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(164, 99, 191);
cursor: pointer;
display: inline-block;" onclick="color(6, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(142, 67, 173);
cursor: pointer;
display: inline-block;" onclick="color(7, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(61, 85, 110);
cursor: pointer;
display: inline-block;" onclick="color(8, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(34, 47, 61);
cursor: pointer;
display: inline-block;" onclick="color(9, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(242, 197, 17);
cursor: pointer;
display: inline-block;" onclick="color(10, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(243, 156, 25);
cursor: pointer;
display: inline-block;" onclick="color(11, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(232, 75, 60);
cursor: pointer;
display: inline-block;" onclick="color(12, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(192, 56, 43);
cursor: pointer;
display: inline-block;" onclick="color(13, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(241, 241, 241);
cursor: pointer;
display: inline-block;" onclick="color(14, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(189, 195, 200);
cursor: pointer;
display: inline-block;" onclick="color(15, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(255, 219, 172);
cursor: pointer;
display: inline-block;" onclick="color(16, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(0 0 0);
cursor: pointer;
display: inline-block;" onclick="color(17, 'left_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(198, 134, 66);
cursor: pointer;
display: inline-block;" onclick="color(18, 'left_arm');"></div>
</div>
</div>
</b-modal>
</div>
<div id="rightarm_color_div" style="display: none;">
<b-modal style="margin-top: -350px;" :active.sync="isRArmModalActive">
<div class="box">
<div class="container">
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(31, 188, 156);
cursor: pointer;
display: inline-block;" onclick="color(1, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(28, 160, 133);
cursor: pointer;
display: inline-block;" onclick="color(2, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(46, 204, 112);
cursor: pointer;
display: inline-block;" onclick="color(3, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(51, 152, 219);
cursor: pointer;
display: inline-block;" onclick="color(4, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(41, 128, 185);
cursor: pointer;
display: inline-block;" onclick="color(5, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(164, 99, 191);
cursor: pointer;
display: inline-block;" onclick="color(6, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(142, 67, 173);
cursor: pointer;
display: inline-block;" onclick="color(7, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(61, 85, 110);
cursor: pointer;
display: inline-block;" onclick="color(8, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(34, 47, 61);
cursor: pointer;
display: inline-block;" onclick="color(9, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(242, 197, 17);
cursor: pointer;
display: inline-block;" onclick="color(10, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(243, 156, 25);
cursor: pointer;
display: inline-block;" onclick="color(11, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(232, 75, 60);
cursor: pointer;
display: inline-block;" onclick="color(12, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(192, 56, 43);
cursor: pointer;
display: inline-block;" onclick="color(13, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(241, 241, 241);
cursor: pointer;
display: inline-block;" onclick="color(14, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(189, 195, 200);
cursor: pointer;
display: inline-block;" onclick="color(15, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(255, 219, 172);
cursor: pointer;
display: inline-block;" onclick="color(16, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(0 0 0);
cursor: pointer;
display: inline-block;" onclick="color(17, 'right_arm');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(198, 134, 66);
cursor: pointer;
display: inline-block;" onclick="color(18, 'right_arm');"></div>
</div>
</div>
</b-modal>
</div>
<div id="leftleg_color_div" style="display: none;">
<b-modal style="margin-top: -350px;" :active.sync="isLLegModalActive">
<div class="box">
<div class="container">
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(31, 188, 156);
cursor: pointer;
display: inline-block;" onclick="color(1, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(28, 160, 133);
cursor: pointer;
display: inline-block;" onclick="color(2, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(46, 204, 112);
cursor: pointer;
display: inline-block;" onclick="color(3, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(51, 152, 219);
cursor: pointer;
display: inline-block;" onclick="color(4, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(41, 128, 185);
cursor: pointer;
display: inline-block;" onclick="color(5, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(164, 99, 191);
cursor: pointer;
display: inline-block;" onclick="color(6, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(142, 67, 173);
cursor: pointer;
display: inline-block;" onclick="color(7, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(61, 85, 110);
cursor: pointer;
display: inline-block;" onclick="color(8, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(34, 47, 61);
cursor: pointer;
display: inline-block;" onclick="color(9, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(242, 197, 17);
cursor: pointer;
display: inline-block;" onclick="color(10, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(243, 156, 25);
cursor: pointer;
display: inline-block;" onclick="color(11, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(232, 75, 60);
cursor: pointer;
display: inline-block;" onclick="color(12, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(192, 56, 43);
cursor: pointer;
display: inline-block;" onclick="color(13, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(241, 241, 241);
cursor: pointer;
display: inline-block;" onclick="color(14, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(189, 195, 200);
cursor: pointer;
display: inline-block;" onclick="color(15, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(255, 219, 172);
cursor: pointer;
display: inline-block;" onclick="color(16, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(0 0 0);
cursor: pointer;
display: inline-block;" onclick="color(17, 'left_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(198, 134, 66);
cursor: pointer;
display: inline-block;" onclick="color(18, 'left_leg');"></div>
</div>
</div>
</b-modal>
</div>
<div id="rightleg_color_div" style="display: none;">
<b-modal style="margin-top: -350px;" :active.sync="isRLegModalActive">
<div class="box">
<div class="container">
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(31, 188, 156);
cursor: pointer;
display: inline-block;" onclick="color(1, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(28, 160, 133);
cursor: pointer;
display: inline-block;" onclick="color(2, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(46, 204, 112);
cursor: pointer;
display: inline-block;" onclick="color(3, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(51, 152, 219);
cursor: pointer;
display: inline-block;" onclick="color(4, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(41, 128, 185);
cursor: pointer;
display: inline-block;" onclick="color(5, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(164, 99, 191);
cursor: pointer;
display: inline-block;" onclick="color(6, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(142, 67, 173);
cursor: pointer;
display: inline-block;" onclick="color(7, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(61, 85, 110);
cursor: pointer;
display: inline-block;" onclick="color(8, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(34, 47, 61);
cursor: pointer;
display: inline-block;" onclick="color(9, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(242, 197, 17);
cursor: pointer;
display: inline-block;" onclick="color(10, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(243, 156, 25);
cursor: pointer;
display: inline-block;" onclick="color(11, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(232, 75, 60);
cursor: pointer;
display: inline-block;" onclick="color(12, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(192, 56, 43);
cursor: pointer;
display: inline-block;" onclick="color(13, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(241, 241, 241);
cursor: pointer;
display: inline-block;" onclick="color(14, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(189, 195, 200);
cursor: pointer;
display: inline-block;" onclick="color(15, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(255, 219, 172);
cursor: pointer;
display: inline-block;" onclick="color(16, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(0 0 0);
cursor: pointer;
display: inline-block;" onclick="color(17, 'right_leg');"></div>
<div style="width: 42px;
height: 42px;
margin-bottom: 11px;
margin-right: 11px;
border-radius: 11px;
background-color: rgb(198, 134, 66);
cursor: pointer;
display: inline-block;" onclick="color(18, 'right_leg');"></div>
</div>
</div>
</b-modal> 
</div>
<div style="" class="is-boxed is-centered">
<tab-item id="1" label="Hats" icon-pack="fas" icon="hard-hat"></tab-item>
<tab-item id="2" label="Faces" icon-pack="fas" icon="smile"></tab-item>
<tab-item id="3" label="Shirts" icon-pack="fas" icon="tshirt"></tab-item>
<tab-item id="4" label="Pants" icon-pack="fas" icon="socks"></tab-item>
<tab-item id="5" label="Gear" icon-pack="fas" icon="tools"></tab-item> 
<tab-item id="6" label="Accessories" icon-pack="fas" icon="user"></tab-item>                        
<tab-item id="7" label="Heads" icon-pack="fas" icon="user"></tab-item>                        
<tab-item id="8" label="Tshirts" icon-pack="fas" icon="user"></tab-item>                        
</div>
                </div>
                <div class="push-5"></div>
                <div class="novello-title is-size-5">Wearing</div>
                <div class="box">
                    <div id="wearing">
                        
        </div>
    </div>
</section>
<?php
$app->footer();
?>