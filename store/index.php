<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

$title = "Store";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

/* ---------------------------
   INPUT / STATE
---------------------------- */
$category = isset($_GET['category']) && is_numeric($_GET['category'])
    ? (int)$_GET['category']
    : null;

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$showAll = isset($_GET['showAll']) && $_GET['showAll'] === 'true';

$isAdmin = $app->auth() && isset($my->admin) && $my->admin == 1;

$placeholders = [
    "🎩 Amazing Top Hat",
    "🧢 Billy's Baseball Cap",
    "👒 Flower Hat",
    "👕 Polo",
    "👚 Dress",
    "🛠️ Handy Gear"
];

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
?>

<div class="col-2-12">
    <div class="content shop-buttons">

        <div class="shop-button <?= !$category ? 'is-active' : '' ?>" data-category="">
            All Items
        </div>

        <?php foreach ($categories as $id => $cat): ?>
            <div class="shop-button <?= $category === $id ? 'is-active' : '' ?>"
                 data-category="<?= $id ?>">
                <?= $cat['icon'] ?> <?= $cat['name'] ?>
            </div>
        <?php endforeach; ?>

        <?php if ($isAdmin): ?>
            <div class="shop-button <?= $showAll ? 'is-active' : '' ?>"
                 data-showall="true">
                👁️ Show All Items
            </div>
            <a href="/admin/review-items.php" class="shop-button">
                ⚠️ Review Items
            </a>
        <?php endif; ?>

        <a href="/store/creator-area/" class="shop-button">Creator Area</a>
        <div class="shop-button" data-reset="true"><b>Reset Filters</b></div>
    </div>
</div>

<div class="col-10-12">
    <div class="shop-search">
        <input
            id="searchInput"
            class="search not-default width-100"
            type="text"
            placeholder="<?= $placeholders[array_rand($placeholders)] ?>"
            value="<?= htmlspecialchars($query) ?>"
        />
    </div>

    <div class="search-results">
        <div class="col-10-12 result">
            <?php
            if ($query) {
                echo 'Results for "' . htmlspecialchars($query) . '"';
            } elseif ($category && isset($categories[$category])) {
                echo $categories[$category]['icon'] . ' ' . $categories[$category]['name'];
            } else {
                echo 'All Items';
            }
            ?>

            <?php if ($showAll && $isAdmin): ?>
                <span class="tag is-warning ml-2">
                    Showing All (Including Unmoderated)
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="item-container" id="data">
        <div class="has-text-centered" style="padding:3rem;">
            <i class="fas fa-spinner fa-pulse fa-3x"></i>
            <p class="mt-3">Loading items…</p>
        </div>
    </div>
</div>

<script>
/* ---------------------------
   CLIENT STATE
---------------------------- */
let currentCategory = <?= $category ? $category : 'null' ?>;
let currentQuery    = <?= json_encode($query) ?>;
let currentShowAll  = <?= $showAll ? 'true' : 'false' ?>;
let isAdmin         = <?= $isAdmin ? 'true' : 'false' ?>;

/* ---------------------------
   CORE FUNCTIONS
---------------------------- */
function loadItems(page = 1) {
    $.post('/api/store/get-items/', {
        id: currentCategory,
        page: page,
        query: currentQuery || null,
        showAll: currentShowAll
    }).done(html => {
        $('#data').html(html);
    });
}

function updateURL() {
    const params = new URLSearchParams();
    if (currentCategory) params.set('category', currentCategory);
    if (currentQuery) params.set('query', currentQuery);
    if (currentShowAll) params.set('showAll', 'true');

    history.pushState({}, '', '/store/' + (params.toString() ? '?' + params : ''));
}

function updateActiveButtons() {
    document.querySelectorAll('.shop-button').forEach(btn => {
        btn.classList.remove('is-active');

        if (btn.dataset.category !== undefined &&
            btn.dataset.category == (currentCategory ?? '')) {
            btn.classList.add('is-active');
        }

        if (btn.dataset.showall && currentShowAll) {
            btn.classList.add('is-active');
        }
    });
}

/* ---------------------------
   EVENTS
---------------------------- */
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.shop-button[data-category]').forEach(btn => {
        btn.addEventListener('click', () => {
            currentCategory = btn.dataset.category || null;
            updateActiveButtons();
            loadItems();
            updateURL();
        });
    });

    document.querySelectorAll('.shop-button[data-showall]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!isAdmin) return;
            currentShowAll = !currentShowAll;
            updateActiveButtons();
            loadItems();
            updateURL();
        });
    });

    document.querySelectorAll('.shop-button[data-reset]').forEach(btn => {
        btn.addEventListener('click', () => {
            currentCategory = null;
            currentQuery = '';
            currentShowAll = false;
            document.getElementById('searchInput').value = '';
            updateActiveButtons();
            loadItems();
            updateURL();
        });
    });

    document.getElementById('searchInput').addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            currentQuery = e.target.value.trim();
            loadItems();
            updateURL();
        }
    });

    loadItems();
});
</script>

<?php $app->footer(); ?>
