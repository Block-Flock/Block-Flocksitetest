var done = false;
var lasturl = "";
var inventorypage = 1;
var inventorytype = "hats";
var request;
var isLoaded = true;

$(document).ready(function() {
  $(".character_color").click(function() {
    showColorModal($(this).attr("id"));
  });

  $("#characterImage")[0].onload = function() {
    if (done) {
      $(".characterGenderToggleBtn").fadeIn('fast');
      $("#characterImage").fadeIn('fast');
      $(".loaderContainer").hide();
    }
  };

  loadInventory();
  loadWearing();
  $('#hatsBtn').click(() => {categoryClick($("#hatsBtn"), "hats")});
  $("#facesBtn").click(() => {categoryClick($("#facesBtn"), "faces")});
  $('#toolsBtn').click(() => {categoryClick($("#toolsBtn"), "tools")});
  $('#shirtsBtn').click(() => {categoryClick($("#shirtsBtn"), "shirts")});
  $('#pantsBtn').click(() => {categoryClick($("#pantsBtn"), "pants")});

  $("#regenBtn").click(function() {
    $("#regenBtn").addClass("is-loading").attr("disabled", true);
    $(".characterGenderToggleBtn").hide();
    $("#characterImage").hide();
    $(".loaderContainer").show();
    done = false;

    $.post("/api/rendering/refresh.php").done(function(data) {
      if (data == "render-offline") {
        showModal("Backend server error", "Your character was unable to be updated because our render server did not respond. Please try again later");
        $("#"+part).css("background-color", oldColor);
      }else if (data.substr(0, 8) == "success-") {
        var dataJson = JSON.parse(data.substr(8));
        if (lasturl != dataJson.url) {
          $(".characterGenderToggleBtn").hide();
          $("#characterImage").hide();
          $(".loaderContainer").show();
        }

        lasturl = dataJson.url;
        $("#characterImage").attr("src", dataJson.url);
        $(".avatar_navimg").attr("src", dataJson.thumb_url);
        done = true;
      }else showModal("Error", "Due to a network error, your avatar failed to update");
    }).fail(function() {
      showModal("Error", "Due to a network error, your avatar failed to update");
    }).always(function() {
      $("#regenBtn").removeClass("is-loading").attr("disabled", false);
    });
  });

  $(".characterGenderToggleBtn").click(function() {
    $(".characterGenderToggleBtn").hide();
    $("#characterImage").hide();
    $(".loaderContainer").show();

    done = false;
    $.post("/api/character/toggle-gender").done(function(data) {
      if (data.substr(0, 8) == "success-") {
        var json = JSON.parse(data.substr(8));
        if (lasturl != json.url) {
          $(".characterGenderToggleBtn").hide();
          $("#characterImage").hide();
          $(".loaderContainer").show();
        }

        if (json.new_gender == "male") {
          $(".characterGenderToggleBtn").removeClass("toggleMale").addClass("toggleFemale").attr("data-tooltip", "Click here to toggle the female character");
          $("#genderIcon").removeClass("fa-male").addClass("fa-female");
        }if (json.new_gender == "female") {
          $(".characterGenderToggleBtn").removeClass("toggleFemale").addClass("toggleMale").attr("data-tooltip", "Click here to toggle the male character");
          $("#genderIcon").removeClass("fa-female").addClass("fa-male");
        }
        lasturl = json.url;
        $("#characterImage").attr("src", json.url);
        $(".avatar_navimg").attr("src", json.thumb_url);
        done = true;
      }
    }).fail(function() {
      showModal("Error", "Due to a network error, your avatar failed to update");
    });
  });
});

function rgb2hex(rgb) {
  rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);
  function hex(x) {
    return ("0" + parseInt(x).toString(16)).slice(-2);
  }
  return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

