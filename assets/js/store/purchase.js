$(document).ready(function() {
  $("#purchaseItem").click(function() {
    $("#purchaseItem").addClass("is-loading").attr("disabled", true);
    $("#purchase_message").empty().removeClass("is-danger").removeClass("is-success");

    $.post("/api/store/purchase", {
      itemId: $("#purchaseItem").attr("data-id")
    }).done(function(data) {
      if (data.substr(0, 8) != "success-") $("#purchaseItem").removeClass("is-loading").attr("disabled", false);

      if (data == "already-owned")
        $("#purchase_message").addClass("is-danger").text("Error, looks like you already own this item");
      else if (data == "insufficient-currency")
        showModal("Insufficient funds", "Your account does not have sufficient cubes to purchase this item");
      else if (data.substr(0, 8) == "success-") {
        var jsonData = JSON.parse(data.substr(8));
        $("#currency_cubes").text(jsonData.cubes);
        showModal("Success", "You have successfully purchased this item");
        $("#purchaseItem").addClass("tooltip is-tooltip-bottom").attr("data-tooltip", "You already own this item").removeClass("is-loading");
      }else
        $("#purchase_message").addClass("is-danger").text("Due to a network error, this item could not be purchased. Please attempt again");
    }).fail(function() {
      $("#purchase_message").addClass("is-danger").text("Due to a network error, this item could not be purchased. Please attempt again");
    });
  });
});
