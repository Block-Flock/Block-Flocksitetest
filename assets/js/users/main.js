$(document).ready(function() {
    $("#searchBtn").click(function() {
        $("#searchBtn").addClass("is-loading").attr("disabled", true);
        $("#searchValue").attr("disabled", true);
        if ($("#searchValue").val().length > 0)
            window.location = "/users?filter=" + $("#searchBtn").attr("data-filter") + "&query=" + $("#searchValue").val();
        else
            window.location = "/users?filter=" + $("#searchBtn").attr("data-filter");
    });

    $("#searchValue").keyup(function(e) {
        if (e.keyCode == 13)
            $("#searchBtn").click();
    });
});