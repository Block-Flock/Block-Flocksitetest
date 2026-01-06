$(document).ready(function() {
  $(".themeButton").click(function() {
    var btn = $(this);
    btn.addClass("is-loading");
    $(".themeButton").attr("disabled", true);
    $.post("/api/settings/other/theme/", {
      theme: btn.attr("data-theme"),
      csrf: $("#csrf").val()
    }).done(function(data) {
      if (data == "success") window.location.reload();
      else if (data == "unchanged") $("#message").addClass("is-danger").text("You can not change your theme to your current theme.");
      else $("#message").addClass("is-danger").text("Due to a network error, your theme could not be changed.");
    }).always(function() {
      btn.removeClass("is-loading").attr("disabled", false);
    }).fail(function() {
      $("#message").addClass("is-danger").text("Due to a network error, your theme could not be changed.");
    });
  });
});
