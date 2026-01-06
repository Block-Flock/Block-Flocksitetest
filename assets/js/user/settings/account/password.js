$(document).ready(function() {
  $("#changePasswordBtn").click(function() {
    $("#newPasswordMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#newPasswordConfirmMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#passwordMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#twostepcodeMessage").removeClass("is-danger").removeClass("is-success").empty();
    $("#message").removeClass("is-danger").removeClass("is-success").empty();

    $("#newPassword").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#newPasswordConfirm").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#password").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#twostepcode").removeClass("is-danger").removeClass("is-success").attr("disabled", true);
    $("#changePasswordBtn").addClass("is-loading").attr("disabled", true);

    $.post("/api/settings/account/password/change-password", {
      newPassword: $("#newPassword").val(),
      confirmNewPassword: $("#newPasswordConfirm").val(),
      currentPassword: $("#password").val(),
      code: $("#twostepcode").val()
    }).done(function(data) {
      if (data == "rate-limit") $("#message").addClass("is-danger").text("You have changed your password too recently. Please wait before doing so again.");
      else if (data == "rate-limit-password") $("#message").addClass("is-danger").text("Due to too many failed password verification attempts, you must wait before using any feature with password verification again ...");
      else if (data == "missing-information") {
        if ($("#newPassword").val().length == 0) {
          $("#newPassword").addClass("is-danger");
          $("#newPasswordMessage").addClass("is-danger").text("Please enter your new password");
        }

        if ($("#newPasswordConfirm").val().length == 0) {
          $("#newPasswordConfirm").addClass("is-danger");
          $("#newPasswordConfirmMessage").addClass("is-danger").text("Please confirm your new password");
        }

        if ($("#password").val().length == 0) {
          $("#password").addClass("is-danger");
          $("#passwordMessage").addClass("is-danger").text("Please enter your current password");
        }

        if ($('#twostepcode').length > 0 && $("#twostepcode").val().length == 0) {
          $("#twostepcode").addClass("is-danger");
          $("#twostepcodeMessage").addClass("is-danger").text("Please enter your 6 digit verification code");
        }
      }else if (data == "no-password" || data == "password-too-long" || data == "incorrect-password") {
        $("#password").addClass("is-danger");
        $("#passwordMessage").addClass("is-danger")

        if (data == "no-password")
          $("#passwordMessage").text("Please enter your current password");

        if (data == "password-too-long")
          $("#passwordMessage").text("Your current password is too long");

        if (data == "incorrect-password")
          $("#passwordMessage").text("This password is incorrect. Please check and try again");
      }else if (data == "no-new-password" || data == "new-password-too-short" || data == "new-password-too-long" || data == "passwords-cant-match" || data == "password-complexity-error") {
        $("#newPassword").addClass("is-danger");
        $("#newPasswordMessage").addClass("is-danger");

        if (data == "no-new-password")
          $("#newPasswordMessage").text("Please enter your new password");

        if (data == "new-password-too-short")
          $("#newPasswordMessage").text("Your new password is too short. It should be at least 6 characters");

        if (data == "password-complexity-error")
          $("#newPasswordMessage").text("Please make your password more complex");

        if (data == "new-password-too-long")
          $("#newPasswordMessage").text("Your new password is too long");

        if (data == "passwords-cant-match")
          $("#newPasswordMessage").text("Your new password can not match your current password");
      }else if (data == "no-confirm-password" || data == "password-confirmation-failed") {
        $("#newPasswordConfirm").addClass("is-danger");
        $("#newPasswordConfirmMessage").addClass("is-danger");

        if (data == "no-confirm-password")
          $("#newPasswordConfirmMessage").text("Please confirm your new password");

        if (data == "password-confirmation-failed") {
          $("#newPasswordConfirmMessage").text("Your new password and new password confirmation do not match. Please check and try again");
          $("#newPassword").addClass("is-danger");
        }
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
        $("#message").addClass("is-success").text("Your password has been changed successfully. All other sessions have been logged out");
        $("#newPassword").empty();
        $("#newPasswordConfirm").empty();
        $("#password").empty();
        $("#twostepcode").empty();
      }
      else $("#message").addClass("is-danger").text("Due to a network error, your password could not be changed");
    }).always(function() {
      $("#newPassword").attr("disabled", false);
      $("#newPasswordConfirm").attr("disabled", false);
      $("#password").attr("disabled", false);
      $("#twostepcode").attr("disabled", false);
      $("#changePasswordBtn").removeClass("is-loading").attr("disabled", false);
    }).fail(function() {
      $("#message").addClass("is-danger").text("Due to a network error, your password could not be changed");
    });
  });
});
