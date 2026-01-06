$(document).ready(function() {
  $("#dEnable").click(function() {
    $("#dEnable").attr("disabled", true).addClass("is-loading");
    $("#dMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#dPasswordMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#dPassword").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
    $.post("/api/settings/account/twostepverification/enable", {
      password: $("#dPassword").val()
    }).done(function(data) {
      if (data == "rate-limit")
        $("#dMessage").addClass("is-danger").text("You have too recently enabled Two Step Verification. Please wait before enabling it again.");
      else if (data.substr(0, 8) == "success-") {
        var dataJson = JSON.parse(data.substr(8));
        changeStateView("init", dataJson.privateKey, dataJson.QRImageURL);
      }else if (data == "rate-limit-password" || data == "incorrect-password" || data == "no-password" || data == "password-too-short" || data == "password-too-long") {
        $("#dPassword").addClass("is-danger");
        $("#dPasswordMessage").addClass("is-danger");

        if (data == "rate-limit-password")
          $("#dPasswordMessage").text("Due to too many failed password verification attempts, you must wait before using any feature with password verification again ...");

        if (data == "incorrect-password")
          $("#dPasswordMessage").text("You have entered an incorrect password. Please check and try again");

        if (data == "no-password")
          $("#dPasswordMessage").text("Please enter your password");

        if (data == "password-too-short")
          $("#dPasswordMessage").text("Your password is too short. It should be at least 6 characters");

        if (data == "password-too-long")
          $("#dPasswordMessage").text("Your password is too long");
      }
      else $("#dMessage").addClass("is-danger").text("Due to a network error, Two Step Verification could not be enabled");
    }).always(function() {
      $("#dEnable").attr("disabled", false).removeClass("is-loading");
      $("#dPassword").val("").attr("disabled", false);
    }).fail(function() {
      $("#dMessage").addClass("is-danger").text("Due to a network error, Two Step Verification could not be enabled");
    });
  });

  $("#init_finishBtn").click(function() {
    $("#init_finishBtn").attr("disabled", true).addClass("is-loading");
    $("#iMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#init_codeMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#init_code").removeClass("is-danger").removeClass("is-success").attr("disabled", true);

    $.post("/api/settings/account/twostepverification/verify", {
      code: $("#init_code").val()
    }).done(function(data) {
      if (data == "rate-limit") $("#iMessage").addClass("is-danger").text("You have incorrectly entered your two step code too many times. Please wait before trying again. Are you sure you are using Authy to generate your codes?");
      else if (data == "no-code" || data == "invalid-code" || data == "wrong-code") {
        $("#init_code").addClass("is-danger");
        $("#init_codeMessage").addClass("is-danger");

        if (data == "no-code")
          $("#init_codeMessage").text("Please enter your two step verification code");

        else if (data == "invalid-code")
          $("#init_codeMessage").text("This two step verification code is invalid. Please check and try again");

        else if (data == "wrong-code")
          $("#init_codeMessage").text("This two step verification code is wrong. Please try again");
      }else if (data == "success")
        changeStateView("enabled");
      else $("#iMessage").addClass("is-danger").text("Due to a network error, verification of two step verification has failed");
    }).always(function() {
      $("#init_finishBtn").attr("disabled", false).removeClass("is-loading");
      $("#init_code").attr("disabled", false);
    }).fail(function() {
      $("#iMessage").addClass("is-danger").text("Due to a network error, verification of two step verification has failed");
    });
  });

  $("#init-print").click(function() {
    printDetails("#data-privatekey-init", "#data-qr");
  });

  $("#enabled_disableBtn").click(function() {
    $("#enabled_disableBtn").attr("disabled", true).addClass("is-loading");
    $("#enabled_disable_code").attr("disabled", true).removeClass("is-success").removeClass("is-danger");
    $("#enabled_disable_codeMessage").empty().removeClass("is-success").removeClass("is-danger");
    $("#enabled_disableMessage").empty().removeClass("is-success").removeClass("is-danger");

    $.post("/api/settings/account/twostepverification/disable", {
      code: $("#enabled_disable_code").val()
    }).done(function(data) {
      if (data == "rate-limit" || data == "invalid-code" || data == "wrong-code" || data == "no-code") {
        $("#enabled_disable_code").addClass("is-danger");
        $("#enabled_disable_codeMessage").addClass("is-danger");

        if (data == "rate-limit")
          $("#enabled_disable_codeMessage").text("Due to too many failed attempts, you must wait before trying again");

        if (data == "no-code")
          $("#enabled_disable_codeMessage").text("Please enter your 6 digit verification code");

        if (data == "invalid-code")
          $("#enabled_disable_codeMessage").text("This code is invalid. Please check and try again");

        if (data == "wrong-code")
          $("#enabled_disable_codeMessage").text("This code is wrong. Please try again");
      }else if (data == "success")
        changeStateView("disabled");
      else $("#enabled_disableMessage").addClass("is-danger").text("Due to a network error, two step verification could not be disabled");
    }).always(function() {
      $("#enabled_disableBtn").attr("disabled", false).removeClass("is-loading");
      $("#enabled_disable_code").attr("disabled", false);
    }).fail(function() {
      $("#enabled_disableMessage").addClass("is-danger").text("Due to a network error, two step verification could not be disabled");
    });
  });

  $("#enabled_detailsBtn").click(function() {
    $("#enabled_detailsBtn").attr("disabled", true).addClass("is-loading");
    $("#enabled_details_code").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
    $("#enabled_details_codeMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#enabled_detailsMessage").empty().removeClass("is-danger").removeClass("is-success");

    $.post("/api/settings/account/twostepverification/details", {
      code: $("#enabled_details_code").val()
    }).done(function(data) {
      if (data == "rate-limit" || data == "no-code" || data == "invalid-code" || data == "wrong-code") {
        $("#enabled_details_code").addClass("is-danger");
        $("#enabled_details_codeMessage").addClass("is-danger");

        if (data == "rate-limit")
          $("#enabled_details_codeMessage").text("Due to too many failed attempts, you must wait before trying again");

        if (data == "no-code")
          $("#enabled_details_codeMessage").text("Please enter your 6 digit verification code");

        if (data == "invalid-code")
          $("#enabled_details_codeMessage").text("This code is invalid. Please check and try again");

        if (data == "wrong-code")
          $("#enabled_details_codeMessage").text("This code is wrong. Please try again");
      }else if (data.substr(0, 8) == "success-") {
        var dataJson = JSON.parse(data.substr(8));
        $("#detailContainer").html('<p class="has-text-grey-light">You can review your details here. Remember to never share this to anyone!</p>\
        <b>Your private key is</b>: <span id="print_details_privatekey">' + dataJson.privatekey + '</span>\
        <br><img id="print_details_qr" src="' + dataJson.qrURL + '"><br>\
        <p class="has-text-grey-light">You can use either your private key or your QR code to add it to your authenticator app (Authy) in case you have to transfer this over. <a id="printDetails">(print)</a>');
        $("#printDetails").click(function() {
          printDetails("#print_details_privatekey", "#print_details_qr");
        });
      }else
        $("#enabled_detailsMessage").addClass("is-danger").text("Due to a network error, your details could not be obtained");
    }).always(function() {
      $("#enabled_detailsBtn").removeClass("is-loading").attr("disabled", false);
      $("#enabled_details_code").attr("disabled", false);
    }).fail(function() {
      $("#enabled_detailsMessage").addClass("is-danger").text("Due to a network error, your details could not be obtained");
    });
  });

  $("#enabled_optBtn").click(function() {
    $("#enabled_opt_value").attr("disabled", true);
    $("#enabled_optBtn").attr("disabled", true).addClass("is-loading");
    $("#enabled_opt_code").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
    $("#enabled_opt_password").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
    $("#enabled_opt_codeMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#enabled_opt_passwordMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#enabled_optMessage").empty().removeClass("is-danger").removeClass("is-success");
    var choice = $('#enabled_opt_value').prop('checked') ? 0 : 1;

    $.post("/api/settings/account/twostepverification/passreset", {
      code: $("#enabled_opt_code").val(),
      password: $("#enabled_opt_password").val(),
      choice: choice
    }).done(function(data) {
      if (data == "rate-limit") $("#enabled_optMessage").addClass("is-danger").text("You have changed this too recently. Please wait before changing it again");
      else if (data == "choice-unchanged") $("#enabled_optMessage").addClass("is-danger").text("You new choice matches your current one. If you wish to change this, you must click on the checkbox and then click the save button");
      else if (data == "missing-information") {
        if ($("#enabled_opt_code").val().length == 0) {
          $("#enabled_opt_code").addClass("is-danger");
          $("#enabled_opt_codeMessage").addClass("is-danger").text("Please enter your 6 digit verification code");
        }

        if ($("#enabled_opt_password").val().length == 0) {
          $("#enabled_opt_password").addClass("is-danger");
          $("#enabled_opt_passwordMessage").addClass("is-danger").text("Please enter your password");
        }
      }else if (data == "no-code" || data == "invalid-code" || data == "wrong-code" || data == "rate-limit-code") {
        $("#enabled_opt_code").addClass("is-danger");
        $("#enabled_opt_codeMessage").addClass("is-danger");

        if (data == "no-code")
          $("#enabled_opt_codeMessage").text("Please enter your 6 digit code");

        if (data == "invalid-code")
          $("#enabled_opt_codeMessage").text("This code is invalid. Please check and try again");

        if (data == "wrong-code")
          $("#enabled_optcodeMessage").text("This code is wrong. Please try again");

        if (data == "rate-limit-code")
          $("#enabled_opt_codeMessage").text("Due to too many failed two step verification attempts, you must wait before trying again");

      }else if (data == "no-password" || data == "password-too-short" || data == "password-too-long" || data == "incorrect-password" || data == "rate-limit-password") {
        $("#enabled_opt_password").addClass("is-danger");
        $("#enabled_opt_passwordMessage").addClass("is-danger");

        if (data == "no-password")
          $("#enabled_opt_passwordMessage").text("Please enter your password");

        if (data == "password-too-short")
          $("#enabled_opt_passwordMessage").text("Your password is too short. It should be at least 6 characters long");

        if (data == "password-too-long")
          $("#enabled_opt_passwordMessage").text("Your password is too long");

        if (data == "incorrect-password") {
          $("#enabled_opt_passwordMessage").text("Your password is invalid. Please check and try again");
          $("#enabled_opt_password").val("");
        }

        if (data == "rate-limit-password")
          $("#enabled_opt_passwordMessage").text("Due to too many failed password verification attempts, you must wait before using any feature with password verification again ...");

      }else if (data.substr(0, 8) == "success-") {
        var status = data.substr(8);
        if (status == 0)
          $("#enabled_optMessage").addClass("is-success").text("Saved successfully, your account can now be retrieved using two step verification");
        else
          $("#enabled_optMessage").addClass("is-success").text("Saved successfully, your account can no longer be retrieved using two step verification");

        $("#enabled_opt_code").val("");
        $("#enabled_opt_password").val("");
      }else $("#enabled_optMessage").addClass("is-danger").text("Due to a network error, the request has failed. Please try again");
    }).always(function() {
      $("#enabled_opt_value").attr("disabled", false);
      $("#enabled_optBtn").attr("disabled", false).removeClass("is-loading");
      $("#enabled_opt_code").attr("disabled", false);
      $("#enabled_opt_password").attr("disabled", false);
    }).fail(function() {
      $("#enabled_optMessage").addClass("is-danger").text("Due to a network error, the request has failed. Please try again");
    });
  });
});

