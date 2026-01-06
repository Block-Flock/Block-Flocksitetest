$(document).ready(function() {

  $("#cancelBtn").click(function() {
    window.location = "/forum?id=" + $("#cancelBtn").attr("data-id");
  });

  $("#createPost").click(function() {
    $("#createPost").addClass("is-loading").attr("disabled", true);
    $("#cancelBtn").attr("disabled", true);
    $("#postTitle").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
    $("#postContent").attr("disabled", true).removeClass("is-danger").removeClass("is-success");
    $("#postTitleMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#postContentMessage").empty().removeClass("is-danger").removeClass("is-success");
    $("#message").empty().removeClass("is-danger").removeClass("is-success");

    $.post("/api/forum/new", {
      id: $("#createPost").attr("data-id"),
      title: $("#postTitle").val(),
      content: $("#postContent").val(),
      "_u": $('meta[name="_u"]').attr('content')
    }).done(function(data) {
      if (data == "rate-limit")
        $("#message").addClass("is-danger").text("You have posted too recently. Please wait before posting again");
      else if (data == "missing-information") {
        if ($("#postTitle").val().trim().length == 0) {
          $("#postTitle").addClass("is-danger");
          $("#postTitleMessage").addClass("is-danger").text("Please enter a post title");
          $("#postTitle").val("");
        }
        if ($("#postContent").val().trim().length == 0) {
          $("#postContent").addClass("is-danger");
          $("#postContentMessage").addClass("is-danger").text("Please write content for your post");
          $("#postContent").val("");
        }
      }else if (data == "no-title" || data == "title-too-short" || data == "title-too-long" || data.substr(0, 10) == "bad-title-" || data == "illegal-title") {
        $("#postTitle").addClass("is-danger");
        $("#postTitleMessage").addClass("is-danger");

        if (data == "no-title")
          $("#postTitleMessage").text("Please enter a post title");

        if (data == "title-too-short")
          $("#postTitleMessage").text("Your post title is too short. It should be at least 3 characters long");

        if (data == "title-too-long")
          $("#postTitleMessage").text("Your post title is too long. It can not be longer than 64 characters");

        if (data.substr(0, 10) == "bad-title-")
          $("#postTitleMessage").text("Your post title contains a bad word. Please remove \"" + data.substr(10) + "\" from your title.");

        if (data == "illegal-title")
          $("#postTitleMessage").text("Your post title contains one or more illegal characters. Make sure to only use alphanumeric characters in your title");

      }else if (data == "no-content" || data == "content-too-short" || data == "content-too-long" || data.substr(0, 12) == "bad-content-") {
        $("#postContent").addClass("is-danger");
        $("#postContentMessage").addClass("is-danger");

        if (data == "no-content")
          $("#postContentMessage").text("Please write content for your post");

        if (data == "content-too-short")
          $("#postContentMessage").text("Your post content is too short. It should be at least 3 characters long");

        if (data == "content-too-long")
          $("#postContentMessage").text("Your post content is too long. It can not be longer than 2000 characters");

        if (data.substr(0, 12) == "bad-content-")
          $("#postContentMessage").text("Your post content contains a bad word. Please remove \"" + data.substr(12) + "\" from your post.");

      }else if (data.substr(0, 8) == "success-")
        window.location = "/forum/post?id=" + data.substr(8);
      else $("#message").addClass("is-danger").text("Due to a network error, your post could not be created");
    }).always(function(data) {
      if (data.substr(0, 8) != "success-") {
        $("#createPost").removeClass("is-loading").attr("disabled", false);
        $("#cancelBtn").attr("disabled", false);
        $("#postTitle").attr("disabled", false);
        $("#postContent").attr("disabled", false);
      }
    }).fail(function() {
      $("#message").addClass("is-danger").text("Due to a network error, your post could not be created");
    });
  });
});
