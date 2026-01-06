<html>
  <head>
    <?php
      $title = "Dashboard";
      require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
    ?>
  </head>
  <body>
    <section class="section">
      <div class="container">
        <div class="site-announcement">
          <div class="box has-background-danger has-text-white-bis has-text-centered has-shadow novelloAnnouncement" style="margin-bottom: 2.5rem;">
            <div class="container">
              <p><b>This page is not the final design of the rebuilt account dashboard.</b></p>
            </div>
          </div>
        </div>
        <div class="columns">
          <div class="column is-1"></div>
          <div class="column is-3">
            <div class="novello-title is-size-5 usercontent">Bricktaria</div>
            <div class="box has-text-centered">
              <div class="card-body">
                <img src="/assets/images/avatars/50c677477459c71f6608211f5a34b157.png?r=33f31c6764e7f83b5475600836398ed2">
                <p class="usercontent is-size-6 has-text-grey-light">Good evening, Bricktaria.</p>
              </div>
            </div>
          </div>
          <div class="column">
            <div class="novello-title is-size-5 usercontent">User Feed</div>
            <div class="box has-text-centered">
              <div class="card-body">
                <div class="field has-addons">
                  <div class="control is-expanded">
                    <input type="text" class="input" placeholder="Whatcha' up to, Bricktaria?">
                  </div>
                  <div class="control">
                    <button class="button is-primary">Update Status</button>
                  </div>
                </div>
                <hr>
                <article class="media">
                  <div class="media-left">
                    <figure class="image is-64x64">
                      <img src="/assets/images/avatars/50c677477459c71f6608211f5a34b157.png?r=33f31c6764e7f83b5475600836398ed2">
                    </figure>
                  </div>
                  <div class="media-content">
                    <div class="content">
                      <p>
                        <strong>Bricktaria</strong> <small><i class="fa-solid fa-clock"></i> 2 Hours Ago</small>
                        <br>
                        Very cool new, rebuilt dashboard.
                      </p>
                    </div>
                    <nav class="level is-mobile">
                      <div class="level-left">
                        <a class="level-item" aria-label="reply">
                          <span class="icon is-small">
                            <i class="fas fa-reply" aria-hidden="true"></i>
                          </span>
                        </a>
                        <a class="level-item" aria-label="retweet">
                          <span class="icon is-small">
                            <i class="fas fa-retweet" aria-hidden="true"></i>
                          </span>
                        </a>
                        <a class="level-item" aria-label="like">
                          <span class="icon is-small">
                            <i class="fas fa-heart" aria-hidden="true"></i>
                          </span>
                        </a>
                      </div>
                    </nav>
                  </div>
                </article>
              </div>
            </div>
          </div>
          <div class="column is-1"></div>
        </div>
      </div>
    </section>
  </body>
</html>