var chatOpen, isChatLoading, inConversation = false;
var startId, chatLoadInterval, chatUpdateInterval = undefined;

$(document).ready(function() {
  $(".chat-collaspe").click(function() {
    if (inConversation) return;
    if (chatOpen) {
      $.cookie('chat_open', 'false', { expires: 7, path: '/' });
      $.cookie('chat_conversation_id', '0', { expires: 7, path: '/' });
      $(".chat-collaspe").removeClass("chat-active");
      $(".chat-container").remove();
      clearInterval(chatLoadInterval);
      clearTimeout(chatUpdateInterval);
      chatOpen = false;
    }else{
      $.cookie('chat_open', 'true', { expires: 7, path: '/' });
      chatOpen = true;
      $(".chat-collaspe").addClass("chat-active");
      $(".chatter").append('<div class="chat-container column-is-vcentered"></div>');
      chat_load_conversations();
    }
  });

  // Check if cookies are set, if they're not then we set them with default values. We also update their expirations.
  if ($.cookie('chat_open') === undefined) $.cookie('chat_open', 'false', { expires: 7, path: '/' });
  else $.cookie('chat_open', $.cookie('chat_open'), { expires: 7, path: '/' });
  if ($.cookie('chat_conversation_id') === undefined) $.cookie('chat_conversation_id', '0', { expires: 7, path: '/' });
  else $.cookie('chat_conversation_id', $.cookie('chat_conversation_id'), { expires: 7, path: '/' });
  if ($.cookie('chat_open') == 'true') {
    if ($.cookie('chat_conversation_id') == '0') $(".chat-collaspe").click();
    else chat_load_conversation($.cookie('chat_conversation_id'));
  }
});

function chat_load_conversations() {
  clearInterval(chatLoadInterval);
  clearTimeout(chatUpdateInterval);
  $("#chat_summary_container").empty();
  $.cookie('chat_conversation_id', '0', {expires: 7, path: '/'});
  $(".chat-container").html('<div class="chat-loader"><i class="fas fa-spinner fa-spin fa-4x has-text-grey-lighter"></i></div>');
  $.get("/api/chat/fetch-chats").done(function(data) {
    inConversation = false;
    var json = JSON.parse(data);
    $(".chat-container").empty();
    for (var i = 0; i < Object.keys(json).length; i++) {
      var last_message_html = (json[i].last_username.length == 0) ? ""
        : '<span class="has-text-grey-light"><span class="chat_username" title="'+json[i].last_username+'"><b class="text-truncate">\
        '+json[i].last_username+'</b></span>: '+json[i].last_message+'</span>';
      $(".chat-container").append('<div class="media chatInstance">\
        <figure class="media-left">\
          <div class="avatar_user avatar_dashboard_friend">\
            <img height="45" width="45" src="'+json[i].chat_image+'">\
          </div>\
        </figure>\
        <div class="media-content chats_container usercontent">\
          <p class="text-truncate"><b class="chat_name" title="'+json[i].chat_name+'" data-id="'+json[i].chat_id+'">'+json[i].chat_name+'</b></p>'+last_message_html+'\
        </div>\
      </div>');
    }

    if (Object.keys(json).length == 0) {
      $(".chat-container").html('<div class="chat-error-message">\
        <i class="far fa-comments fa-3x"></i>\
        <p><strong class="has-text-grey-light">You are not in any chat...</strong></p>\
        <p class="has-text-grey-light">To chat with a friend, go to their profile and click the chat button</p>\
        <div class="push-5"></div>\
        <a class="button" href="/friends"><i class="fas fa-user-friends"></i>&nbsp;&nbsp;My friends</a>\
      </div>');
    }

    $(".chat_name").click(function() {
      var chat_conversation_id = $(this).attr("data-id");
      $.cookie('chat_conversation_id', chat_conversation_id, { expires: 7, path: '/' });
      chat_load_conversation(chat_conversation_id);
    });
  });
}

