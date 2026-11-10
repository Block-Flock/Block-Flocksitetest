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
<div class="hero-body glass-box mb-6">
      <div class="columns has-text-centered">
          <div class="column">
              <h1 class="title has-text-primary is-size-1" style="font-weight: 800; text-transform: uppercase;">Join Bricktaria Today!</h1>
              <h2 class="subtitle has-text-light is-size-4">A social platform designed to connect players and game creation!</h2>
              <div class="buttons is-centered mt-5">
                  <a href="/join" class="button is-primary is-large is-rounded"><b><i class="fas fa-user-plus mr-2"></i> Join us for free!</b></a>
                  <a href="/log-in" class="button is-light is-outlined is-large is-rounded"><b><i class="fas fa-user mr-2"></i> Already joined</b></a>
              </div>
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
              <div class="glass-box h-100">
                <h1 class="title has-text-primary"><i class="fas fa-user-astronaut fa-3x"></i></h1>
                <h2 class="subtitle mt-4">Customize your own unique avatar!</h2>
              </div>
            </div>
            <div class="column is-one-third">
              <div class="glass-box h-100">
                <h1 class="title has-text-primary"><i class="fas fa-gamepad fa-3x"></i></h1>
                <h2 class="subtitle mt-4">Play Games!</h2>
              </div>
            </div>
            <div class="column is-one-third">
              <div class="glass-box h-100">
                <h1 class="title has-text-primary"><i class="fas fa-exchange-alt fa-3x"></i></h1>
                <h2 class="subtitle mt-4">Trade!</h2>
              </div>
            </div>
          </div>
          <div style="height: 30px;"></div>
          <p class="has-text-centered is-size-6 mb-0 has-text-grey-light">Join the other <b><?php echo isset($nousers->id) ? number_format($nousers->id) : '0'; ?></b> players building & exploring on Bricktaria today!</p>
        </div>
      </section>
      <?php
$app->footer();
?>
