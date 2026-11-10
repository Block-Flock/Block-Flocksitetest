<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

$title = "Store";

// Define extra head content to include avatar scripts and new shop styles
$extra_head_content = '
<script src="/assets/vendor/three.min.js"></script>
<script src="/assets/vendor/GLTFLoader.js"></script>
<script src="/assets/vendor/OBJLoader.js"></script>
<link rel="stylesheet" href="/assets/css/shop.css?v=' . time() . '">
';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

// Get current filters from URL
$category = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : 1;
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

<div class="container" style="margin-top: 20px;">
    <div class="columns"> <!-- Using 'columns' wrapper assuming it might still be needed for layout context, or just a generic wrapper -->
        
        <!-- Sidebar: Categories -->
        <div class="col-2-12">
            <div class="shop-buttons">
                <div class="header">Item Type</div>
                <a href="#" data-category="1" class="tab <?php echo $category == 1 ? 'active' : ''; ?>">Hats</a>
                <a href="#" data-category="7" class="tab <?php echo $category == 7 ? 'active' : ''; ?>">Heads</a>
                <a href="#" data-category="6" class="tab <?php echo $category == 6 ? 'active' : ''; ?>">Accessories</a>
                <a href="#" data-category="2" class="tab <?php echo $category == 2 ? 'active' : ''; ?>">Faces</a>
                <a href="#" data-category="5" class="tab <?php echo $category == 5 ? 'active' : ''; ?>">Gears</a>
                
                <div class="header" style="margin-top: 20px;">User Creation</div>
                <a href="#" data-category="3" class="tab <?php echo $category == 3 ? 'active' : ''; ?>">Shirts</a>
                <a href="#" data-category="4" class="tab <?php echo $category == 4 ? 'active' : ''; ?>">Pants</a>
                <a href="#" data-category="8" class="tab <?php echo $category == 8 ? 'active' : ''; ?>">T-Shirts</a>
                
                <?php if($isAdmin): ?>
                    <div class="header" style="margin-top: 20px;">Admin</div>
                    <a href="#" onclick="toggleShowAll(); return false;" class="tab <?php echo $showAll ? 'active' : ''; ?>">Show All</a>
                    <a href="/admin/review-items.php" class="tab">Review Items</a>
                <?php endif; ?>
                
                <div class="header" style="margin-top: 20px;">Others</div>
                <a href="/store/creator-area/" class="tab">Creator Area</a>
                <a href="#" onclick="resetFilters(); return false;" class="tab">Reset Filters</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-10-12">
            <!-- Search Bar -->
            <div class="shop-search">
                <input type="text" id="searchInput" placeholder="Search for items..." value="<?php echo htmlspecialchars($query ?? ''); ?>">
                <div class="advanced">
                    <!-- Advanced filters could go here -->
                    <button class="btn-search" onclick="searchItems()">Search</button>
                </div>
            </div>

            <!-- Results Container -->
            <div class="search-results item-container">
                <!-- Items will be loaded here via AJAX -->
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-pulse fa-3x"></i>
                    <p>Loading items...</p>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pageButtons" style="text-align: center; margin-top: 20px;">
                <!-- Pagination buttons will be injected by JS/PHP response if needed -->
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/shop.js?v=<?php echo time(); ?>"></script>

<?php $app->footer(); ?>
