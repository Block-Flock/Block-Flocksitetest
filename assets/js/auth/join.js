var request;
var username_throttle = email_throttle = null;

$(document).ready(function() {
    $('#createAccount').click(() => createAccount());
    $("#createAccount").click(function() {
        $("#createAccount").attr("disabled", true).addClass("is-loading");
        $(".input").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
        $(".help").empty().removeClass("is-success").removeClass("is-danger");
        initializeDismissCallback()
    });

    $("#username").keyup(function() {
        clearTimeout(username_throttle);
        username_throttle = setTimeout(function() {
            if (request != undefined) request.abort();
            request = $.post("/api/check/username/", {
                username: $("#username").val(),
                csrf: $("#csrf").val()
            }).done(function(data) {
                $("#username").addClass("is-danger");
                if (data == "no-username")
                    $("#username_message").addClass("is-danger").text("Please enter your username");
                else if (data == "username-too-short")
                    $("#username_message").addClass("is-danger").text("Your username is too short. It must be at least 3 characters long");
                else if (data == "username-too-long")
                    $("#username_message").addClass("is-danger").text("Your username is too long. It can not be longer than 15 characters");
                else if (data == "invalid-username")
                    $("#username_message").addClass("is-danger").text("Your username is invalid. It can only contain letters (a-Z) and numbers (0-9)");
                else if (data.substr(0, 13) == "bad-username")
                    $("#username_message").addClass("is-danger").text('Bad word found in username. Please remove "' + data.substr(13) + '" from your username');
                else if (data == "username-taken")
                    $("#username_message").addClass("is-danger").text("This username has already been taken. Please try something else");
                else if (data == "passed") {
                    $("#username_message").removeClass("is-danger").addClass("is-success").text("This username is available");
                    $("#username").removeClass("is-danger").addClass("is-success");
                }
            });
        }, 500);
    });

    $("#emailAddress").keyup(function() {
        clearTimeout(email_throttle);
        email_throttle = setTimeout(function() {
            if (request != undefined) request.abort();
            request = $.post("/api/check/email/", {
                email: $("#emailAddress").val(),
                csrf: $("#csrf").val()
            }).done(function(data) {
                $("#emailAddress").addClass("is-danger");
                if (data == "no-email")
                    $("#emailAddress_message").addClass("is-danger").text("Please enter your e-mail address. It will be verified and be used to reset your password.");
                else if (data == "email-too-long")
                    $("#emailAddress_message").addClass("is-danger").text("Your e-mail address is too long. It can not be longer than 128 characters.");
                else if (data == "invalid-email")
                    $("#emailAddress_message").addClass("is-danger").text("You have entered an invalid e-mail address. Please check and try again.");
                else if (data == "email-taken")
                    $("#emailAddress_message").addClass("is-danger").text("The e-mail address you are using has already been taken by another account. Please use another one.");
                else if (data == "passed") {
                    $("#emailAddress_message").removeClass("is-danger").addClass("is-success").text("This e-mail address is valid and available.");
                    $("#emailAddress").removeClass("is-danger").addClass("is-success");
                }
            });
        }, 500);
    });

    $("#password").keyup(function() {
        $("#password").addClass("is-danger");
        if ($("#passwordConfirm").val() != $("#password").val() && $("#passwordConfirm").val().length > 0) {
            $("#passwordConfirm").addClass("is-danger");
            $("#passwordConfirm_message").addClass("is-danger").text("Your password and password confirmation did not match. Please try again.");
        }
        if ($("#passwordConfirm").val() == $("#password").val() && $("#passwordConfirm").val().length > 0) {
            $("#passwordConfirm_message").removeClass("is-danger").addClass("is-success").text("Confirmation successful.");
            $("#passwordConfirm").removeClass("is-danger").addClass("is-success");
        }
        if ($("#password").val().length == 0)
            $("#password_message").addClass("is-danger").text("Please enter a password. You will need one to login.");
        else if ($("#password").val().length < 6)
            $("#password_message").addClass("is-danger").text("Your password is too short. It must be at least 6 characters long.");
        else if ($("#password").val().length > 45)
            $("#password_message").addClass("is-danger").text("Your password is too long.");
        else {
            $("#password_message").removeClass("is-danger").addClass("is-success").text("Your password meets all the requirements.");
            $("#password").removeClass("is-danger").addClass("is-success");
        }
    });

    $("#passwordConfirm").keyup(function() {
        $("#passwordConfirm").addClass("is-danger");
        if ($("#passwordConfirm").val().length == 0)
            $("#passwordConfirm_message").addClass("is-danger").text("Please confirm your password.");
        else if ($("#passwordConfirm").val() != $("#password").val())
            $("#passwordConfirm_message").addClass("is-danger").text("Your password and password confirmation did not match. Please try again.");
        else {
            $("#passwordConfirm_message").removeClass("is-danger").addClass("is-success").text("Confirmation successful.");
            $("#passwordConfirm").removeClass("is-danger").addClass("is-success");
        }
    });
});