function showColorModal(part) {
  var characterColors = ["#000000", "#030303", "#0c090a", "#2c3539", "#2b1b17", "#34282c", "#25383c", "#3b3131", "#413839", "#3d3c3a", "#463e3f", "#4c4646", "#504a4b", "#565051", "#5c5858", "#625d5d", "#666362", "#6d6968", "#726e6d", "#736f6e", "#837e7c", "#848482", "#b6b6b4", "#d1d0ce", "#eeeeee", "#bcc6cc", "#98afc7", "#6d7b8d", "#657383", "#616d7e", "#646d7e", "#566d7e", "#737ca1", "#4863a0", "#2b547e", "#34495e", "#2b3856", "#151b54", "#000080", "#342d7e", "#15317e", "#151b8d", "#0000a0", "#0020c2", "#0041c2", "#2554c7", "#1569c7", "#2b60de", "#1f45fc", "#6960ec", "#736aff", "#357ec7", "#368bc1", "#488ac7", "#3090c7", "#659ec7", "#87afc7", "#95b9c7", "#728fce", "#2b65ec", "#306eff", "#157dec", "#1589ff", "#6495ed", "#6698ff", "#38acec", "#56a5ec", "#2196f3", "#5cb3ff", "#3bb9ff", "#79baec", "#82cafa", "#82caff", "#a0cfec", "#b7ceec", "#b4cfec", "#c2dfff", "#c6deff", "#afdcec", "#addfff", "#bdedff", "#93ffe8", "#9afeff", "#7fffd4", "#00ffff", "#7dfdfe", "#57feff", "#8eebec", "#50ebec", "#4ee2ec", "#81d8d0", "#92c7c7", "#77bfc7", "#78c7c7", "#48cccd", "#43c6db", "#46c7c7", "#7bccb5", "#43bfc7", "#3ea99f", "#3b9c9c", "#438d80", "#348781", "#307d7e", "#5e7d7e", "#4c787e", "#008080", "#4e8975", "#78866b", "#848b79", "#617c58", "#728c00", "#667c26", "#254117", "#306754", "#347235", "#437c17", "#137e07", "#387c44", "#347c2c", "#347c17", "#348017", "#4e9258", "#6aa121", "#4aa02c", "#41a317", "#3ea055", "#6cbb3c", "#6cc417", "#4cc417", "#52d017", "#4cc552", "#54c571", "#99c68e", "#89c35c", "#85bb65", "#8bb381", "#9cb071", "#b2c248", "#9dc209", "#a1c935", "#7fe817", "#59e817", "#57e964", "#64e986", "#5efb6e", "#00ff00", "#5ffb17", "#87f717", "#8afb17", "#6afb92", "#98ff98", "#b5eaaa", "#c3fdb8", "#ccfb5d", "#b1fb17", "#bce954", "#edda74", "#ede275", "#f8e709", "#ffe87c", "#ffdb58", "#ffd801", "#fdd017", "#eac117", "#f5c86a", "#f2bb66", "#fbb917", "#fbb117", "#ffa62f", "#e9ab17", "#e2a76f", "#deb887", "#f9dda2", "#ffcba4", "#c9be62", "#e8a317", "#ee9a4d", "#c8b560", "#d4a017", "#c2b280", "#c7a317", "#c68e17", "#b5a642", "#ada96e", "#c19a6b", "#cd7f32", "#c88141", "#c58917", "#af9b60", "#af7817", "#b87333", "#966f33", "#806517", "#827839", "#827b60", "#786d5f", "#493d26", "#483c32", "#613b09", "#6f4e37", "#835c3b", "#7f5217", "#7f462c", "#c47451", "#c36241", "#c35817", "#c85a17", "#cc6600", "#e56717", "#e66c2c", "#f87217", "#f87431", "#e67451", "#ff8040", "#f88017", "#ff7f50", "#f88158", "#f9966b", "#e78a61", "#e18b6b", "#e77471", "#f75d59", "#e55451", "#e55b3c", "#ff0000", "#ff2400", "#f62217", "#f70d1a", "#c30404", "#f62817", "#e42217", "#e41b17", "#dc381f", "#c34a2c", "#c24641", "#c04000", "#c11b17", "#9f000f", "#990012", "#8c001a", "#954535", "#7e3517", "#8a4117", "#7e3817", "#800517", "#810541", "#7d0541", "#7e354d", "#7d0552", "#7f4e52", "#7f5a58", "#7f525d", "#b38481", "#c5908e", "#c48189", "#c48793", "#e8adaa", "#ecc5c0", "#edc9af", "#fdd7e4", "#fcdfff", "#ffdfdd", "#fbbbb9", "#faafbe", "#faafba", "#f9a7b0", "#e7a1b0", "#e799a3", "#e38aae", "#f778a1", "#e56e94", "#f660ab", "#fc6c85", "#f6358a", "#f52887", "#e45e9d", "#e4287c", "#f535aa", "#ff00ff", "#e3319d", "#f433ff", "#fc6cd8", "#d16587", "#c25a7c", "#ca226b", "#c12869", "#c12267", "#c25283", "#c12283", "#b93b8f", "#7e587e", "#571b7e", "#583759", "#4b0082", "#461b7e", "#4e387e", "#614051", "#5e5a80", "#6a287e", "#7d1b7e", "#a74ac7", "#b048b5", "#a36cde", "#6c2dc7", "#842dce", "#8d38c9", "#7a5dc7", "#7f38ec", "#8e35ef", "#893bff", "#8467d7", "#a23bec", "#b041ff", "#c45aec", "#9172ec", "#9e7bff", "#d462ff", "#e238ec", "#c38ec7", "#c8a2c8", "#e6a9ec", "#e0b0ff", "#c6aec7", "#f9b7ff", "#d2b9d3", "#d9cfec", "#ebdde2", "#e3e4fa"];
  var colors = {
    "headColor": "head", "bodyColor": "body", "leftArmColor":"left arm", "rightArmColor": "right arm", "leftArmColor": "left arm",
    "rightLegColor": "right leg", "leftLegColor": "left leg"
  };

  if (colors[part] == undefined) return;
  showModal("Change " + colors[part] + " color", '<div class="has-text-centered" id="colorContainer"></div>', "", 0);
  for (var i = 0; i < characterColors.length; i++)
    $("#colorContainer").append('<div class="colorBtn has-hover-anim" style="background-color:'+characterColors[i]+'">');

  $(".colorBtn").click(function() {
    var oldColor = $("#"+part).css("background-color");
    var color = $(this).css("background-color");
    $("#"+part).css("background-color", color);
    closeModal(false);
    $(".characterGenderToggleBtn").hide();
    $("#characterImage").hide();
    $(".loaderContainer").show();
    done = false;

    $.post("/api/character/update-color", {
      newColor: rgb2hex(color),
      bodyPart: part
    }).done(function(data) {
      if (data == "render-offline") {
        showModal("Backend server error", "Your character was unable to be updated because our render server did not respond. Please try again later", "", 150, true, false);
        $("#"+part).css("background-color", oldColor);
      }else if (data.substr(0, 8) == "success-") {
        var dataJson = JSON.parse(data.substr(8));
        if (lasturl != dataJson.url) {
          $(".characterGenderToggleBtn").hide();
          $("#characterImage").hide();
          $(".loaderContainer").show();
        }

        lasturl = dataJson.url;
        $("#characterImage").attr("src", dataJson.url);
        $(".avatar_navimg").attr("src", dataJson.thumb_url);
        done = true;
      }else if (data == "unchanged") {
        $(".characterGenderToggleBtn").show();
        $("#characterImage").show();
        $(".loaderContainer").hide();
      }else {
        showModal("Error", "Due to a network error, your avatar failed to update", "", 150, true, false);
        $("#"+part).css("background-color", oldColor);
      }
    }).fail(function() {
      showModal("Error", "Due to a network error, your avatar failed to update", "", 150, true, false);
      $("#"+part).css("background-color", oldColor);
    });
  });
}

