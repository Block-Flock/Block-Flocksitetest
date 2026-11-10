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
<link rel="stylesheet" href="/assets/css/shop.css?v=<?php echo time(); ?>">
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
<?php
// PHP from user's snippet, adapted
$ads = true; // Placeholder, not strictly used for display based on existing header
$pageName = "Shop"; // Placeholder
$placeholders = [ "🎩 Amazing Top Hat", "🧢 Billy's Baseball Cap", "👒 Flower Hat", "🐱 Something Hot", "🔨 Handy Man", "👚 Dress", "👕 Polo" ];

$perPage = [12,24,36,48];
$approval = [ 0 => "Only Approved", 2 => "Only Unapproved", 3 => "Only Declined", 1 => "All" ];
$saleType = [ 1 => "On Sale", 2 => "Offsale",0 => "Both" ];
$fields = [
    "query" => "",
    "category" => "threeo", // This will be overridden by existing $category if present
    "sort" => "",
    "creator" => "",
    "min" => "",
    "max" => "",
    "page" => "1",
    "perPage" => "",
    "showVerified" => "",
    "saleType" => "0"
];
$advancedFields = [
    "showVerified",
    "perPage",
    "min",
    "max",
    "creator",
    "saleType"
];
foreach ($fields as $filter => $value) {
    if (isset($_GET[$filter])) {
        if (is_string($_GET[$filter])) {
            $fields[$filter] = htmlentities($_GET[$filter]);
        }
    }
}
// Override $fields["query"] and $fields["category"] with existing $query and $category
if (!is_null($query)) {
    $fields["query"] = htmlspecialchars($query);
}
if (!is_null($category)) {
    // Map existing numeric categories to "threeo" or "all" if necessary, or keep numeric
    // For now, let's keep it numeric and use the existing getCategoryName function for display
    $fields["category"] = $category;
}
// If showAll is true, set showVerified to "All" (1)
if ($showAll) {
    $fields["showVerified"] = 1;
}

$advanced = false;
foreach ($advancedFields as $filter) {
    if (!empty(trim($fields[$filter]))) {
        $advanced = true;
        break;
    }
}
?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
(adsbygoogle = window.adsbygoogle || []).push({
google_ad_client: "ca-pub-9016364683167219",
enable_page_level_ads: true
});
</script>
<script>
    var defaultSearch = <?=json_encode($fields)?>;
</script>
<div class="col-2-12">
	<div class="content shop-buttons">
		<?php
		// Using the categories from the original file, adapted to the new HTML structure
		// The original categories in the user's snippet were static or from $bop->getCategories()
		// I will use the existing getCategoryName function to populate buttons
        // Also add an "All" button
        ?>
        <div class="shop-button" data-category="all">
            All Items
        </div>
        <?php
		$categories_list = [
            1 => ['name' => 'Hats', 'icon' => '🎩'],
            2 => ['name' => 'Faces', 'icon' => '😀'],
            3 => ['name' => 'Shirts', 'icon' => '👕'],
            4 => ['name' => 'Pants', 'icon' => '👖'],
            5 => ['name' => 'Gears', 'icon' => '🛠️'],
            6 => ['name' => 'Accessories', 'icon' => '💎'],
            7 => ['name' => 'Heads', 'icon' => '👤'],
            8 => ['name' => 'T-Shirts', 'icon' => '👚']
        ];
		foreach ($categories_list as $id => $catInfo) {
		?>
		<div class="shop-button <?= $category == $id ? 'is-active' : '' ?>" data-category="<?=$id?>">
			<?=$catInfo['icon']?> <?=$catInfo['name']?>
		</div>
		<?php } ?>
        <?php if($isAdmin): ?>
        <div class="shop-button <?= $showAll ? 'is-active' : '' ?>" data-showall="true">
            👁️ Show All Items
        </div>
        <a href="/admin/review-items.php" class="shop-button">⚠️ Review Items</a>
        <?php endif; ?>
        <a href="/store/creator-area/" class="shop-button">Creator Area</a>
        <div class="shop-button" data-reset="true"><b>Reset Filters</b></div>
	</div>