function chat_load_information(chatId) {
  if (typeof(chatId) === undefined) return;
  $.get("/api/chat/fetch-chat-summary?chatId=" + chatId).done(function(data) {
    var json = JSON.parse(data);
    $("#chat_summary_container").html('- <span class="chatNameContainer" title="'+json.chat_name+'"><span class="text-truncate">' + json.chat_name + '</span></span>\
      <span class="is-pulled-right">\
        <div class="dropdown is-hoverable">\
          <div class="dropdown-trigger">\
            <a class="button is-small chatMemberButton tooltip" data-tooltip="Chat members"><i class="fas fa-user"></i>&nbsp;'+json.member_count+'</a>\
          </div>\
          <div class="dropdown-menu chatMemberDropdownMenu animated fadeIn faster" id="dropdown-menu4" role="menu">\
            <div class="dropdown-content chatMemberDropdown">\
              <div class="dropdown-item chatMemberDropdownItem">\
                <div id="chatMemberContainer"></div>\
              </div>\
            </div>\
          </div>\
        </div>\
        <button class="button is-small closeChatButton"><i class="fas fa-times"></i></button>\
      </span>');

    for (var i = 0; i < Object.keys(json.members).length; i++) {
      $("#chatMemberContainer").append('<div class="media">\
        <figure class="media-left">\
          <div class="avatar_dashboard_friend_container">\
            <div class="avatar_user avatar_dashboard_friend">\
              <a href="/user/profile/'+json.members[i].username+'"><img height="45" width="45" src="'+json.members[i].avatar_url+'"></a>\
            </div>\
          </div>\
        </figure>\
        <div class="media-content">\
          <strong><a href="/user/profile/'+json.members[i].username+'" class="username has-text-grey-darker">'+json.members[i].username+'</strong>\
        </div>\
      </div>');
    }

    $(".closeChatButton").click(function() {
        chat_load_conversations();
    });
  }).fail(function() {
    chat_load_conversations();
  });
}

function chat_load_conversation(chatId, page = 1) {
  if (typeof(chatId) === undefined) return;
  if ($.cookie('chat_conversation_id') != chatId) $.cookie('chat_conversation_id', chatId, { expires: 7, path: '/' });
  if ($.cookie('chat_open') != 'true') $.cookie('chat_open', 'true', { expires: 7, path: '/' });
  if (page == 1) {
    if (!inConversation)
      inConversation = true;

    if (!chatOpen) {
      $(".chat-collaspe").addClass("chat-active");
      $(".chatter").append('<div class="chat-container"></div>');
      chatOpen = true;
    }

    startId = undefined;
    $(".chat-container").html('<div class="message-container"><div class="chat-loader"><i class="fas fa-spinner fa-spin fa-4x has-text-grey-lighter"></i></div></div>\
      <div class="sender-container">\
        <div class="field has-addons">\
          <div class="control is-expanded">\
            <input class="input" type="text" id="chatTextSender" maxlength="255" placeholder="Send a message...">\
          </div>\
          <div class="control">\
            <button class="button is-info" id="chatButtonSender" data-chatId="'+chatId+'"><i class="far fa-paper-plane"></i></button>\
          </div>\
        </div>\
      </div>');

    chat_load_information(chatId);
    $("#chatButtonSender").prop("disabled", true);
    $("#chatTextSender").prop("disabled", true);

    $("#chatButtonSender").click(function() {
      var content = $("#chatTextSender").val();
      $("#chatButtonSender").prop("disabled", true).addClass("is-loading");
      $("#chatTextSender").prop("disabled", true);
      var localMessage = renderMessage("1 second ago",
        $('<div/>').text(content).html(),
        $(".chatter").attr("data-username"),
        $(".chatter").attr("data-avatar_url"),
        true,
        false
      ).css("color", "#9e9e9e");

      $.post("/api/chat/send-message", {
        content: content,
        chatId: $("#chatButtonSender").attr("data-chatId")
      }).done(function(data) {
        $("#chatTextSender").val("");
        if (data == "content-too-short" || data == "content-too-long" || data == "bad-content" || data == "not-friends") {
          localMessage.remove();
          $("#chatTextSender").addClass("is-danger");

          if (data == "content-too-short")
            $("#chatTextSender").attr("placeholder", "Please type a message...");
          else if (data == "content-too-long")
            $("#chatTextSender").attr("placeholder", "Your message is too long...");
          else if (data == "bad-content")
            $("#chatTextSender").attr("placeholder", "Do not use profanity...");
          else if (data == "not-friends")
            $("#chatTextSender").attr("placeholder", "You must be friends to DM");
        }else if (data == "success") {
          localMessage.css("color", "#4a4a4a");
          $("#chatTextSender").removeClass("is-danger").attr("placeholder", "Send a message...");
        }
        else localMessage.css("color", "red").find(".chat_date").find("small").text("Not sent");
      }).always(function() {
        $("#chatButtonSender").prop("disabled", false).removeClass("is-loading");
        $("#chatTextSender").prop("disabled", false);
        $('#chatTextSender').focus();
      }).fail(function() {
        localMessage.css("color", "red").find(".chat_date").find("small").text("Not sent");
      });
    });

    $("#chatTextSender").keyup(function(e) {
      if (e.keyCode == 13) $("#chatButtonSender").click();
    });
  }

  var params = (startId === undefined) ? "?chatId=" + chatId : "?chatId=" + chatId + "&startId=" + startId
  $.get("/api/chat/fetch-messages" + params + "&page=" + page).done(function(data) {
    $(".chat-loader").remove();
    $(".chatLoader").remove();
    $("#chatButtonSender").prop("disabled", false);
    $("#chatTextSender").prop("disabled", false);
    $('#chatTextSender').focus();

    isChatLoading = false;
    var json = JSON.parse(data);
    if (startId === undefined) {
      startId = (json.start_id !== undefined) ? json.start_id : 0;
      updateMessages(chatId, startId);
    }

    delete json.start_id;
    var current_top_element = ($(".chatInstance:first").length) ? $(".chatInstance:first") : undefined;
    clearInterval(chatLoadInterval);
    for (var i = 0; i < Object.keys(json).length; i++)
      renderMessage(json[i].time, json[i].content, json[i].username, json[i].avatar_url, json[i].online);

    // We will only show the loader and use the loader when 10 (potentially a next page possible) is true.
    if (Object.keys(json).length == 10) {
      $(".message-container").prepend('<div class="chatLoader"><div class="push-5"></div>\
        <div class="has-text-centered push-10"><i class="fas fa-spinner fa-spin fa-2x has-text-grey-lighter"></i><p class="has-text-grey-lighter">Loading older messages...</p></div>\
      </div>');
      chatLoadInterval = setInterval(function() {
        if ($(".chatLoader").offset().top > $(".message-container").offset().top && !isChatLoading) {
          isChatLoading = true;
          clearInterval(chatLoadInterval);
          chat_load_conversation(chatId, page + 1);
        }
      }, 150);

      // Scroll to the last first message in the message container.
      if (current_top_element !== undefined)
        $('.message-container').scrollTop(current_top_element.position().top);
    }

    if (page == 1)
      $(".message-container").scrollTop($(".message-container")[0].scrollHeight);
  }).fail(function(xhr) {
    isChatLoading = false;
    $(".chatLoader").remove();

    // If the initial load fails (or a 404 happened), go back to previous view.
    if (page == 1 || xhr.status == 404)
      chat_load_conversations();
  });
}

