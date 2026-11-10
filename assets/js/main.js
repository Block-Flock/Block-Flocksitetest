var search_delay = null;

$(document).ready(function() {
  $(".mclose").click(function() {
    closeModal();
  });

  // Cookie agreement
  if ($.cookie('cookies_accepted') == undefined)
    $.cookie('cookies_accepted', 'false', { expires: 365, path: '/' });

  $("#accept_cookie_banner").click(function() {
    $("#cookie_banner").remove();
    $.cookie('cookies_accepted', 'true', { expires: 365, path: '/' });
  });


});

function showModal(title, bodycontent, footercontent = "", bottom = 150, showCloseBtn = true, animated = true) {
  if (showCloseBtn) $(".mclose").last().show();
  else $(".mclose").last().hide();
  $(".modal-card").css("bottom", bottom);
  if (animated)
    $(".modal-card").removeClass("animated zoomOut").addClass("animated zoomIn");
  $(".modal-background").show();
  $("#globalModal").addClass("is-active");
  $("#globalModalTitle").text(title);
  $("#globalModalContent").html(bodycontent);
  $("#globalModalFooter").html(footercontent);
}

function closeModal(animated = true) {
  if (animated) {
    $(".modal-card").addClass("animated zoomOut");
    $(".modal-background").fadeOut('fast', function() {
      $("#globalModal").removeClass("is-active");
    });
  }else {
    $(".modal-card").removeClass("animated zoomIn").removeClass("animated zoomOut");
    $("#globalModal").removeClass("is-active");
  }
}
