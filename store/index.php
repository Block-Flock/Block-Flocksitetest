<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

$title = 'Shop';

$extra_head_content = '
<link rel="stylesheet" href="/assets/css/shop.css?v=' . time() . '">
<script src="/assets/vendor/three.min.js"></script>
<script src="/assets/vendor/GLTFLoader.js"></script>
';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$category = isset($_GET['category']) && is_numeric($_GET['category'])
    ? (int)$_GET['category']
    : null;

$query = isset($_GET['query']) ? trim($_GET['query']) : null;
$showAll = isset($_GET['showAll']) && $_GET['showAll'] === 'true';

$isAdmin = $app->auth() && isset($my) && !empty($my->admin);
?>

<div class="main-holder grid">

    <!-- ALERT / NOTICE AREA -->
    <div class="col-10-12 push-1-12">
        <div class="alert warning">
            Welcome to the shop.
        </div>
    </div>

    <!-- SHOP CONTENT -->
    <div class="col-10-12 push-1-12 shop-container">

        <!-- SHOP HEADER -->
        <div class="shop-header">
            <h1>Shop</h1>

            <div class="shop-search">
                <input
                    type="text"
                    id="shop-search"
                    placeholder="Search items..."
                    value="<?= htmlspecialchars($query ?? '') ?>"
                >
            </div>
        </div>

        <!-- CATEGORY BAR -->
        <div class="shop-categories">
            <a href="#" data-category="" class="category<?= $category === null ? ' active' : '' ?>">All</a>
            <a href="#" data-category="1" class="category<?= $category === 1 ? ' active' : '' ?>">Hats</a>
            <a href="#" data-category="2" class="category<?= $category === 2 ? ' active' : '' ?>">Faces</a>
            <a href="#" data-category="3" class="category<?= $category === 3 ? ' active' : '' ?>">Shirts</a>
            <a href="#" data-category="4" class="category<?= $category === 4 ? ' active' : '' ?>">Pants</a>
            <a href="#" data-category="5" class="category<?= $category === 5 ? ' active' : '' ?>">Gears</a>
            <a href="#" data-category="6" class="category<?= $category === 6 ? ' active' : '' ?>">Accessories</a>
            <a href="#" data-category="7" class="category<?= $category === 7 ? ' active' : '' ?>">Heads</a>
        </div>

        <!-- ITEM GRID -->
        <div id="shop-items" class="shop-grid">
            <!-- Items injected via JS -->
        </div>

        <!-- LOAD MORE -->
        <div class="shop-footer">
            <button id="load-more">Load More</button>
        </div>

    </div>
</div>

<script>
const shopState = {
    category: <?= json_encode($category) ?>,
    query: <?= json_encode($query) ?>,
    showAll: <?= json_encode($showAll) ?>
};

function updateURL() {
    const params = new URLSearchParams();

    if (shopState.category !== null) params.set('category', shopState.category);
    if (shopState.query) params.set('query', shopState.query);
    if (shopState.showAll) params.set('showAll', 'true');

    history.replaceState({}, '', '?' + params.toString());
}

function loadItems(reset = false) {
    fetch('/api/shop/items?' + new URLSearchParams(shopState))
        .then(r => r.text())
        .then(html => {
            const container = document.getElementById('shop-items');
            if (reset) container.innerHTML = '';
            container.insertAdjacentHTML('beforeend', html);
        });
}

document.querySelectorAll('.category').forEach(cat => {
    cat.addEventListener('click', e => {
        e.preventDefault();
        shopState.category = cat.dataset.category || null;
        updateURL();
        loadItems(true);
    });
});

document.getElementById('shop-search').addEventListener('input', e => {
    shopState.query = e.target.value.trim() || null;
    updateURL();
    loadItems(true);
});

document.getElementById('load-more').addEventListener('click', () => {
    shopState.showAll = true;
    updateURL();
    loadItems();
});

loadItems(true);
</script>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php'); ?>
