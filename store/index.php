<?php
// 1. SETUP & CONFIGURATION
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

$title = "Store";

/* FIX: PHP must execute before string output */
$extra_head_content = '
<!-- Three.js Libraries for Avatar Rendering -->
<script src="/assets/vendor/three.min.js"></script>
<script src="/assets/vendor/GLTFLoader.js"></script>
<script src="/assets/vendor/OBJLoader.js"></script>
<link rel="stylesheet" href="/assets/css/shop.css?v=' . time() . '">
';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

// Get current filters from URL
$category = isset($_GET['category']) && is_numeric($_GET['category'])
    ? (int)$_GET['category']
    : null;

$query = isset($_GET['query']) ? trim($_GET['query']) : null;
$showAll = isset($_GET['showAll']) && $_GET['showAll'] === 'true';

// Check if user is admin (FIX: guard $my)
$isAdmin = $app->auth() && isset($my) && isset($my->admin) && $my->admin == 1;

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

<section class="store-hero has-text-centered">
    <div class="container">
        <h1 class="title is-2">Marketplace</h1>
        <h2 class="subtitle is-5">Discover items for your avatar</h2>

        <div class="search-container">
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input
                        class="input is-medium is-rounded"
                        type="text"
                        id="searchInput"
                        placeholder="Search items..."
                        value="<?= htmlspecialchars($query ?? '') ?>">
                </div>
                <div class="control">
                    <button class="button is-medium is-primary is-rounded" onclick="searchItems()">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
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
                        <li><a href="#" onclick="filterCategory(1);return false;" class="<?= $category === 1 ? 'is-active' : '' ?>">🎩 Hats</a></li>
                        <li><a href="#" onclick="filterCategory(7);return false;" class="<?= $category === 7 ? 'is-active' : '' ?>">👤 Heads</a></li>
                        <li><a href="#" onclick="filterCategory(6);return false;" class="<?= $category === 6 ? 'is-active' : '' ?>">💎 Accessories</a></li>
                        <li><a href="#" onclick="filterCategory(2);return false;" class="<?= $category === 2 ? 'is-active' : '' ?>">😀 Faces</a></li>
                        <li><a href="#" onclick="filterCategory(5);return false;" class="<?= $category === 5 ? 'is-active' : '' ?>">🛠️ Gears</a></li>
                    </ul>

                    <p class="menu-label mt-4"><b>User Creation</b></p>
                    <ul class="menu-list">
                        <li><a href="#" onclick="filterCategory(3);return false;" class="<?= $category === 3 ? 'is-active' : '' ?>">👕 Shirts</a></li>
                        <li><a href="#" onclick="filterCategory(4);return false;" class="<?= $category === 4 ? 'is-active' : '' ?>">👖 Pants</a></li>
                        <li><a href="#" onclick="filterCategory(8);return false;" class="<?= $category === 8 ? 'is-active' : '' ?>">👚 T-Shirts</a></li>
                    </ul>

                    <?php if ($isAdmin): ?>
                        <p class="menu-label mt-4"><b>Admin Options</b></p>
                        <ul class="menu-list">
                            <li><a href="#" onclick="toggleShowAll();return false;" class="<?= $showAll ? 'is-active' : '' ?>">👁️ Show All Items</a></li>
                            <li><a href="/admin/review-items.php">⚠️ Review Items</a></li>
                        </ul>
                    <?php endif; ?>

                    <p class="menu-label mt-4"><b>Others</b></p>
                    <ul class="menu-list">
                        <li><a href="/store/creator-area/">Creator Area</a></li>
                        <li><a href="#" onclick="resetFilters();return false;"><b>Reset Filters</b></a></li>
                    </ul>
                </aside>
            </div>

            <div class="column is-9">
                <div class="level mb-5">
                    <h3 class="title is-4" id="categoryTitle">
                        <?php
                        if ($query) {
                            echo 'Results for "' . htmlspecialchars($query) . '"';
                        } elseif ($category) {
                            $cat = getCategoryName($category);
                            echo $cat['icon'] . ' ' . $cat['name'];
                        } else {
                            echo 'All Items';
                        }
                        ?>
                        <?php if ($showAll && $isAdmin): ?>
                            <span class="tag is-warning ml-2">
                                Showing All (Including Unmoderated)
                            </span>
                        <?php endif; ?>
                    </h3>
                </div>

                <div id="data">
                    <div class="has-text-centered" style="padding:3rem;">
                        <i class="fas fa-spinner fa-pulse fa-3x"></i>
                        <p class="mt-3">Loading items...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script src="/assets/js/dashboard-avatars.js?v=<?= time() ?>"></script>
<script>
let currentCategory = <?= $category ? $category : 'null' ?>;
let currentQuery    = <?= json_encode($query) ?>;
let currentShowAll  = <?= $showAll ? 'true' : 'false' ?>;
let isAdmin         = <?= $isAdmin ? 'true' : 'false' ?>;

window.onload = function () {
    getItems(currentCategory ?? 1, 1, currentQuery, currentShowAll);
};

function getItems(id, page, query, showAll) {
    $.post('/api/store/get-items/', {
        id: id,
        page: page || 1,
        query: query || null,
        showAll: showAll || false
    }).done(function (data) {
        $('#data').html(data);
    });
}

function filterCategory(categoryId) {
    currentCategory = categoryId;
    updateActiveMenu();
    getItems(categoryId, 1, currentQuery, currentShowAll);
    updateURL();
}

function searchItems() {
    currentQuery = document.getElementById('searchInput').value.trim();
    getItems(currentCategory ?? 1, 1, currentQuery, currentShowAll);
    updateURL();
}

function toggleShowAll() {
    if (!isAdmin) return;
    currentShowAll = !currentShowAll;
    getItems(currentCategory ?? 1, 1, currentQuery, currentShowAll);
    updateURL();
}

function resetFilters() {
    currentCategory = null;
    currentQuery = null;
    currentShowAll = false;
    document.getElementById('searchInput').value = '';
    getItems(1, 1, null, false);
    updateURL();
    updateActiveMenu();
}

function updateURL() {
    let params = [];
    if (currentCategory) params.push('category=' + currentCategory);
    if (currentQuery) params.push('query=' + encodeURIComponent(currentQuery));
    if (currentShowAll) params.push('showAll=true');
    history.pushState({}, '', '/store/' + (params.length ? '?' + params.join('&') : ''));
}

function updateActiveMenu() {
    document.querySelectorAll('.menu-list a').forEach(a => a.classList.remove('is-active'));
    if (!currentCategory) return;

    document.querySelectorAll('.menu-list a').forEach(a => {
        if (a.getAttribute('onclick')?.includes('filterCategory(' + currentCategory + ')')) {
            a.classList.add('is-active');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('searchInput').addEventListener('keydown', e => {
        if (e.key === 'Enter') searchItems();
    });
});
</script>

<?php $app->footer(); ?>
