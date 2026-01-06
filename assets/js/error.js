$(document).ready(function() {
  $("#returnBtn").click(function() {
    window.history.back();
  });

  if (document.referrer == "")
    $("#returnBtn").attr("disabled", true);
});
