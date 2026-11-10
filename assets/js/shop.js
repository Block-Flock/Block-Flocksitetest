// Shop functionality
let currentCategory = new URLSearchParams(window.location.search).get('category') || 1;
let currentQuery = new URLSearchParams(window.location.search).get('query') || null;
let currentShowAll = new URLSearchParams(window.location.search).get('showAll') === 'true';
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Initial load
    getItems(currentCategory, currentPage, currentQuery, currentShowAll);

    // Search input handler
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchItems();
            }
        });
    }

    // Category click handlers
    document.querySelectorAll('.shop-buttons .tab').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryId = this.dataset.category;
            filterCategory(categoryId);
        });
    });
});

function getItems(id, page, query, showAll) {
    const resultsContainer = document.querySelector('.search-results');
    if (resultsContainer) {
        resultsContainer.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>';
    }

    $.post('/api/store/get-items/', {
        id: id,
        page: page || 1,
        query: query || null,
        showAll: showAll || false
    }).done(function(data) {
        if (resultsContainer) {
            resultsContainer.innerHTML = data;
        }
        updateActiveMenu(id);
    }).fail(function() {
        if (resultsContainer) {
            resultsContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: red;">Failed to load items.</div>';
        }
    });
}

function filterCategory(categoryId) {
    currentCategory = categoryId;
    currentPage = 1;
    getItems(currentCategory, currentPage, currentQuery, currentShowAll);
    updateURL();
}

function searchItems() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        currentQuery = searchInput.value.trim();
        currentPage = 1;
        getItems(currentCategory || 1, currentPage, currentQuery, currentShowAll);
        updateURL();
    }
}

function toggleShowAll() {
    currentShowAll = !currentShowAll;
    currentPage = 1;
    getItems(currentCategory || 1, currentPage, currentQuery, currentShowAll);
    updateURL();
}

function resetFilters() {
    currentCategory = 1;
    currentQuery = null;
    currentShowAll = false;
    currentPage = 1;
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    
    getItems(currentCategory, currentPage, currentQuery, currentShowAll);
    updateURL();
}

function updateURL() {
    let url = '/store/';
    let params = [];
    
    if (currentCategory) params.push('category=' + currentCategory);
    if (currentQuery) params.push('query=' + encodeURIComponent(currentQuery));
    if (currentShowAll) params.push('showAll=true');
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    window.history.pushState({}, '', url);
}

function updateActiveMenu(activeId) {
    document.querySelectorAll('.shop-buttons .tab').forEach(button => {
        if (button.dataset.category == activeId) {
            button.classList.add('active'); // Assuming 'active' class for styling
        } else {
            button.classList.remove('active');
        }
    });
}