function createAccount() {
    $("#createAccount").attr("disabled", true).addClass("is-loading");
    $(".input").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
    $(".help").empty().removeClass("is-success").removeClass("is-danger");
    $.post("/api/auth/register/", {
        username: $("#username").val(),
        password: $("#password").val(),
        passwordConfirm: $("#passwordConfirm").val(),
        email: $("#emailAddress").val(),
        csrf: $("#csrf").val()
    }).done(function(data) {
        if (data == "missing-information") {
            if ($("#username").val().length == 0) {
                $("#username").addClass("is-danger");
                $("#username_message").addClass("is-danger").text("Please enter a unqiue username.");
            }

            if ($("#password").val().length == 0) {
                $("#password").addClass("is-danger");
                $("#password_message").addClass("is-danger").text("Please enter a password. You will need one to login.");
            }

            if ($("#passwordConfirm").val().length == 0) {
                $("#passwordConfirm").addClass("is-danger");
                $("#passwordConfirm_message").addClass("is-danger").text("Please confirm your password.");
            }

            if ($("#emailAddress").val().length == 0) {
                $("#emailAddress").addClass("is-danger");
                $("#emailAddress_message").addClass("is-danger").text("Please enter your e-mail address. It will be verified and be used to reset your password.");
            }

            if ($("#recaptcha").val().length == 0) {
                $("#recaptcha").addClass("is-danger");
                $("#recaptcha_message").addClass("is-danger").text("Please fill in Recaptcha.");
            }

        } else if (data == "no-username" || data == "username-too-short" || data == "username-too-long" || data == "invalid-username" || data.substr(0, 13) == "bad-username-" || data == "username-taken") {
            $("#username").addClass("is-danger");
            if (data == "no-username")
                $("#username_message").addClass("is-danger").text("Please enter your username.");
            else if (data == "username-too-short")
                $("#username_message").addClass("is-danger").text("Your username is too short. It must be at least 3 characters long.");
            else if (data == "username-too-long")
                $("#username_message").addClass("is-danger").text("Your username is too long. It can not be longer than 15 characters long.");
            else if (data == "invalid-username")
                $("#username_message").addClass("is-danger").text("Your username is invalid. It can only contain letters (a-Z) and numbers (0-9).");
            else if (data.substr(0, 13) == "bad-username")
                $("#username_message").addClass("is-danger").text('Bad word found in username. Please remove "' + data.substr(13) + '" from your username.');
            else if (data == "username-taken")
                $("#username_message").addClass("is-danger").text("This username has already been taken. Please try something else.");
        } else if (data == "no-password" || data == "password-too-short" || data == "password-too-long") {
            $("#password").addClass("is-danger");
            if (data == "no-password")
                $("#password_message").addClass("is-danger").text("Please enter a password. You will need one to login.");
            else if (data == "password-too-short")
                $("#password_message").addClass("is-danger").text("Your password is too short. It must be at least 6 characters long.");
            else if (data == "password-too-long")
                $("#password_message").addClass("is-danger").text("Your password is too long.");
        } else if (data == "no-password-confirm" || data == "password-confirmation-failed") {
            $("#passwordConfirm").addClass("is-danger");
            if (data == "no-password-confirm")
                $("#passwordConfirm_message").addClass("is-danger").text("Please confirm your password.");
            else if (data == "password-confirmation-failed") {
                $("#password").addClass("is-danger");
                $("#passwordConfirm_message").addClass("is-danger").text("Your password and password confirmation did not match. Please try again.");
            } else if (data == "no-recaptcha") {
                $("#recaptcha").addClass("is-danger");
                if (data == "no-recaptcha")
                    $("#recaptcha_message").addClass("is-danger").text("Please fill in Recaptcha");
                }
        } else if (data == "no-email" || data == "email-too-long" || data == "invalid-email" || data == "email-taken") {
            if (data == "no-email")
                $("#emailAddress_message").addClass("is-danger").text("Please enter your e-mail address. It will be verified and be used to reset your password.");
            else if (data == "email-too-long")
                $("#emailAddress_message").addClass("is-danger").text("Your e-mail address is too long. It can not be longer than 128 characters.");
            else if (data == "invalid-email")
                $("#emailAddress_message").addClass("is-danger").text("You have entered an invalid e-mail address. Please check and try again.");
                else if (data == "email-taken")
                $("#emailAddress_message").addClass("is-danger").text("The e-mail address you are using has already been taken by another account. Please use another one.");
            } else if (data == "no-recaptcha") {
                if (data == "no-recaptcha")
                    $("#recaptcha_message").addClass("is-danger").text("Please fill in Recaptcha.");
            }  else if (data == "success") {
            if (!navigator.cookieEnabled) window.location = "/log-in";
            else {
                $.cookie('cookies_accepted', 'true', { expires: 365, path: '/' });
                window.location.replace("/log-in");
            }
        } else if (data == "rate-limit") showModal("Error", "You have too recently created an account. Please try again later.");
        else $("#message").addClass("is-danger").html('Due to a network error, your account could not be created. Try <a onclick="window.location.reload();">refreshing</a> the page and try again.');
 }).always(function() {
        $("#createAccount").attr("disabled", false).removeClass("is-loading");
        $(".input").attr("disabled", false);
    }).fail(function() {
        $("#message").addClass("is-danger").text("Due to a network error, your account could not be created. Please try again later.");
    });
}

function initializeDismissCallback() {
    $('*').filter(function() {
        return $(this).css('z-index') == 2000000000;
    }).each(function() {
        $(this).click(function() {
            $("#createAccount").attr("disabled", false).removeClass("is-loading");
            $(".input").attr("disabled", false);
        });
    });
}


















