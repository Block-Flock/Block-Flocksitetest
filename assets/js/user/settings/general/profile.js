$(document).ready(function() {
  $("#saveBlurb").click(function() {
    $("#saveBlurb").attr("disabled", true).addClass("is-loading");
    $("#blurbContent").attr("disabled", true);
    $("#blurbMessage").empty().removeClass("is-danger").removeClass("is-success");
    $.post("/api/settings/general/profile/change-blurb/", {
      content: $("#blurbContent").val(),
      csrf: $("#csrf").val()
    }).done(function(data) {
      if (data == "success")
        $("#blurbMessage").addClass("is-success").text("Your blurb has been successfully updated.");
      else if (data.substr(0, 10) == "bad-blurb-")
        $("#blurbMessage").addClass("is-danger").text('Your blurb contains a bad word. Please remove "'+data.substr(10)+'" and try again.');
      else if (data == "email-not-verified")
        $("#blurbMessage").addClass("is-danger").text('You must verify your e-mail address before adding a blurb.');
      else $("#blurbMessage").addClass("is-danger").text("Due to a network error, your blurb could not be updated.");
    }).always(function() {
      $("#saveBlurb").attr("disabled", false).removeClass("is-loading");
      $("#blurbContent").attr("disabled", false);
    }).fail(function() {
      $("#blurbMessage").addClass("is-danger").text("Due to a network error, your blurb could not be updated.");
    });
  });
});
