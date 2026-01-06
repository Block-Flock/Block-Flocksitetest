$(document).ready(function() {
  (function($) {
    $.fn.goTo = function() {
          $('html, body').animate({
              scrollTop: $(this).offset().top-75 + 'px'
          }, 'fast');
          return this;
      }
  })(jQuery);

  $("#btn_1").click(function() {
    $("#cat_1").goTo();
  });

  $("#btn_2").click(function() {
    $("#cat_2").goTo();
  });

  $("#btn_3").click(function() {
    $("#cat_3").goTo();
  });

  $("#btn_4").click(function() {
    $("#cat_4").goTo();
  });

  $("#btn_5").click(function() {
    $("#cat_5").goTo();
  });
});
