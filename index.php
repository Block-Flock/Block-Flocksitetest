<?php
$title = "Welcome";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$app->checkAuth(false);

$selectUserCount = $db->prepare("SELECT * FROM `users` ORDER BY `id` DESC LIMIT 1");
$selectUserCount->execute();
$nousers= $selectUserCount->fetch(PDO::FETCH_OBJ);
?>
      <section class="section">
      <div class="container">
<div class="hero-body" style="/* background-image: url('https://media.discordapp.net/attachments/788430429559455764/929782517010165810/Avatoria-avatar-image.png?width=1152&height=268'); */">
      <div class="columns has-text-centered">
          <div class="column">
              <h1 class="title">Join Bricktaria Today!</h1>
              <h2 class="subtitle">A social platform designed to connect players and game creation!</h2>
              <a href="/join" class="button is-info"><b><i class="fas fa-user-plus"></i>&nbsp;Join us for free!</b></a>
              <a href="/log-in" class="button is-success"><b><i class="fas fa-user"></i>&nbsp;Already joined</b></a>
          </div>
      </div>
    </div>
    <main>        <div class="container">
          <!--
          OLD HEADER:
          <div class="hero-body has-text-centered">
            <img src="https://media.discordapp.net/attachments/818558751816155228/925819633548812359/heree.png?width=705&height=200" alt="Bricktaria logo." height="150" width="150">
            <h2 class="subtitle">Join the other 100+ people playing & creating on Bricktaria!</h1>
            <a href="#" class="button is-danger is-small">Already have an account?</a>
            <a href="#" class="button is-primary is-small">Join Bricktaria!</a>
          </div>
          -->
          <div class="columns has-text-centered">
            <div class="column is-one-third">
              <div class="box">
                <h1 class="title"><i class="fas fa-user-astronaut"></i></h1>
                <h2 class="subtitle">Customize your own unique avatar!</h2>
              </div>
            </div>
            <div class="column is-one-third">
              <div class="box">
                <h1 class="title"><i class="fas fa-gamepad"></i></h1>
                <h2 class="subtitle">Play Games!</h2>
              </div>
            </div>
            <div class="column is-one-third">
              <div class="box">
                <h1 class="title"><i class="fas fa-exchange-alt"></i></h1>
                <h2 class="subtitle">Trade!</h2>
              </div>
            </div>
          </div>
          <div style="height: 15px;"></div>
          <p class="has-text-centered is-size-7 mb-0">Join the other <b><?php echo isset($nousers->id) ? number_format($nousers->id) : '0'; ?> players building & exploring on Bricktaria today!</p>
        </div>
      </section>
      <?php
$app->footer();
?>
