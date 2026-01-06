$(document).ready(function() {
  $("#search_collapse").click(function() {
    $("#mainColumn").removeClass("is-mobile");
    $("#search_collapse").hide();
    $("#searchContainer").show();
  });

  $("#searchBtn").click(function() {
    $("#searchBtn").addClass("is-loading").attr("disabled", true);
    $("#searchValue").attr("disabled", true);

    if ($("#searchValue").val().trim().length > 0)
      window.location = "/store?type=" + $("#searchBtn").attr("data-type") + "&query=" + $("#searchValue").val();
    else {
      $("#searchValue").addClass("is-danger").attr("disabled", false);
      $("#searchBtn").removeClass("is-loading").attr("disabled", false);
    }
  });

  $("#searchValue").keyup(function(e) {
    if (e.keyCode == 13)
      $("#searchBtn").click();
  });
});
