window.onload = function() {
    getinv(1, 1);
}

function getItems(id, page) {
    $.post('/api/inventory/', {
        id: id,
        page: page
    }).done(function(data){
        $("#data").html(data);
    })
}
