var lastAvatarHash = "";
var defaultAvatarHash = "";
var lastUsername = "";
var avatarRequest;
var avatarRequestThrottle = null;

$(document).ready(function() {
    $('#loginButton').click(() => doLogin());
    $("#loginButton").click(function() {
        $(this).addClass("is-loading").attr("disabled", true);
        $(".input").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
        $(".help").empty().removeClass("is-success").removeClass("is-danger");
        initializeDismissCallback();
    });

    lastAvatarHash = $(".login_avatar img").attr("src");
    defaultAvatarHash = lastAvatarHash;
    $("#username").keyup(function(e) {
        if (e.keyCode == 13) // Enter key
            if ($("#loginButton").is(":visible")) $("#loginButton").click();

        if (lastUsername != $("#username").val()) {
            $(".login_avatar img").removeClass("animated bounceIn");

            clearTimeout(avatarRequestThrottle);
            avatarRequestThrottle = setTimeout(function() {
                lastUsername = $("#username").val();
                if (avatarRequest !== undefined)
                    avatarRequest.abort();

                avatarRequest = $.post("/api/check/avatar/", {
                    username: $("#username").val()
                }).done(function(data) {
                    var json = JSON.parse(data);
                    if (json.image_hash != lastAvatarHash) {
                        $(".login_avatar img").attr("src", json.image_hash);
                        if (json.image_hash != defaultAvatarHash)
                            $(".login_avatar img").addClass("animated bounceIn");
                    }
                    lastAvatarHash = json.image_hash;
                });
            }, 500);
        }
    });

    $("#password").keyup(function(e) {
        if (e.keyCode == 13) // Enter key
            if ($("#loginButton").is(":visible")) $("#loginButton").click();
    });
});

function doLogin() {
    $("#loginButton").addClass("is-loading").attr("disabled", true);
    $("#username").removeClass("is-danger").removeClass("is-success");
    $("#password").removeClass("is-danger").removeClass("is-success");
    $("#username_message").empty();
    $("#password_message").empty();
    $("#message").empty();

    if (!navigator.cookieEnabled) {
        $("#message").addClass("is-danger").text("Oops! To login, you must have cookies enabled. Please enable cookies and attempt again.");
        $("#loginButton").removeClass("is-loading").attr("disabled", false);
        $("#username").attr("disabled", false);
        $("#password").attr("disabled", false);
        return;
    }

    $.post("/api/auth/login/", {
        username: $("#username").val(),
        password: $("#password").val(),
        csrf: $("#csrf").val()
    }).done(function(data) {
        if ($("#username").val().length == 0) {
            $("#username").addClass("is-danger");
            $("#username_message").addClass("is-danger").text("You cannot leave this field empty.");
        }
        if ($("#password").val().length == 0) {
            $("#password").addClass("is-danger");
            $("#password_message").addClass("is-danger").text("Your cannot leave this field empty.")
        } else if (data == "no-user") {
            $("#username").addClass("is-danger");
            $("#username_message").addClass("is-danger").text("There was not an account found with that username.");
        } else if (data == "incorrect-password") {
            $("#password").addClass("is-danger").val("");
            $("#password_message").addClass("is-danger").text("Incorrect password has been specified. Please check and try again.")
        } else if (data == "rate-limit") $("#message").addClass("is-danger").text("Due to too many failed login attempts, you have to wait a bit before trying again.");
        else if (data == "success") {
            $.cookie('cookies_accepted', 'true', { expires: 365, path: '/' });
            $.cookie('chat_open', 'false', { expires: 7, path: '/' });
            $.cookie('chat_conversation_id', '0', { expires: 7, path: '/' });
            window.location.reload();
        } else $("#message").addClass("is-danger").addClass("is-danger").html('Due to a network error, you could not be logged in. Try <a onclick="window.location.reload();">refreshing</a> the page and try again.');
    }).always(function(data) {
        $("#loginButton").removeClass("is-loading").attr("disabled", false);
        $(".input").attr("disabled", false);
    }).fail(function() {
        $("#message").addClass("is-danger").text("Due to a network error, you could not be logged in.");
    })
}

function initializeDismissCallback() {
    $('*').filter(function() {
        return $(this).css('z-index') == 2000000000;
    }).each(function() {
        $(this).click(function() {
            $("#loginButton").attr("disabled", false).removeClass("is-loading");
            $(".input").attr("disabled", false);
        });
    });
}