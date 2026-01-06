function getSub(id, page) {

    $.post('/api/forum/get-forum/', {
        id: id,
        page: page
    }).done(function(data) {
        $("#data").html(data);
    })

}