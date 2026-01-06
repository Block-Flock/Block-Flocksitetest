$(document).ready(function() {
    $("#go").click(function() {
        var btn = $(this);
        btn.addClass("is-loading");
        $("#go").attr("disabled", true);
        $.post("/api/settings/account/change-username/", {
            newUsername: $("#new-username").val(),
            newUsernameConfirm: $("#new-username-confirm").val(),
            password: $("#password").val(),
            csrf: $("#csrf").val()
        }).done(function(data) {
            if (data == "success") $("#message").addClass("is-success").text("Your username has been changed successfully, please refresh.");
            else if (data == "username-unavailable") $("#username").addClass("is-danger").text("The username that you have specified is not available.");
            else if (data == "bad-username") $("#newusername").addClass("is-danger").text("Your username has triggered our profanity filter, check your username.");
            else if (data == "invalid-username") $("#newusername").addClass("is-danger").text("Your username is invalid. It can only contain letters (a-Z) and numbers (0-9).");
            else if (data == "insufficent-balance") $("#message").addClass("is-danger").text("You need at least 500 Coins to change your username.");
            else if (data == "unchanged") $("#username").addClass("is-danger").text("You can not change your username to your current username.");
            else $("#message").addClass("is-danger").text("Due to a network error, your username could not be changed.");
        }).always(function() {
            btn.removeClass("is-loading").attr("disabled", false);
        }).fail(function() {
            $("#message").addClass("is-danger").text("Due to a network error, your username could not be changed.");
        });
    });
});






















