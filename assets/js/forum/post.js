$(document).ready(function() {
  var url = new URL(window.location.href);
  var hlight = url.searchParams.get("highlight");
  if (typeof hlight != undefined && Number.isInteger(parseInt(hlight)) && hlight.indexOf(".") == -1) {
    if ($("#reply_" + hlight).length == 0) return;
    var offset = $("#reply_" + hlight).offset();
    offset.left = offset.top -= 75;
    $('html, body').animate({scrollTop: offset.top, scrollLeft: offset.left});
    $("#reply_" + hlight).addClass("animated pulse");
  }
});
