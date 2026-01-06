$(document).ready(function() {
    $("#forgotButton").click(function() {
        $("#forgotButton").attr("disabled", true).addClass("is-loading");
        $("#email").attr("disabled", true).removeClass("is-success").removeClass("is-danger");
        $("#email_message").empty().removeClass("is-danger").removeClass("is-success");
        $("#message").empty().removeClass("is-danger").removeClass("is-success");
        initializeDismissCallback();
        grecaptcha.execute();
    });
});

function requestEmail(token) {
    if (token == undefined) return;
    $("#forgotButton").attr("disabled", true).addClass("is-loading");
    $("#email").attr("disabled", true).removeClass("is-success").removeClass("is-danger");
    $("#email_message").empty().removeClass("is-danger").removeClass("is-success");
    $("#message").empty().removeClass("is-danger").removeClass("is-success");
    $.post("/api/auth/forgot-password/email", {
        email: $("#email").val(),
        token: token
    }).done(function(data) {
        if (data != "success") grecaptcha.reset();
        if (data == "rate-limit") $("#message").addClass("is-danger").text("You have too recently requested a password reset request. Please wait before doing that again");
        else if (data == "captcha-error") $("#message").addClass("is-danger").text("Invalid captcha submission. Please try again");
        else if (data == "temporarily-blocked") $("#message").addClass("is-danger").text("You have been temporarily blocked from requesting password requests");
        else if (data == "missing-information" || data == "no-user" || data == "invalid-email") {
            $("#email").addClass("is-danger");
            $("#email_message").addClass("is-danger");

            if (data == "missing-information")
                $("#email_message").text("Please enter your e-mail address to initiate the password reset process");

            if (data == "no-user")
                $("#email_message").text("No account has been found that is using this e-mail address. Please check your e-mail address and try again");

            if (data == "invalid-email")
                $("#email_message").text("The e-mail address you have entered is invalid. Please check your e-mail address and try again");
        } else if (data == "success")
            $("#email_message").addClass("is-success").text("E-mail sent! Check your inbox for a message, if you can not find it, try checking your spam or junk folders");
        else $("#message").addClass("is-danger").text("Due to a network error, the e-mail could not be sent");
    }).always(function() {
        $("#forgotButton").attr("disabled", false).removeClass("is-loading");
        $("#email").attr("disabled", false);
    }).fail(function() {
        $("#message").addClass("is-danger").text("Due to a network error, the e-mail could not be sent");
    });
}

function initializeDismissCallback() {
    $('*').filter(function() {
        return $(this).css('z-index') == 2000000000;
    }).each(function() {
        $(this).click(function() {
            $("#forgotButton").attr("disabled", false).removeClass("is-loading");
            $("#email").attr("disabled", false);
        });
    });
}