function categoryClick(button, type) {
  if (button.hasClass("li-disabled")) return;
  $("li").removeClass("is-active");
  $(button).addClass("is-active");
  loadInventory(1, type);
}

function loadInventory(page = inventorypage, type = inventorytype) {
  if (page < 1) return;
  if (isLoaded == false) return;
  isLoaded = false;
  $(".pagination-link").attr("disabled", true);
  inventorypage = page;
  inventorytype = type;

  $("li").addClass("li-disabled");
  if (request != undefined) request.abort();
  request = $.post("/api/character/inventory", {
    page: page,
    type: type
  }).done(function(data) {
    $("#inventory_columns").empty();
    if (data == "error") {
      $("#inventory_columns").html('<div class="column">\
        <p class="has-text-danger">An error has occurred. Please try again</p>\
      </div>');
      $("#inventory_pagination").remove();
    }else {
      var json = JSON.parse(data);
      var pages = json.pages;
      delete json.pages;

      if (Object.keys(json).length == 0)
        $("#inventory_columns").html('<div class="column">\
          <p class="has-text-danger">It looks like you do not own any items in this category! Why not find something to wear in our <a href="/store">store</a>?</p>\
        </div>');

      for (var i = 0; i < Object.keys(json).length; i++) {
        var wear_btn = (json[i].wearing)
          ? '<button onclick="item_action(' + json[i].id + ', \'unwear\');" class="button is-small is-danger item_'+json[i].id+'"><i class="fas fa-times"></i>&nbsp;<strong>Unwear</strong></button>'
          : '<button onclick="item_action(' + json[i].id + ', \'wear\');" class="button is-small is-info item_'+json[i].id+'"><i class="fas fa-plus"></i>&nbsp;<strong>Wear</strong></button>';
        $("#inventory_columns").append('<div class="column is-6-mobile is-3-tablet">\
            <div class="inventory_result">\
              <div class="has-text-centered">\
                <a href="/store/view?id='+json[i].id+'" target="_blank">\
                  <img height="100" width="100" src="'+json[i].thumbnail+'">\
                </a>\
              </div>\
              <a class="title is-6 storetitle" href="/store/view?id='+json[i].id+'" target="_blank">'+json[i].name+'</a>\
              <p class="has-text-grey-light">By <a href="/user/profile/'+json[i].creator+'" target="_blank">'+json[i].creator+'</a></p>\
              '+wear_btn+'\
            </div>\
        </div>');
      }

      $("#inventory_pagination").remove();
      if (pages > 1) {
        $("#inventory").append('<nav id="inventory_pagination" class="pagination is-centered is-small" role="navigation" aria-label="pagination"></nav>');
        if (page > 1) $("#inventory_pagination").append('<a onclick="loadInventory('+(page-1)+', \''+type+'\')" class="is-hidden-mobile pagination-previous"><i class="fas fa-backward"></i>&nbsp;Previous</a>');
        else $("#inventory_pagination").append('<button class="is-hidden-mobile pagination-previous disabled" disabled="true"><i class="fas fa-backward"></i>&nbsp;Previous</button>');
        $("#inventory_pagination").append('<ul id="inventory_pagination_list" class="pagination-list"></ul>');
        if (page > 2) $("#inventory_pagination_list").append('<li><button onclick="loadInventory(1, \''+type+'\')" class="pagination-link button">1</a></li><span class="pagination-ellipsis">&hellip;</span>');
        for (var i = (page - 1); i < page; i++)
          if (i >= 1)
            $("#inventory_pagination_list").append('<li><button onclick="loadInventory('+i+', \''+type+'\')" class="pagination-link button">'+i+'</button></li>');
        $("#inventory_pagination_list").append('<li><button class="pagination-link button is-current">'+page+'</button></li>');
        for (var i = (page + 1); i < (page + 2) && i <= pages; i++)
          $("#inventory_pagination_list").append('<li><button onclick="loadInventory('+i+', \''+type+'\')" class="pagination-link button">'+i+'</button></li>');
        if (page - (pages - 1) < 0) $("#inventory_pagination_list").append('<span class="pagination-ellipsis">&hellip;</span><li><button onclick="loadInventory('+pages+', \''+type+'\')" class="pagination-link button">'+pages+'</button></li>');
        if (page == pages)
          $("#inventory_pagination").append('<button class="is-hidden-mobile pagination-next disabled" disabled="true">Next page&nbsp;<i class="fas fa-forward"></i></button>');
        else $("#inventory_pagination").append('<a onclick="loadInventory('+(page+1)+', \''+type+'\')" class="is-hidden-mobile pagination-next">Next page&nbsp;<i class="fas fa-forward"></li></a>');
      }
    }
  }).fail(function(jqXHR, textStatus, errorThrown) {
    if (errorThrown != "abort")
      $("#inventory_columns").html('<div class="column">\
          <p class="has-text-danger">Could not load your inventory at this time. Please attempt again later</p>\
        </div>');
  }).always(function() {
    isLoaded = true;
    $("li").removeClass("li-disabled");
    $(".pagination-link").attr("disabled", false);
  });
}