function updateMessages(chatId, startIndex) {
  var lastStartIndex = startIndex;
  $.get("/api/chat/fetch-messages?chatId=" + chatId + "&lastId=" + startIndex).done(function(data) {
    if (data == "error") return;
    var json = JSON.parse(data);
    last_id = (json.start_id !== undefined) ? json.start_id : lastStartIndex;

    delete json.start_id;
    for (var i = 0; i < Object.keys(json).length; i++)
      renderMessage(json[i].time, json[i].content, json[i].username, json[i].avatar_url, json[i].online, false);

    // Check if we have any new last message ID to go after and if we do then we assign it to our startIndex.
    if (chatUpdateInterval !== undefined) clearTimeout(chatUpdateInterval);
    chatUpdateInterval = setTimeout(function() {
      if (chatOpen && inConversation)
        updateMessages(chatId, last_id);
    }, 1250);
  });
}

function renderMessage(messageTime, messageContent, senderUsername, senderAvatar, senderOnline, prepend = true) {
  var onlineHTML = (senderOnline) ? '<div title="'+senderUsername+' is online" class="avatar_online_circle circle-xs"></div>' : '';
  var messageHTML = '<div class="media chatInstance">\
    <figure class="media-left">\
      <div class="avatar_dashboard_friend_container">\
        '+ onlineHTML +'\
        <div class="avatar_user avatar_dashboard_friend">\
          <a href="/user/profile/' + senderUsername + '"><img height="45" width="45" src="' + senderAvatar + '"/></a>\
        </div>\
      </div>\
    </figure>\
    <div class="media-content chat_message_content">\
      <div class="chat_details"><div class="chat_username" title="' + senderUsername + '"><strong class="text-truncate"><a href="/user/profile/' + senderUsername + '" class="username has-text-grey-darker">' + senderUsername + '</a></strong></div><div class="chat_date"><span class="has-text-grey-light">&nbsp;<strong>Â·</strong>&nbsp;<small>' + messageTime + '</small></span></div></div>\
      <p class="usercontent">' + messageContent + '</p>\
    </div>\
  </div>';

  if (prepend)
    $(".message-container").prepend(messageHTML);
  else {
    var msg = $(messageHTML).appendTo('.message-container');
    $(".message-container").scrollTop($(".message-container")[0].scrollHeight);
    return msg;
  }
}
