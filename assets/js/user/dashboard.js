var initHTML = "";
var loading = false;
var loadInterval = null;

$(document).ready(function() {
  initHTML = $("#wallPostContainer").html();
  load_posts();

  $("#postStatusButton").click(function() {
    $("#postStatusButton").addClass("is-loading").attr("disabled", true);
    $("#statusContent").attr("disabled", true).removeClass("is-success").removeClass("is-danger");
    $("#message").empty().removeClass("is-success").removeClass("is-danger");

    $.post("/api/dashboard/update-status", {
      message: $("#statusContent").val()
    }).done(function(data) {
      if (data == "no-message" || data == "message-too-short" || data == "message-too-long" || data == "message-unchanged" || data.substr(0, 12) == "bad-message-") {
        $("#statusContent").addClass("is-danger");
        $("#message").addClass("is-danger");

        if (data == "no-message")
          $("#message").text("Please type something for your status update");
        else if (data == "message-too-short")
          $("#message").text("Your status update is too short. It should be at least 3 characters long");
        else if (data == "message-too-long")
          $("#message").text("Your status update is too long. It can not be longer than 255 characters");
        else if (data == "message-unchanged")
          $("#message").text("Your new status update matches your old one. Please post something new");
        else if (data.substr(0, 12) == "bad-message-")
          $("#message").text('Your new status contains a bad word. Please remove "'+data.substr(12)+'" from your new status and try again');
      }else if (data == "success") {
        $("#statusContent").val("");
        clearInterval(loadInterval);
        $("#wallPostContainer").html(initHTML);
        load_posts();
      }else if (data == "rate-limit")
        $("#message").addClass("is-danger").text("You have posted too recently. Please wait before posting again...");
      else $("#message").addClass("is-danger").text("Due to a network error, the status update could not be posted. Please attempt again later");
    }).always(function() {
      $("#postStatusButton").removeClass("is-loading").attr("disabled", false);
      $("#statusContent").attr("disabled", false);
    }).fail(function() {
      $("#message").addClass("is-danger").text("Due to a network error, the status update could not be posted. Please attempt again later");
    });
  });
});

function isLoaderVisible() {
  if ($(".wall_post_loading") !== undefined) {
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();
    var elemTop = $(".wall_post_loading").offset().top;
    var elemBottom = elemTop + $(".wall_post_loading").height();
    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
  }else clearInterval(loadInterval);
}

function load_posts(page = 1) {
  if (page > 1) $("#wallPostContainer").append(initHTML);
  $.get("/api/dashboard/fetch-posts?page=" + parseFloat(page)).done(function(data) {
    $(".loading_container").remove();
    loading = false;

    if (data == "no-posts")
      $("#wallPostContainer").append('<p class="has-text-grey-light">It looks like there is nothing to see here...</p>');
    else{
      var json = JSON.parse(data);

      var pages = json.amount_pages;
      delete json.amount_pages;

      for (var i = 0; i < Object.keys(json).length; i++) {
        var onlineHTML = (json[i].online) ? '<div title="'+json[i].username+' is online" class="avatar_online_circle circle-xs"></div>' : '';
        var deleteHTML = (json[i].canDelete) ? '<div class="media-right">\
                                                  <button class="delete deleteWallPost" data-id="'+json[i].id+'"></button>\
                                                </div>' : "";

        $("#wallPostContainer").append('<div class="box wall_post" data-wallpost-id="'+json[i].id+'">\
          <article class="media">\
            <figure class="media-left">\
              <div class="avatar_dashboard_friend_container">\
                '+ onlineHTML +'\
                <div class="avatar_user avatar_dashboard_friend">\
                  <a href="/user/profile/' + json[i].username + '"><img height="45" width="45" src="' + json[i].avatar_url + '"/></a>\
                </div>\
              </div>\
            </figure>\
            <div class="media-content wall_post_content">\
              <div class="content">\
                <strong><a href="/user/profile/' + json[i].username + '" class="username has-text-grey-darker">' + json[i].username + '</a></strong><span class="has-text-grey-light">&nbsp;<strong>Â·</strong>&nbsp;<small>' + json[i].time + '</small></span>\
                <p class="usercontent">' + json[i].content + '</p>\
              </div>\
            </div>\
            '+deleteHTML+'\
          </article>\
        </div>');
      }

      $(".deleteWallPost").click(function() {
        var postId = $(this).attr("data-id");
        showModal("Delete wall post",
          'Are you sure you want to delete this wall post?',
          '<button class="button is-danger" id="doDelete">Yes</button>');

        $("#doDelete").click(function() {
          $("#doDelete").attr("disabled", true).addClass("is-loading");
          $.post("/api/dashboard/delete-post", {
            postId: postId
          }).done(function(data) {
            if (data == "success") {
              $('[data-wallpost-id='+postId+']').remove();
              closeModal();
            }else if (data == "post-not-found")
              showModal("Error", "This wall post does not exist", "");
            else showModal("Error", "Due to a network error, this wall post could not be deleted.", "");
          }).fail(function() {
            showModal("Error", "Due to a network error, this wall post could not be deleted.", "");
          });
        });
      });

      if (pages > page) {
        $("#wallPostContainer").append(initHTML);
        loadInterval = setInterval(function() {
          if (isLoaderVisible() && loading == false) {
            loading = true;
            clearInterval(loadInterval);
            load_posts(page + 1);
          }
        }, 150);
      }
    }
  }).fail(function() {
    $(".loading_container").remove();
    if (loadInterval !== undefined)
      clearInterval(loadInterval);
  });
}