function loadWearing() {
  $.get("/api/character/wearing").done(function(data) {
    if (data == "error")
      $("#wearing_columns").html('<div class="column">\
          <p class="has-text-danger">Due to a network error, your items could not be displayed</p>\
        </div>');
    else if (data == "[]")
    $("#wearing_columns").html('<div class="column">\
        <p>It looks like you are wearing nothing right now. When you wear something from your inventory, it will show up here</p>\
      </div>');
    else {
      $("#wearing_columns").empty();
      var json = JSON.parse(data);
      for (var i = 0; i < Object.keys(json).length; i++)
        $("#wearing_columns").append('<div class="column is-6-mobile is-3-tablet">\
          <div class="inventory_result">\
            <div class="has-text-centered">\
              <a href="/store/view?id='+json[i].id+'">\
                <img height="100" width="100" src="'+json[i].thumbnail+'">\
              </a>\
            </div>\
            <a class="title is-6 storetitle" href="/store/view?id='+json[i].id+'">'+json[i].name+'</a>\
            <p class="has-text-grey-light">By <a href="/user/profile/'+json[i].creator+'">'+json[i].creator+'</a></p>\
            <button onclick="item_action(' + json[i].id + ', \'unwear\');" class="button is-small is-danger item_'+json[i].id+'"><i class="fas fa-times"></i>&nbsp;<strong>Unwear</strong></button>\
          </div>\
        </div>');
    }
  }).fail(function() {
    $("#wearing_columns").html('<div class="column">\
        <p class="has-text-danger">Could not show your wearing items at this time. Please attempt again later</p>\
      </div>');
  });
}

