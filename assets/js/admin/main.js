
$(document).ready(function() {
    $('#loginButton').click(() => doLogin());
    $("#loginButton").click(function() {
        $(this).addClass("is-loading").attr("disabled", true);
        $(".input").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
        $(".help").empty().removeClass("is-success").removeClass("is-danger");
        initializeDismissCallback();
    });

    $("#username").keyup(function(e) {
        if (e.keyCode == 13) // Enter key
            if ($("#loginButton").is(":visible")) $("#loginButton").click();
    });

    $("#password").keyup(function(e) {
        if (e.keyCode == 13) // Enter key
            if ($("#loginButton").is(":visible")) $("#loginButton").click();
    });
});

function doLogin() {
    $("#loginButton").addClass("is-loading").attr("disabled", true);
    $("#adminid").removeClass("is-danger").removeClass("is-success");
    $("#password").removeClass("is-danger").removeClass("is-success");
    $("#adminid_message").empty();
    $("#password_message").empty();
    $("#message").empty();

    if (!navigator.cookieEnabled) {
        $("#message").addClass("is-danger").text("Oops! To login, you must have cookies enabled. Please enable cookies and attempt again.");
        $("#loginButton").removeClass("is-loading").attr("disabled", false);
        $("#adminid").attr("disabled", false);
        $("#password").attr("disabled", false);
        return;
    }

    $.post("/api/admin/login/", {
        adminid: $("#adminid").val(),
        password: $("#password").val(),
        csrf: $("#csrf").val()
    }).done(function(data) {
        if ($("#adminid").val().length == 0) {
            $("#adminid").addClass("is-danger");
            $("#adminid_message").addClass("is-danger").text("You cannot leave this field empty.");
        }
        if ($("#password").val().length == 0) {
            $("#password").addClass("is-danger");
            $("#password_message").addClass("is-danger").text("Your cannot leave this field empty.")
        } else if (data == "no-user") {
            $("#adminid").addClass("is-danger");
            $("#adminid_message").addClass("is-danger").text("There was not an administration account with this id in our records.");
        } else if (data == "incorrect-password") {
            $("#password").addClass("is-danger").val("");
            $("#password_message").addClass("is-danger").text("Incorrect password has been specified. Please check and try again.")
        } else if (data == "rate-limit") $("#message").addClass("is-danger").text("Due to too many failed login attempts, you have to wait a bit before trying again.");
        else if (data == "success") {
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