var profile_username = "";

$(function() {
  profile_username = $("#moreFriendsButton").attr("data-username");
  initializeFriendButtons();
  $(".modal-card-body").css("overflow", "initial");

  $("#moreFriendsButton").click(function() {
    $(".modal-card").css("display", "block");
    showModal($("#moreFriendsButton").attr("data-username") + "'s friends",
      '<div id="modal_friendContainer">\
        <div class="has-text-centered animated-slow infinite flash">\
          <i class="fas fa-3x fa-spinner fa-spin has-text-grey-light"></i>\
          <div class="push-10"></div>\
          <p class="has-text-grey-light"><b>Loading friends...</b></p>\
        </div>\
      </div>', '', 35);

    friendModal_loadPage();
  });
});

function friendModal_loadPage(page = 1) {
  if (page < 1) return;
  $(".pagination-link").attr("disabled", true);
  $("li").addClass("li-disabled");

  $.post("/api/friends/fetch-user", {
    username: profile_username,
    page: page
  }).done(function(data) {
    if (data == "error")
      showModal("Error", "Due to a network error, the friends were unable to be displayed.");
    else if (data == "no-user")
      showModal("Error", "This user was not found");
    else {
      var json = JSON.parse(data);
      if (json.error !== undefined && json.error == "page-exceeds-amount-pages")
        friendModal_loadPage(json.amount_pages);
      else{
        var amount_pages = json.amount_pages;
        $("#globalModalTitle").text(profile_username + "'s friends ("+json.amount_friends+")");
        $("#modal_friendContainer").empty();

        if (json.amount_friends == 0)
          $("#modal_friendContainer").html('<p class="has-text-grey-light">This user has not yet added any friends</p>');
        else $("#modal_friendContainer").html('<div id="modal_friendContainer_columns" class="columns is-multiline is-mobile"></div>\
        <div id="modal_pagination"></div>');

        delete json.amount_pages;
        delete json.amount_friends;

        for (var i = 0; i < Object.keys(json).length; i++) {
          var online_html = (json[i].online) ? '<span class="is-online">â—</span>' :
            '<span class="has-text-grey-light">â—</span>';
          $("#modal_friendContainer_columns").append('<div class="column is-4">\
            <div class="has-text-centered">\
              <a href="/user/'+json[i].username+'"><img height="100" width="100" src="'+json[i].avatar_url+'"/></a>\
              <p class="text-truncate">'+online_html+' <a href="/user/'+json[i].username+'">'+json[i].username+'</a></p>\
            </div>\
          </div>')
        }

        $("#friend_pagination").remove();
        if (amount_pages > 1) {
          $("#modal_pagination").append('<nav id="friend_pagination" class="pagination is-centered" role="navigation" aria-label="pagination"></nav>');
          if (page > 1) $("#friend_pagination").append('<a onclick="friendModal_loadPage('+(page-1)+', profile_username)" class="is-hidden-mobile pagination-previous"><i class="fas fa-backward"></i>&nbsp;Previous</a>');
          else $("#friend_pagination").append('<button class="is-hidden-mobile pagination-previous disabled" disabled="true"><i class="fas fa-backward"></i>&nbsp;Previous</button>');
          $("#friend_pagination").append('<ul id="friend_pagination_list" class="pagination-list"></ul>');
          if (page > 2) $("#friend_pagination_list").append('<li><button onclick="friendModal_loadPage(1, profile_username)" class="pagination-link button">1</a></li><span class="pagination-ellipsis">&hellip;</span>');
          for (var i = (page - 1); i < page; i++)
            if (i >= 1)
              $("#friend_pagination_list").append('<li><button onclick="friendModal_loadPage('+i+', profile_username)" class="pagination-link button">'+i+'</button></li>');
          $("#friend_pagination_list").append('<li><button class="pagination-link button is-current">'+page+'</button></li>');
          for (var i = (page + 1); i < (page + 2) && i <= amount_pages; i++)
            $("#friend_pagination_list").append('<li><button onclick="friendModal_loadPage('+i+', profile_username)" class="pagination-link button">'+i+'</button></li>');
          if (page - (amount_pages - 1) < 0) $("#friend_pagination_list").append('<span class="pagination-ellipsis">&hellip;</span><li><button onclick="friendModal_loadPage('+amount_pages+', profile_username)" class="pagination-link button">'+amount_pages+'</button></li>');
          if (page == amount_pages)
            $("#friend_pagination").append('<button class="is-hidden-mobile pagination-next disabled" disabled="true">Next page&nbsp;<i class="fas fa-forward"></i></button>');
          else $("#friend_pagination").append('<a onclick="friendModal_loadPage('+(page+1)+', profile_username)" class="is-hidden-mobile pagination-next">Next page&nbsp;<i class="fas fa-forward"></li></a>');
        }
      }
    }
  }).fail(function() {
    showModal("Error", "Due to a network error, the friends were unable to be displayed.");
  }).always(function() {
    $(".pagination-link").attr("disabled", false);
    $("li").removeClass("li-disabled");
  });
}