function item_action(itemId, action) {
  if (action != "wear" && action != "unwear") return;
  if (itemId == undefined) return;

  $(".item_" + itemId).attr("disabled", true).addClass("is-loading");
  $(".characterGenderToggleBtn").hide();
  $("#characterImage").hide();
  $(".loaderContainer").show();
  done = false;

  var api = (action == "wear") ? "/api/character/wear" : "/api/character/unwear";
  $.post(api, {
    item_id: itemId
  }).done(function(data) {
    if (data == "render-offline") showModal("Error", "The server did not respond. Please try again later");
    else if (data.substr(0, 8) == "success-") {
      var dataJson = JSON.parse(data.substr(8));
      if (lasturl != dataJson.url) {
        $(".characterGenderToggleBtn").hide();
        $("#characterImage").hide();
        $(".loaderContainer").show();
      }

      loadInventory();
      loadWearing();
      lasturl = dataJson.url;
      $("#characterImage").attr("src", dataJson.url);
      $(".avatar_navimg").attr("src", dataJson.thumb_url);
      done = true;
    }else if (data == "item-not-approved" || data == "item-not-found") {
      $(".characterGenderToggleBtn").show();
      $("#characterImage").show();
      $(".loaderContainer").hide();
      $(".item_" + itemId).attr("disabled", false).removeClass("is-loading");

      if (data == "item-not-approved")
        showModal("Error", "This item could not be used because it has either not been approved yet, or has been deleted");
    }else showModal("Error", "Due to a network error, your avatar failed to update");
  }).fail(function() {
    showModal("Error", "Due to a network error, your avatar failed to update");
  });
}
