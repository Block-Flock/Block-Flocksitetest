</main>

<style>
  html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
    background-color: #f4f5f7;
    font-family: 'Segoe UI', sans-serif;
  }

  main {
    flex: 1 0 auto;
  }

  footer.footer {
    background-color: #1f1f1f;
    color: #ffffff;
    padding: 2rem 1.5rem;
  }

  .novello-footer-link-title {
    font-weight: bold;
    color: #e6e6e6;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
  }

  .novello-footer-link a {
    color: #c0c0c0;
    text-decoration: none;
    display: block;
    margin-bottom: 0.25rem;
    transition: color 0.2s ease;
  }

  .novello-footer-link a:hover {
    color: #ffffff;
  }

  #footer_copyright {
    color: #aaaaaa;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    display: block;
  }
</style>

<footer class="footer">
  <div class="container">
    <div class="content has-text-centered">
      <strong id="footer_copyright">Copyright &copy; 2025 Bricktaria, Inc. All rights reserved.</strong>
    </div>
    <div class="content has-text-centered">
      <div class="columns is-multiline is-mobile">
        <div class="column is-one-quarter">
          <div class="novello-footer-link-title">Legal</div>
          <p class="novello-footer-link"><a href="/legal/terms">Terms</a></p>
          <p class="novello-footer-link"><a href="/legal/privacy">Privacy</a></p>
          <p class="novello-footer-link"><a href="/legal/dmca">DMCA</a></p>
        </div>
        <div class="column is-one-quarter">
          <div class="novello-footer-link-title">Social Media</div>
          <p class="novello-footer-link"><a href="https://twitter.com">Twitter</a></p>
          <p class="novello-footer-link"><a href="https://discord.gg/bloxcity">Discord</a></p>
          <p class="novello-footer-link"><a href="#">YouTube</a></p>
        </div>
        <div class="column is-one-quarter">
          <div class="novello-footer-link-title">Support</div>
          <p class="novello-footer-link"><a href="#">Help</a></p>
          <p class="novello-footer-link"><a href="#">Contact Us</a></p>
          <p class="novello-footer-link"><a href="#">Parental Guidance</a></p>
        </div>
        <div class="column is-one-quarter">
          <div class="novello-footer-link-title">Navigation</div>
          <p class="novello-footer-link"><a href="/">Home</a></p>
          <p class="novello-footer-link"><a href="/forum">Forum</a></p>
          <p class="novello-footer-link"><a href="/store">Store</a></p>
        </div>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
<script src="https://unpkg.com/buefy/dist/buefy.min.js"></script>
<script>
  new Vue({
    el: '#app',
    data() {
      return {
        isLoading: false,
        isFullPage: true,
        isShowModal: false,
        isShowModalUpdates: false,
        isShowModalMenu: false
      }
    },
    created() {
      this.isLoading = true;
      setTimeout(() => {
        this.isLoading = false;
      }, 10);
    }
    <?php if (isset($title) && $title == 'Forum') { ?>,
    methods: {
      onChange(value) {
        if (value === 0) this.getSub(1);
        else if (value === 1) this.getSub(2);
        else if (value === 2) this.getSub(3);
      },
      getSub(id) {
        $.post('/api/forum/get-forum/', { id: id }).done(function(data) {
          $("#" + id).html(data);
        });
      }
    }
    <?php } else if (isset($title) && $title == 'Store') { ?>,
    methods: {
      onChange(value) {
        this.getItems(value + 1);
      },
      getItems(id) {
        $.post('/api/store/get-items/', { id: id }).done(function(data) {
          $("#" + id).html(data);
        });
      }
    }
    <?php } ?><?php if (isset($title) && $title == 'Avatar') { ?>,
    data() {
      return {
        isLoading: false,
        isFullPage: true,
        isHeadModalActive: false,
        isRArmModalActive: false,
        isLArmModalActive: false,
        isTorsoModalActive: false,
        isRLegModalActive: false,
        isLLegModalActive: false,
        color: null
      }
    },
    methods: {
      onChange(value) {
        this.get_inventory(value + 1);
      },
      get_inventory(category) {
        $.post('/api/rendering/get-inventory.php', { category: category }).done(function(data) {
          $("#" + category).html(data);
        });
      }
    }
    <?php } ?>
  });
</script>
<?php ob_flush(); ?>
