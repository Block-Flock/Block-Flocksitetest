window.onload = function() {
    // Auto-load category 1 (hats) on initial page load
    getItems(1, 1);
}

function getItems(id, page, query, showAll) {
    $.post('/api/store/get-items/', {
        id: id,
        page: page,
        query: query || null,
        showAll: showAll || false
    }).done(function(data){
        $("#data").html(data);
    })
}