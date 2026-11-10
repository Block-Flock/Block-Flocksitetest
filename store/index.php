<?php
// 1. SETUP & CONFIGURATION
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

$title = "Store";

// Define extra head content to include avatar scripts
$extra_head_content = '
<!-- Three.js Libraries for Avatar Rendering -->
<script src="/assets/vendor/three.min.js"></script>
<script src="/assets/vendor/GLTFLoader.js"></script>
<script src="/assets/vendor/OBJLoader.js"></script>
';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

// Get current filters from URL
$category = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : null;
$query = isset($_GET['query']) ? trim($_GET['query']) : null;
$showAll = isset($_GET['showAll']) && $_GET['showAll'] == 'true';

// Check if user is admin
$isAdmin = $app->auth() && $my && isset($my->admin) && $my->admin == 1;

// Function to get category name by ID
function getCategoryName($type) {
    $categories = [
        1 => ['name' => 'Hats', 'icon' => '🎩'],
        2 => ['name' => 'Faces', 'icon' => '😀'],
        3 => ['name' => 'Shirts', 'icon' => '👕'],
        4 => ['name' => 'Pants', 'icon' => '👖'],
        5 => ['name' => 'Gears', 'icon' => '🛠️'],
        6 => ['name' => 'Accessories', 'icon' => '💎'],
        7 => ['name' => 'Heads', 'icon' => '👤'],
        8 => ['name' => 'T-Shirts', 'icon' => '👚']
    ];
    return $categories[$type] ?? ['name' => 'Unknown', 'icon' => '❓'];
}
?>

<style>
    .store-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 1.5rem;
        border-radius: 0 0 20px 20px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .store-hero .title { color: white; }
    .store-hero .subtitle { color: #e0e0e0; }
    
    .menu-list a {
        border-radius: 8px;
        transition: all 0.2s;
    }
    .menu-list a.is-active {
        background-color: #764ba2;
        box-shadow: 0 4px 10px rgba(118, 75, 162, 0.4);
        color: white !important;
    }
    .menu-list a:hover:not(.is-active) {
        background-color: #f5f5f5;
        transform: translateX(5px);
    }

    .item-card {
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }
    .item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .price-tag {
        background-color: #23d160;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.9rem;
        display: inline-block;
    }
    .unmoderated-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #ff9800;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: bold;
        z-index: 10;
    }
    .canvas-container canvas {
        position: absolute;
        top: 50%;
        left: 50%;

        transform: translate(-50%, -50%);

        max-width: 100%;
        max-height: 100%;

        width: auto !important;
        height: auto !important;

        display: block;
    }
    }
    .canvas-container canvas {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }
    .category-icon {
        margin-right: 8px;
        font-size: 1.2em;
    }
    .admin-controls {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }
</style>