// This will hook the onClick events for the buttons.
function initializeFriendButtons() {
  $("#add_friend").click(function() {
    $("#add_friend").addClass("is-loading").attr("disabled", true);
    var username = $(this).attr("data-username");
    $.post("/api/friends/add", {
      username: username
    }).done(function(data) {
      if (data == "rate-limit")
        showModal("Error", "You are sending friend requests too quickly, please wait before doing this again");
      else if (data == "cannot-add-yourself")
        showModal("Error", "You can not add yourself as a friend");
      else if (data == "already-established")
        showModal("Error", "You are either already friends with this user, or a request is already pending. Please refresh the page and try again");
      else if (data == "success") {
        $("#friendButtonContainer").html('<button data-username="'+ username +'" id="cancel_request" class="button is-danger is-fullwidth"><strong><i class="fas fa-times"></i> Cancel Request</strong></button>');
        initializeFriendButtons();
      }else showModal("Error", "Due to a network error, the friend request could not be sent. Please attempt again");
    }).fail(function() {
      showModal("Error", "Due to a network error, the friend request could not be sent. Please attempt again");
    }).always(function() {
      $("#add_friend").removeClass("is-loading").attr("disabled", false);
    });
  });

  $("#cancel_request").click(function() {
    $("#cancel_request").addClass("is-loading").attr("disabled", true);
    var username = $(this).attr("data-username");
    $.post("/api/friends/cancel", {
      username: username
    }).done(function(data) {
      if (data == "success") {
        $("#friendButtonContainer").html('<button data-username="'+ username +'" id="add_friend" class="button is-success is-fullwidth"><strong><i class="fas fa-plus"></i> Add Friend</strong></button>');
        initializeFriendButtons();
      }else showModal("Error", "Due to a network error, the friend request could not be canceled. Please attempt again");
    }).fail(function() {
      showModal("Error", "Due to a network error, the friend request could not be canceled. Please attempt again");
    }).always(function() {
      $("#cancel_request").removeClass("is-loading").attr("disabled", false);
    });
  });

  $("#accept_request").click(function() {
    $("#accept_request").addClass("is-loading").attr("disabled", true);
    var username = $(this).attr("data-username");
    $.post("/api/friends/accept", {
      username: username
    }).done(function(data) {
      if (data == "invalid-request")
        showModal("Error", "Invalid request. Please attempt again");
      else if (data == "already-friends")
        showModal("Error", "You are already friends with this user");
      else if (data == "success") {
        $("#friendButtonContainer").html('<button data-username="'+ username +'" id="remove_friend" class="button is-danger is-fullwidth"><strong><i class="fas fa-user-slash"></i> Remove Friend</strong></button>\
          <div class="push-5"></div>\
          <button data-username="'+ username +'" id="doChat" class="button is-info is-fullwidth"><strong><i class="fas fa-comments"></i> Chat</strong></button>');
        initializeFriendButtons();
      }else showModal("Error", "Due to a network error, the friend request could not be accepted. Please attempt again");
    }).fail(function() {
      showModal("Error", "Due to a network error, the friend request could not be accepted. Please attempt again");
    }).always(function() {
      $("#accept_request").removeClass("is-loading").attr("disabled", false);
    });
  });

  $("#ignore_request").click(function() {
    $("#ignore_request").addClass("is-loading").attr("disabled", true);
    var username = $(this).attr("data-username");
    $.post("/api/friends/ignore", {
      username: username
    }).done(function(data) {
      if (data == "success") {
        $("#friendButtonContainer").html('<button data-username="'+ username +'" id="add_friend" class="button is-success is-fullwidth"><strong><i class="fas fa-plus"></i> Add Friend</strong></button>');
        initializeFriendButtons();
      }else showModal("Error", "Due to a network error, the friend request could not be ignored. Please attempt again");
    }).fail(function() {
      showModal("Error", "Due to a network error, the friend request could not be ignored. Please attempt again");
    }).always(function() {
      $("#ignore_request").removeClass("is-loading").attr("disabled", false);
    });
  });

  $("#remove_friend").click(function() {
    var username = $(this).attr("data-username");
    showModal("Remove friend",
      "Are you sure you want to remove <b>"+username+"</b> as a friend?",
      '<button class="button is-danger" id="doRemove">Yes</button>');

    $("#doRemove").click(function() {
      $("#doRemove").addClass("is-loading").attr("disabled", true);
      $.post("/api/friends/remove", {
        username: username
      }).done(function(data) {
        if (data == "success") {
          closeModal();
          $("#friendButtonContainer").html('<button data-username="'+ username +'" id="add_friend" class="button is-success is-fullwidth"><strong><i class="fas fa-plus"></i> Add Friend</strong></button>');
          initializeFriendButtons();
        }else showModal("Error", "Due to a network error, your friend could not be removed. Please attempt again");
      }).fail(function() {
        showModal("Error", "Due to a network error, your friend could not be removed. Please attempt again");
      }).always(function() {
        $("#doRemove").removeClass("is-loading").attr("disabled", false);
      });
    });
  });

  $("#doChat").click(function() {
    var username = $(this).attr("data-username");
    $("#doChat").addClass("is-loading").attr("disabled", true);
    $.post("/api/chat/initialize-chat", {
      username: username
    }).done(function(data) {
      if (data.substr(0, 8) == "success-") {
        var json = JSON.parse(data.substr(8));
        chat_load_conversation(json.chat_id);
      }else showModal("Error", "Due to a network error, the chat session could not be started");
    }).fail(function() {
      showModal("Error", "Due to a network error, the chat session could not be started");
    }).always(function() {
      $("#doChat").removeClass("is-loading").attr("disabled", false);
    });
  });
}
