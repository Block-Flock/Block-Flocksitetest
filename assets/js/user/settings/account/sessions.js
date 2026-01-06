$(document).ready(function() {
  $("#endSessionBtn").click(function() {
    $("#passwordMessage").removeClass("is-success").removeClass("is-danger").empty();
    $("#empty").removeClass("is-success").removeClass("is-danger").empty();
    $("#twostepcodeMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#message").removeClass("is-danger").removeClass("is-success").empty();

    $("#password").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#endSessionBtn").addClass("is-loading").attr("disabled", true);
    $("#twostepcode").removeClass("is-danger").removeClass("is-success").attr("disabled", true);

    $.post("/api/settings/account/sessions/end-sessions", {
      password: $("#password").val(),
      code: $("#twostepcode").val()
    }).done(function(data) {
      if (data == "success") {
        $("#message").addClass("is-success").text("All other sessions have successfully been ended");
        $("#password").val("");
        $("#twostepcode").val("");
      }
      else if (data == "rate-limit") $("#message").addClass("is-danger").text("You have too recently ended your sessions. Please wait before doing this again");
      else if (data == "missing-information") {
        if ($("#password").val().length == 0) {
          $("#password").addClass("is-danger");
          $("#passwordMessage").addClass("is-danger").text("Please enter your password");
        }

        if ($('#twostepcode').length > 0 && $("#twostepcode").val().length == 0) {
          $("#twostepcode").addClass("is-danger");
          $("#twostepcodeMessage").addClass("is-danger").text("Please enter your 6 digit verification code");
        }
      }
      else if (data == "incorrect-password" || data == "password-too-short" || data == "password-too-long" || data == "rate-limit-password") {
        $("#password").addClass("is-danger");
        $("#passwordMessage").addClass("is-danger");

        if (data == "incorrect-password")
          $("#passwordMessage").text("You have entered an incorrect password. Please check and try again");

        if (data == "password-too-short")
          $("#passwordMessage").text("Your password is too short. It should be at least 6 characters long");

        if (data == "password-too-long")
          $("#passwordMessage").text("Your password is too long");

        if (data == "rate-limit-password")
          $("#passwordMessage").text("Due to too many failed password verification attempts, you must wait before using any feature with password verification again ...");
      }else if (data == "no-code" || data == "invalid-code" || data == "wrong-code" || data == "rate-limit-code") {
        $("#twostepcodeMessage").addClass("is-danger");
        $("#twostepCode").addClass("is-danger");

        if (data == "no-code")
          $("#twostepcodeMessage").text("Please enter your 6 digit verification code");

        if (data == "invalid-code")
          $("#twostepcodeMessage").text("Your 6 digit code is invalid. Please check and try again");

        if (data == "wrong-code")
          $("#twostepcodeMessage").text("Your 6 digit code is wrong. Please try again");

        if (data == "rate-limit-code")
          $("#twostepcodeMessage").text("Due to too many invalid attempts, you must wait before trying again");
      }else $("#message").addClass("is-danger").text("Due to a network error, your other sessions could not be ended");
    }).always(function() {
      $("#password").attr("disabled", false);
      $("#endSessionBtn").removeClass("is-loading").attr("disabled", false);
      $("#twostepcode").attr("disabled", false);
    }).fail(function() {
      $("#message").addClass("is-danger").text("Due to a network error, your other sessions could not be ended");
    });
  });
});