function changeStateView(state, privateKey = "", qrURL = "") {
  $("#view_disabled").hide();
  $("#view_init").hide();
  $("#view_enabled").hide();

  switch(state) {
    case "disabled":
      $("#view_disabled").show();
      break;
    case "init":
      $("#view_init").show();
      $("#data-privatekey-init").text(privateKey);
      $("#data-qr").attr("src", qrURL);
      break;
    case "enabled":
      $("#view_enabled").show();
      break;
    default:
      break;
  }
}

function printDetails(privatekeyelement, qrelement) {
  windowInstance = window.open('','','width=800,height=600');
  windowInstance.document.write('<head>\
    <link rel="stylesheet" href="/assets/css/bulma.css"/>\
  </head>\
  <body>\
    <img width="150" src="/assets/img/logo.png?v=2"><br>\
    Two Step Verification information for Cubash account <b>' + $("#cubash_username").html() + '</b>.<br>\
    <b>Your private key is</b>: ' + $(privatekeyelement).html() + '<br>\
    <img src="' + $(qrelement).attr('src') + '#.png">\
    <br>\
    You can use this information when you lose access to your Cubash account. You can either scan the QR code in Authy or enter your private key manually. The app should start generating 6 digit codes which you can use to login.\
    Keep this somewhere safe so you do not lose this. :)<br><p class="has-text-info">https://cubash.com</p>\
  </body>');
  setTimeout(function() {
    windowInstance.print();
    windowInstance.close();
  }, 250);
}
