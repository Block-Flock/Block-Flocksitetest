$(document).ready(function() {
  $(".upgradeButton").click(function() {
    var btn = $(this);
    btn.attr("disabled", true).addClass("is-loading");
    $.post("/api/upgrade/request-paypal", {
      id: btn.attr("data-id")
    }).done(function(response) {
      $.redirectWithPOST(response.url, {
        "cmd": response.data.cmd,
        "business": response.data.business,
        "item_name": response.data.item_name,
        "item_number": response.data.item_number,
        "currency_code": response.data.currency_code,
        "no_shipping": response.data.no_shipping,
        "a3": response.data.a3,
        "p3": response.data.p3,
        "t3": response.data.t3,
        "src": response.data.src,
        "sra": response.data.sra,
        "notify_url": response.data.notify_url,
        "custom": response.data.custom
      });
    }).fail(function(xhr) {
      if (typeof(xhr.responseJSON) === 'undefined')
        showModal("Error", "An unexpected error has occurred");
      else
        showModal("Error", xhr.responseJSON.errors.generalError);
    }).always(function() {
      btn.attr("disabled", false).removeClass("is-loading")
    });
  });
});
