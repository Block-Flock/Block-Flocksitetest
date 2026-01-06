$(document).ready(function() {
  $(".cancelButton").click(function() {
    showModal("Please confirm",
      "Are you sure you want to cancel your subscription?",
      '<button class="button is-danger" id="doCancel">Yes</button>'
    );

    $("#doCancel").click(function() {
      var btn = $(this);
      btn.attr("disabled", true).addClass("is-loading");
      $.post("/api/settings/account/billing/cancel-subscription").done(function(data, textStatus, xhr) {
        $(".helperText").html("You currently have an cancelled subscription set to expire in: <b>" + xhr.responseJSON.timeToExpire + "</b>.");
        $("#buttonContainer").html('<a class="button is-primary" href="/upgrade">Upgrade</a>');

        showModal("Success", xhr.responseJSON.message);
      }).fail(function() {
        showModal("Error", "A network error has occurred");
      });
    });
  });
});
