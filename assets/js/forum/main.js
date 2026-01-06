$(document).ready(function() {
  $("#searchButton").click(function() {
    var forumId = $("#searchButton").attr("data-id");
    showModal("Search " + $("#forum_name").text(),
      '<p>Enter a keyword to start searching this forum</p>\
      <div class="field">\
        <div class="control">\
        <input class="input" id="searchKeyword" type="text" maxlength="64" placeholder="Enter a keyword">\
        <div class="push-5"></div>\
        <button class="button is-info" id="searchModalBtn">Search</button>\
        </div>\
      </div>', "", 150, false);

      $("#searchModalBtn").click(function() {
        $("#searchModalBtn").attr("disabled", true).addClass("is-loading");
        $("#searchKeyword").attr("disabled", true);
        window.location = "/forum?id=" + forumId + "&keyword=" + $("#searchKeyword").val()
      });
  });
});