</div>
<div class="col-10-12">
	<div class="col-11-12">
		<div class="shop-search">
			<input class="search not-default width-100" type="text" placeholder="<?=$placeholders[rand(0, count($placeholders) - 1)]?>" value="<?=$fields["query"]?>" id="searchInput" />
			<div class="advanced" style="<?=($advanced) ? "" : "display:none"?>">
				<div class="col-1-5">
					Creator <br><input id="creator" class="not-default" type="text" value="<?=$fields["creator"]?>" />
				</div>
				<div class="col-1-5">
					Min Price<br><input id="min" class="not-default" type="text" value="<?=$fields["min"]?>" />
				</div>
				<div class="col-1-5">
					Max Price<br><input id="max" class="not-default" type="text" value="<?=$fields["max"]?>"/>
				</div>
				<div class="col-1-5">
					Page<br><input id="page" class="not-default" type="text" value="<?=$fields["page"]?>"/>
				</div>
				<div class="col-1-5">
					Per Page<br><select class="not-default" id="perPage">
						<?php
						foreach ($perPage as $option) {
							$selected = ($option == $fields["perPage"]) ? "selected" : "";
							?>
							<option value="<?=$option?>" <?=$selected?>><?=$option?></option>
							<?php
						}
						?>
						</select>
				</div>
				<div class="col-1-5">
					Approval Sort<br><select class="not-default" id="showApproved">
					<?php
						foreach ($approval as $option => $name) {
							$selected = ($option == $fields["showVerified"]) ? "selected" : "";
							?>
							<option value="<?=$option?>" <?=$selected?>><?=$name?></option>
							<?php
						}
					?>
					</select>
				</div>
				<div class="col-1-5">
					Sale Type Sort<br><select class="not-default" id="saleType">
					<?php
						foreach ($saleType as $option => $name) {
							$selected = ($option == $fields["saleType"]) ? "selected" : "";
							?>
							<option value="<?=$option?>" <?=$selected?>><?=$name?></option>
							<?php
						}
					?>
					</select>
				</div>
				<div class="col-1-5">
				</div>
			</div>
		</div>
		<div class="search-results">
            <div class="col-10-12 result" id="categoryTitle">
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
            </div>
            <div class="col-2-12" style="text-align:right;"><span class="cog-hover"><i class="fas fa-cog"></i> Advanced</span></div>
        </div>
		<div class="item-container" id="data">
			<!-- Items will be loaded here via AJAX -->
            <div class="has-text-centered" style="padding: 3rem;">
                <span class="icon is-large">
                    <i class="fas fa-spinner fa-pulse fa-3x"></i>
                </span>
                <p class="mt-3">Loading items...</p>
            </div>
		</div>
		<div class="col-1-1" id="pageButtons">
			<button style="float:left;display:inline;width:auto;display:none;" id="previousPage" class="shop-search-button"><i class="fas fa-chevron-left"></i> Previous Page</button>
			<button style="float:right;display:inline;width:auto;display: none;" id="nextPage" class="shop-search-button">Next Page <i class="fas fa-chevron-right"></i></button>
		</div>
	</div>
	<div class="col-1-12">
		<button class="shop-search-button" tooltip="Search" onclick="searchItems()"><i class="fas fa-search"></i></button>
		<div class="small-subtitle">CREATE</div>
		<a href="/shop/create/"><button class="shop-search-button" tooltip="Create"><i class="fas fa-tshirt"></i><i class="fas fa-plus"></i></button></a>
		<div class="small-subtitle">SORT</div>
		<button class="shop-search-button tooltip" data-sort="expensive" title="Most Expensive"><i class="fas fa-dollar-sign"></i><i class="fas fa-level-up-alt"></i></button>
		<button class="shop-search-button tooltip" data-sort="cheap" title="Least Expensive"><i class="fas fa-dollar-sign"></i><i class="fas fa-level-down-alt"></i></button>
		<button class="shop-search-button tooltip" data-sort="most_bought" title="Most Bought"><i class="fas fa-money-bill"></i></button>
		<button class="shop-search-button tooltip" data-sort="most_favorites" title="Most Favorited"><i class="fas fa-heart"></i></button>
		<button class="shop-search-button tooltip" data-sort="most_favorites" title="On Most Wishlists"><i class="fas fa-list"></i></button>
		<button class="shop-search-button tooltip" data-sort="" title="Remove Sort"><i class="fas fa-times"></i></button>
		<div class="small-subtitle">OTHER</div>
		<button class="shop-search-button tooltip" id="help" title="Help"><i class="fas fa-question"></i></button>
		<button class="shop-search-button tooltip" id="copyLink" title="Copy Link"><i class="fas fa-link"></i></button>
	</div>
</div>

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
