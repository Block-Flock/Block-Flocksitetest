$(document).ready(function() {
    $("#forgotButton").click(function() {
        $("#forgotButton").attr("disabled", true).addClass("is-loading");
        $("#username").attr("disabled", true).removeClass("is-success").removeClass("is-danger");
        $("#code").attr("disabled", true).removeClass("is-success").removeClass("is-danger");
        $("#username_message").empty().removeClass("is-success").removeClass("is-danger");
        $("#code_message").empty().removeClass("is-success").removeClass("is-danger");
        $("#message").empty().removeClass("is-success").removeClass("is-danger");

        $.post("/api/auth/forgot-password/two-step-verification", {
            username: $("#username").val(),
            code: $("#code").val()
        }).done(function(data) {
            if (data == "rate-limit") $("#message").addClass("is-danger").text("You have too recently requested a password reset request. Please wait before doing that again");
            else if (data == "missing-information") {
                if ($("#username").val().length == 0) {
                    $("#username").addClass("is-danger");
                    $("#username_message").addClass("is-danger").text("Please enter your username or e-mail address");
                }

                if ($("#code").val().length == 0) {
                    $("#code").addClass("is-danger");
                    $("#code_message").addClass("is-danger").text("Please enter your 6 digit verification code");
                }
            } else if (data == "no-user") {
                $("#username").addClass("is-danger");
                $("#username_message").addClass("is-danger").text("This user was not found. Please check and try again");
            } else if (data == "no-code" || data == "invalid-code" || data == "wrong-code" || data == "rate-limit-code") {
                $("#code").addClass("is-danger");
                $("#code_message").addClass("is-danger");

                if (data == "no-code")
                    $("#code_message").text("Please enter your 6 digit verification code");

                if (data == "invalid-code")
                    $("#code_message").text("This code is invalid. Please check and try again");

                if (data == "wrong-code")
                    $("#code_message").text("Either your 6 digit code is wrong or this account has two step verification disabled or this account has chosen to opt out of password resets using Two Step Verification");

                if (data == "rate-limit-code")
                    $("#code_message").text("Due to too many failed attempts, you must wait before trying this again");
            } else if (data.substr(0, 8) == "success-") {
                var dataJson = JSON.parse(data.substr(8));
                window.location = "/security/reset-password/" + dataJson.userID + "/" + dataJson.key;
            } else $("#message").addClass("is-danger").text("Due to a network error, the password reset process could not be started. Please try again later");
        }).always(function() {
            $("#forgotButton").attr("disabled", false).removeClass("is-loading");
            $("#username").attr("disabled", false);
            $("#code").attr("disabled", false);
        }).fail(function() {
            $("#message").addClass("is-danger").text("Due to a network error, the password reset process could not be started. Please try again later");
        });
    });
});