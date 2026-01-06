$(document).ready(function() {
  $("#changeEmailBtn").click(function() {
    $("#passwordMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#newEmailMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#newEmailConfirmMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#twostepcodeMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#message").removeClass("is-danger").removeClass("is-success").empty();

    $("#password").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#newEmail").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#newEmailConfirm").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#twostepcode").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#changeEmailBtn").addClass("is-loading").attr("disabled", true);

    $.post("/api/settings/account/email/change-email", {
      newEmail: $("#newEmail").val(),
      newEmailConfirm: $("#newEmailConfirm").val(),
      password: $("#password").val(),
      code: $("#twostepcode").val()
    }).done(function(data) {
      if (data == "rate-limit") $("#message").addClass("is-danger").text("You have too recently changed your e-mail. Please wait before doing this again.");
      else if (data == "rate-limit-password") $("#message").addClass("is-danger").text("Due to too many failed password verification attempts, you must wait before using any feature with password verification again ...");
      else if (data == "missing-information") {
        if ($("#password").val().length == 0) {
          $("#password").addClass("is-danger");
          $("#passwordMessage").addClass("is-danger").text("Please enter your password");
        }

        if ($("#newEmail").val().length == 0) {
          $("#newEmail").addClass("is-danger");
          $("#newEmailMessage").addClass("is-danger").text("Please enter your new e-mail address");
        }

        if ($("#newEmailConfirm").val().length == 0) {
          $("#newEmailConfirm").addClass("is-danger");
          $("#newEmailConfirmMessage").addClass("is-danger").text("Please confirm your new e-mail address");
        }

        if ($('#twostepcode').length > 0 && $("#twostepcode").val().length == 0) {
          $("#twostepcode").addClass("is-danger");
          $("#twostepcodeMessage").addClass("is-danger").text("Please enter your 6 digit verification code");
        }
      }else if (data == "no-new-email" || data == "new-email-too-long" || data == "invalid-email" || data == "email-taken" || data == "email-unchanged" || data == "invalid-domain") {
        $("#newEmail").addClass("is-danger");

        if (data == "no-new-email")
          $("#newEmailMessage").addClass("is-danger").text("Please enter your new e-mail address");

        if (data == "new-email-too-long")
          $("#newEmailMessage").addClass("is-danger").text("Your new e-mail address is too long. It should not be longer than 128 characters");

        if (data == "invalid-email")
          $("#newEmailMessage").addClass("is-danger").text("Your new e-mail address is invalid. Please check and try again");

        if (data == "email-taken")
          $("#newEmailMessage").addClass("is-danger").text("This e-mail address has been taken by another account. Please use another");

        if (data == "email-unchanged")
          $("#newEmailMessage").addClass("is-danger").text("The e-mail address you have specified is your current e-mail address. You can not change your e-mail address to your current one");

        if (data == "invalid-domain")
          $("#newEmailMessage").addClass("is-danger").html('This e-mail provider seems to be unregistered in our whitelist. Please use a known e-mail provider such as <a target="_blank" href="http://gmail.com">gmail</a> or <a target="_blank" href="http://outlook.com">outlook</a> or e-mail us at <a href="mailto:support@novello.xyz">support@novello.xyz</a> if you believe this is a mistake');
      }else if (data == "no-email-confirm" || data == "email-confirmation-failed") {
        $("#newEmailConfirm").addClass("is-danger");

        if (data == "no-email-confirm")
          $("#newEmailConfirmMessage").addClass("is-danger").text("Please confirm your new e-mail address");

        if (data == "email-confirmation-failed") {
          $("#newEmail").addClass("is-danger");
          $("#newEmailConfirmMessage").addClass("is-danger").text("Your new e-mail and new e-mail confirmation do not match. Please check and try again");
        }
      }else if (data == "no-password" || data == "password-too-long" || data == "incorrect-password") {
        $("#password").addClass("is-danger");

        if (data == "no-password")
          $("#passwordMessage").addClass("is-danger").text("Please enter your password");

        if (data == "password-too-long")
          $("#passwordMessage").addClass("is-danger").text("Your password is too long");

        if (data == "incorrect-password")
          $("#passwordMessage").addClass("is-danger").text("You have entered an incorrect password. Please check and try again");
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
      }else if (data == "success") {
        $("#password").val("");
        $("#newEmail").val("");
        $("#newEmailConfirm").val("");
        $("#twostepcode").empty();
        document.location.reload();
      }else $("#message").addClass("is-danger").text("Due to a network error, your e-mail address could not be changed.");
    }).always(function() {
      $("#password").attr("disabled", false);
      $("#twostepcode").attr("disabled", false);
      $("#newEmail").attr("disabled", false);
      $("#newEmailConfirm").attr("disabled", false);
      $("#changeEmailBtn").removeClass("is-loading").attr("disabled", false);
    }).fail(function() {
      $("#message").addClass("is-danger").text("Due to a network error, your e-mail address could not be changed.");
    });
  });
});
