var initHTML = "";

$(function() {
    initHTML = $("#friends_container").html();
    loadPage();
});

function loadPage(page = 1, loadAnimation = true) {
    if (page < 1) return;
    $(".pagination-link").attr("disabled", true);
    $("li").addClass("li-disabled");
    if (loadAnimation)
        $("#friends_container").html(initHTML);
    $.post("/api/friends/fetch", {
        page: page
    }).done(function(data) {
        if (data == "error")
            showModal("Error", "Due to a network error, the friend list could not be fetched. Please attempt again");
        else {
            var json = JSON.parse(data);
            if (json.error !== undefined && json.error == "page-exceeds-amount-pages")
                loadPage(json.amount_pages);
            else {
                $("#friends_container").empty();
                var amount_pages = json.amount_pages;
                $("#friend_count").html('(<span id="friend_count_number">' + json.amount_friends + '</span>)');

                if (json.amount_friends == 0)
                    $("#message").html('<p class="has-text-grey-light">You have not yet added any friends to be showed here</p>');

                delete json.amount_pages;
                delete json.amount_friends;

                for (var i = 0; i < Object.keys(json).length; i++) {
                    $("#friends_container").append('<div class="column is-4">\
            <div class="box userBox friendBox">\
              <div class="columns is-mobile">\
                <div class="column is-4 userColumn">\
                  <div class="avatar_user">\
                    <img class="avatar_userimg" src="' + json[i].avatar_url + '"/>\
                  </div>\
                </div>\
                <div class="column is-8 userColumn">\
                  <p><a href="/user/profile/' + json[i].username + '" class="vixlatio-title is-size-6 usercontent username">' + json[i].username + '</a></p>\
                  <button data-username="' + json[i].username + '" class="button remove_friend"><strong><i class="fas fa-user-slash"></i> Remove Friend</strong></button>\
                </div>\
              </div>\
            </div>\
          </div>');
                }

                $(".remove_friend").click(function() {
                    var btn = $(this);
                    var username = btn.attr("data-username");
                    showModal("Remove friend",
                        "Are you sure you want to remove <b>" + username + "</b> as a friend?",
                        '<button class="button is-danger" id="doRemove">Yes</button>');

                    $("#doRemove").click(function() {
                        $("#doRemove").addClass("is-loading").attr("disabled", true);
                        $.post("/api/friends/remove", {
                            username: username
                        }).done(function(data) {
                            if (data == "success") {
                                closeModal();
                                btn.parent().parent().parent().parent().remove();
                                $("#friend_count_number").html(parseFloat($("#friend_count_number").html()) - 1);
                                loadPage(page, false);
                            } else showModal("Error", "Due to a network error, your friend could not be removed. Please attempt again");
                        }).fail(function() {
                            showModal("Error", "Due to a network error, your friend could not be removed. Please attempt again");
                        }).always(function() {
                            $("#doRemove").removeClass("is-loading").attr("disabled", false);
                        });
                    });
                });

                $("#friend_pagination").remove();
                if (amount_pages > 1) {
                    $("#pagination").append('<nav id="friend_pagination" class="pagination is-centered" role="navigation" aria-label="pagination"></nav>');
                    if (page > 1) $("#friend_pagination").append('<a onclick="loadPage(' + (page - 1) + ')" class="is-hidden-mobile pagination-previous"><i class="fas fa-backward"></i>&nbsp;Previous</a>');
                    else $("#friend_pagination").append('<button class="is-hidden-mobile pagination-previous disabled" disabled="true"><i class="fas fa-backward"></i>&nbsp;Previous</button>');
                    $("#friend_pagination").append('<ul id="friend_pagination_list" class="pagination-list"></ul>');
                    if (page > 2) $("#friend_pagination_list").append('<li><button onclick="loadPage(1)" class="pagination-link button">1</a></li><span class="pagination-ellipsis">&hellip;</span>');
                    for (var i = (page - 1); i < page; i++)
                        if (i >= 1)
                            $("#friend_pagination_list").append('<li><button onclick="loadPage(' + i + ')" class="pagination-link button">' + i + '</button></li>');
                    $("#friend_pagination_list").append('<li><button class="pagination-link button is-current">' + page + '</button></li>');
                    for (var i = (page + 1); i < (page + 2) && i <= amount_pages; i++)
                        $("#friend_pagination_list").append('<li><button onclick="loadPage(' + i + ')" class="pagination-link button">' + i + '</button></li>');
                    if (page - (amount_pages - 1) < 0) $("#friend_pagination_list").append('<span class="pagination-ellipsis">&hellip;</span><li><button onclick="loadPage(' + amount_pages + ')" class="pagination-link button">' + amount_pages + '</button></li>');
                    if (page == amount_pages)
                        $("#friend_pagination").append('<button class="is-hidden-mobile pagination-next disabled" disabled="true">Next page&nbsp;<i class="fas fa-forward"></i></button>');
                    else $("#friend_pagination").append('<a onclick="loadPage(' + (page + 1) + ')" class="is-hidden-mobile pagination-next">Next page&nbsp;<i class="fas fa-forward"></li></a>');
                }
            }
        }
    }).fail(function() {
        showModal("Error", "Due to a network error, the friend list could not be fetched. Please attempt again");
    }).always(function() {
        $(".pagination-link").attr("disabled", false);
        $("li").removeClass("li-disabled");
    });
}