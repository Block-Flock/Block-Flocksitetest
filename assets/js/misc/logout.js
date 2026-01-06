$(document).ready(function() {
    $("#logoutBtn").click(function() {
        showModal("Logout",
            "Are you sure you want to logout?",
            '<button class="button is-danger" id="doLogout">Yes</button>'
        );

        $("#doLogout").click(function() {
            $("#doLogout").addClass("is-loading").attr("disabled", true);
            $(".mclose").attr("disabled", true);
            $.post("/api/auth/logout/").done(function() {
                $.removeCookie('chat_open', { path: '/' });
                $.removeCookie('chat_conversation_id', { path: '/' });
                window.location.replace("/");
            }).always(function() {
                $("#doLogout").removeClass("is-loading").attr("disabled", false);
                $(".mclose").attr("disabled", false);
            });
        });
    });
});