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

<section class="section">
    <div class="store-container">
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

                <div id="data" class="item-grid">
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