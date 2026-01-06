$(document).ready(function() {
  $("#uploadItem").click(function() {
    $("#uploadItem").attr("disabled", true).addClass("is-loading");
    $("textarea").attr("disabled", true);
    $("select").attr("disabled", true);
    $(".help").empty().removeClass("is-danger").removeClass("is-success");
    $("input").attr("disabled", true).removeClass("is-danger").removeClass("is-success");

    var formData = new FormData();
    formData.append('name', $("#name").val());
    formData.append('description', $("#description").val());
    formData.append('price', $("#price").val());
    formData.append('buyable', $('#buyable :selected').val());
    formData.append('type', $("#uploadItem").attr("data-type"));
  	formData.append('file', $("#itemFile")[0].files[0]);

    jQuery.ajax({
      type: "POST",
      cache: false,
      url: "/api/store/new",
      data: formData,
      enctype: 'multipart/form-data',
      processData: false,
      contentType: false
    }).fail(function() {
      $("#message").addClass("is-danger").text("Due to a network error, your item could not be uploaded");
    }).always(function() {
      $("#uploadItem").attr("disabled", false).removeClass("is-loading");
      $("input").attr("disabled", false);
      $("textarea").attr("disabled", false);
      $("select").attr("disabled", false);
    }).done(function(data) {
      if (data == "service-unavailable") $("#message").addClass("is-danger").text("The service is currently unavailable. Please try again later");
      else if (data == "rate-limit") $("#message").addClass("is-danger").text("You have uploaded an item too recently. Please wait before doing that again");
      else if (data == "missing-information") {
        if ($("#name").val().trim().length == 0) {
          $("#name").addClass("is-danger");
          $("#name_message").addClass("is-danger").text("Please enter a name for your item");
        }

        if ($("#price").val().trim().length == 0) {
          $("#price").addClass("is-danger");
          $("#price_message").addClass("is-danger").text("Please enter a price for your item");
        }
      }else if (data == "no-name" || data == "name-too-short" || data == "name-too-long" || data.substr(0, 9) == "bad-name-") {
        $("#name").addClass("is-danger");
        $("#name_message").addClass("is-danger");

        if (data == "no-name")
          $("#name_message").text("Please enter a name for your item");

        if (data == "name-too-short")
          $("#name_message").text("Your name is too short. It should be at least 3 characters long");

        if (data == "name-too-long")
          $("#name_message").text("Your name is too long. It can not be longer than 64 characters long");

        if (data.substr(0, 9) == "bad-name-")
          $("#name_message").text('Your name contains a bad word. Please remove "' + data.substr(9) + '" from your name and try again');
      }else if (data == "description-too-long" || data.substr(0, 16) == "bad-description-") {
        $("#description").addClass("is-danger");
        $("#description_message").addClass("is-danger");

        if (data == "description-too-long")
          $("#description_message").text("Your description is too long. It can not be longer than 256 characters");

        if (data.substr(0, 16) == "bad-description-")
          $("#description_message").text('Your description contains a bad word. Please remove "' + data.substr(16) + '" from your description and try again');
      }else if (data == "invalid-price" || data == "price-too-low" || data == "price-too-high") {
        $("#price").addClass("is-danger");
        $("#price_message").addClass("is-danger");

        if (data == "invalid-price")
          $("#price_message").text("That price is invalid. Prices can only contain numbers without decimals");

        if (data == "price-too-low")
          $("#price_message").text("Your price is too low. It should be at least 1 cube");

        if (data == "price-too-high")
          $("#price_message").text("Your price is too high. Your price can not be higher than 9,999,999,999 cubes");
      }else if (data == "invalid-buyable") $("#buyable_message").addClass("is-danger").text("Invalid option. Please refresh the page and try again");
      else if (data == "invalid-type") $("#message").addClass("is-danger").text("Invalid item type. Please refresh the page and try again");
      else if (data == "no-file" || data == "invalid-image" || data == "image-too-large" || data == "incorrect-dimensions") {
        $("#file_message").addClass("is-danger");

        if (data == "no-file")
          $("#file_message").text("Please select a file to continue");

        if (data == "invalid-image")
          $("#file_message").text("Please choose a valid image to continue");

        if (data == "image-too-large")
          $("#file_message").text("That image is too large. An image can be up to 1 MB in size");

        if (data == "incorrect-dimensions")
          $("#file_message").text("Incorrect size(s). Your image must be 1024x1024. Did you use the template?");
      }else if (data == "upload-error")
        $("#message").addClass("is-danger").text("An error has occurred. Please try again. If this problem keeps persisting, please contact an administrator");
      else if (data.substr(0, 8) == "success-") {
        var json = JSON.parse(data.substr(8));
        window.location = "/store/view?id=" + json.id
      }else $("#message").addClass("is-danger").text("Due to a network error, your item could not be uploaded");
    });
  });

  $("#itemFile").change(function() {
    if ($("#itemFile")[0].files[0] === undefined)
      return;

    var formData = new FormData();
    formData.append('file', $("#itemFile")[0].files[0]);
    formData.append('type', $("#uploadItem").attr("data-type"));
    $("#previewImage").hide();
    $(".loaderContainer").show();

    jQuery.ajax({
      type: "POST",
      cache: false,
      url: "/api/store/render-preview",
      data: formData,
      enctype: 'multipart/form-data',
      processData: false,
      contentType: false
    }).done(function(data) {
      $("#previewImage").attr("src", "data:image/png;base64," + data.image);
    }).always(function() {
      $("#previewImage").show();
      $(".loaderContainer").hide();
    });
  });
});
