$(document).ready(function() {
  $("#cancelBtn").click(function() {
    window.location = "/forum/post?id=" + $("#cancelBtn").attr("data-id");
  });

  $("#createReply").click(function() {
    $("#createReply").attr("disabled", true).addClass("is-loading");
    $("#cancelBtn").attr("disabled", true);
    $("#replyContent").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
    $("#replyContentMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#message").empty().removeClass("is-danger").removeClass("is-success");

    $.post("/api/forum/reply", {
      id: $("#createReply").attr("data-id"),
      content: $("#replyContent").val(),
      "_u": $('meta[name="_u"]').attr('content')
    }).done(function(data) {
      if (data == "rate-limit") $("#message").addClass("is-danger").text("You have posted too recently. Please wait a bit before posting again");
      else if (data == "no-content" || data == "content-too-short" || data == "content-too-long" || data.substr(0, 12) == "bad-content-") {
        $("#replyContent").addClass("is-danger");
        $("#replyContentMessage").addClass("is-danger");

        if (data == "no-content")
          $("#replyContentMessage").text("Please type something for your reply message");

        if (data == "content-too-short")
          $("#replyContentMessage").text("Your reply is too short. It should be at least 3 characters long");

        if (data == "content-too-long")
          $("#replyContentMessage").text("Your reply is too long. It can not be longer than 2,000 characters");

        if (data.substr(0, 12) == "bad-content-")
          $("#replyContentMessage").text('Your reply contains a bad word. Please remove "'+data.substr(12)+'" from your reply and try again');
      }else if (data.substr(0, 8) == "success-") {
        var dataJson = JSON.parse(data.substr(8));
        window.location = "/forum/post?id=" + dataJson.postID + "&page=" + dataJson.page + "&highlight=" + dataJson.replyID;
      }else $("#message").addClass("is-danger").text("Due to a network error, your reply could not be created. Please try again later");
    }).always(function(data) {
      if (data.substr(0, 8) != "success-") {
        $("#createReply").attr("disabled", false).removeClass("is-loading");
        $("#cancelBtn").attr("disabled", false);
        $("#replyContent").attr("disabled", false);
      }
    }).fail(function() {
      $("#message").addClass("is-danger").text("Due to a network error, your reply could not be created. Please try again later");
    });
  });
});