<section class="store-hero has-text-centered">
    <div class="container">
        <h1 class="title is-2">Marketplace</h1>
        <h2 class="subtitle is-5">Discover items for your avatar</h2>
        
        <div class="columns is-centered mt-4">
            <div class="column is-6">
                <div class="field has-addons">
                    <div class="control is-expanded">
                        <input class="input is-medium is-rounded" type="text" id="searchInput" placeholder="Search items..." value="<?php echo htmlspecialchars($query ?? ''); ?>">
                    </div>
                    <div class="control">
                        <button class="button is-medium is-primary is-rounded" onclick="searchItems()">Search</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
                <aside class="menu box p-4">
                    <p class="menu-label"><b>Item Type</b></p>
                    <ul class="menu-list">
                        <li><a href="#" onclick="filterCategory(1); return false;" class="<?= $category == 1 ? 'is-active' : '' ?>">🎩 Hats</a></li>
                        <li><a href="#" onclick="filterCategory(7); return false;" class="<?= $category == 7 ? 'is-active' : '' ?>">👤 Heads</a></li>
                        <li><a href="#" onclick="filterCategory(6); return false;" class="<?= $category == 6 ? 'is-active' : '' ?>">💎 Accessories</a></li>
                        <li><a href="#" onclick="filterCategory(2); return false;" class="<?= $category == 2 ? 'is-active' : '' ?>">😀 Faces</a></li>
                        <li><a href="#" onclick="filterCategory(5); return false;" class="<?= $category == 5 ? 'is-active' : '' ?>">🛠️ Gears</a></li>
                    </ul>
                    <p class="menu-label mt-4"><b>User Creation</b></p>
                    <ul class="menu-list">
                        <li><a href="#" onclick="filterCategory(3); return false;" class="<?= $category == 3 ? 'is-active' : '' ?>">👕 Shirts</a></li>
                        <li><a href="#" onclick="filterCategory(4); return false;" class="<?= $category == 4 ? 'is-active' : '' ?>">👖 Pants</a></li>
                        <li><a href="#" onclick="filterCategory(8); return false;" class="<?= $category == 8 ? 'is-active' : '' ?>">👚 T-Shirts</a></li>
                    </ul>
                    
                    <?php if($isAdmin): ?>
                    <p class="menu-label mt-4"><b>Admin Options</b></p>
                    <ul class="menu-list">
                        <li><a href="#" onclick="toggleShowAll(); return false;" class="<?= $showAll ? 'is-active' : '' ?>">👁️ Show All Items</a></li>
                        <li><a href="/admin/review-items.php">⚠️ Review Items</a></li>
                    </ul>
                    <?php endif; ?>
                    
                    <p class="menu-label mt-4"><b>Others</b></p>
                    <ul class="menu-list">
                        <li><a href="/store/creator-area/">Creator Area</a></li>
                        <li><a href="#" onclick="resetFilters(); return false;"><b>Reset Filters</b></a></li>
                    </ul>
                </aside>
            </div>

            <div class="column is-9">
                <div class="level mb-5">
                    <div class="level-left">
                        <div class="level-item">
                            <h3 class="title is-4" id="categoryTitle">
                                <?php 
                                if ($query) {
                                    echo 'Results for "' . htmlspecialchars($query) . '"';
                                } elseif ($category) {
                                    $catInfo = getCategoryName($category);
                                    echo $catInfo['icon'] . ' ' . $catInfo['name'];
                                } else {
                                    echo 'All Items';
                                }
                                ?>
                                <?php if($showAll && $isAdmin): ?>
                                    <span class="tag is-warning ml-2">Showing All (Including Unmoderated)</span>
                                <?php endif; ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div id="data">
                    <!-- Items will be loaded here via AJAX -->
                    <div class="has-text-centered" style="padding: 3rem;">
                        <span class="icon is-large">
                            <i class="fas fa-spinner fa-pulse fa-3x"></i>
                        </span>
                        <p class="mt-3">Loading items...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="/assets/js/dashboard-avatars.js?v=<?php echo time(); ?>"></script>
<script>
// Store state
let currentCategory = <?php echo $category ? $category : 'null'; ?>;
let currentQuery = <?php echo $query ? json_encode($query) : 'null'; ?>;
let currentShowAll = <?php echo $showAll ? 'true' : 'false'; ?>;
let isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

// Load items on page load
window.onload = function() {
    // Load initial items based on URL parameters
    if (currentCategory) {
        getItems(currentCategory, 1, currentQuery, currentShowAll);
    } else {
        getItems(1, 1, currentQuery, currentShowAll); // Default to hats
    }
};

// Modified getItems function to match API parameters
function getItems(id, page, query, showAll) {
    $.post('/api/store/get-items/', {
        id: id,
        page: page || 1,
        query: query || null,
        showAll: showAll || false
    }).done(function(data){
        $("#data").html(data);
    });
}

// Filter by category
function filterCategory(categoryId) {
    currentCategory = categoryId;
    updateActiveMenu();
    getItems(categoryId, 1, currentQuery, currentShowAll);
    updateURL();
}

// Search items
function searchItems() {
    currentQuery = document.getElementById('searchInput').value.trim();
    getItems(currentCategory || 1, 1, currentQuery, currentShowAll);
    updateURL();
}

// Toggle show all (admin only)
function toggleShowAll() {
    if (!isAdmin) return;
    currentShowAll = !currentShowAll;
    getItems(currentCategory || 1, 1, currentQuery, currentShowAll);
    updateURL();
}

// Reset filters
function resetFilters() {
    currentCategory = null;
    currentQuery = null;
    currentShowAll = false;
    document.getElementById('searchInput').value = '';
    getItems(1, 1, null, false);
    updateURL();
    updateActiveMenu();
}

// Update URL without page reload
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

// Update active menu item
function updateActiveMenu() {
    document.querySelectorAll('.menu-list a').forEach(link => {
        link.classList.remove('is-active');
    });
    
    if (currentCategory) {
        document.querySelectorAll('.menu-list a').forEach(link => {
            if (link.getAttribute('onclick') && link.getAttribute('onclick').includes('filterCategory(' + currentCategory + ')')) {
                link.classList.add('is-active');
            }
        });
    }
}

// Allow Enter key in search box
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchItems();
        }
    });
});
</script>
<?php $app->footer(); ?>