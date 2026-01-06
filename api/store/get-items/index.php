<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

// Get parameters
$category = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
$query = isset($_POST['query']) ? trim($_POST['query']) : null;
$showAll = isset($_POST['showAll']) && $_POST['showAll'] == 'true';

$limit = 12;
$page = isset($_POST['page']) && is_numeric($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($page - 1) * $limit;
if ($offset < 0) { $offset = 0; }

// Check if user is admin
$isAdmin = $app->auth() && $my && isset($my->admin) && $my->admin == 1;

// Validate category
if ($category && $category > 8) {
    die(json_encode(['error' => 'Invalid category']));
}

// Build query
$sql = "SELECT * FROM store_items WHERE 1=1";
$params = [];

if ($query) {
    $sql .= " AND name LIKE ?";
    $params[] = "%" . $query . "%";
}

if ($category) {
    $sql .= " AND type = ?"; 
    $params[] = $category;
}

// If user is not admin, only show moderated items
if (!$isAdmin && !$showAll) {
    $sql .= " AND moderation = 1";
}

// Count total for pagination
$countSql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalResult = $countStmt->fetch(PDO::FETCH_OBJ);
$totalItems = $totalResult ? $totalResult->total : 0;
$totalPages = ceil($totalItems / $limit);

// Adjust page if out of bounds
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$sql .= " ORDER BY 
    CASE WHEN moderation = 1 THEN 0 ELSE 1 END,
    id DESC 
    LIMIT $limit OFFSET $offset";

$selectItems = $db->prepare($sql);
$selectItems->execute($params);
$items = $selectItems->fetchAll(PDO::FETCH_OBJ);

// Helper functions
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

function getItemRenderData($item) {
    $typeMap = [
        1 => 'hat',
        7 => 'head',
        6 => 'accessory',
        2 => 'face',
        5 => 'gear',
        3 => 'shirt',
        4 => 'pants',
        8 => 'tshirt'
    ];
    
    $renderType = $typeMap[$item->type] ?? 'accessory';
    $itemsData = [
        'hats' => [],
        'face' => 'default',
        'accessory' => 0,
        'shirt' => 0,
        'pants' => 0,
        'tshirt' => 0,
        'gear' => 0
    ];
    
    if ($renderType === 'hat') {
        $itemsData['hats'] = [(int)$item->id];
    } elseif ($renderType === 'face') {
        $itemsData['face'] = (string)$item->id;
    } else {
        $itemsData[$renderType] = (int)$item->id;
    }
    
    return [
        'type' => $renderType,
        'items' => $itemsData,
        'colors' => [
            'head' => [1, 0.8, 0.6],
            'torso' => [0.2, 0.2, 0.8],
            'leftArm' => [1, 0.8, 0.6],
            'rightArm' => [1, 0.8, 0.6],
            'leftLeg' => [0.1, 0.1, 0.1],
            'rightLeg' => [0.1, 0.1, 0.1]
        ]
    ];
}
?>

<div class="columns is-multiline">
    <?php if(count($items) > 0): ?>
        <?php foreach($items as $item): ?>
            <div class="column is-4-tablet is-3-desktop">
                <div class="card item-card">
                    <?php if($item->moderation == 0): ?>
                        <div class="unmoderated-badge" title="This item is awaiting moderation">
                            ⚠️ Pending Review
                        </div>
                    <?php elseif($item->moderation == 2): ?>
                        <div class="unmoderated-badge" style="background-color: #f14668;" title="This item was rejected">
                            ❌ Rejected
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-image canvas-container">
                        <figure class="image is-1by1">
                            <?php $renderData = getItemRenderData($item); ?>
                            <canvas id="item-render-<?php echo $item->id; ?>" 
                                    width="200" height="200"
                                    style="width: 100%; height: 100%; display: block;"
                                    data-user-id="0"
                                    data-render-type="item"
                                    data-item-type-focus="<?php echo $renderData['type']; ?>"
                                    data-colors='<?php echo json_encode($renderData['colors']); ?>'
                                    data-items='<?php echo json_encode($renderData['items']); ?>'>
                            </canvas>
                        </figure>
                    </div>
                    <div class="card-content p-4">
                        <div class="content">
                            <p class="title is-6 mb-1 text-truncate" title="<?php echo htmlspecialchars($item->name); ?>">
                                <?php echo htmlspecialchars($item->name); ?>
                            </p>
                            <p class="is-size-7 has-text-grey mb-2">
                                <?php 
                                $catInfo = getCategoryName($item->type);
                                echo '<span class="category-icon">' . $catInfo['icon'] . '</span>' . $catInfo['name'];
                                ?>
                                <?php if($item->creator_id): ?>
                                    <br><small>By: User #<?php echo $item->creator_id; ?></small>
                                <?php endif; ?>
                            </p>
                            <div class="level is-mobile mt-3">
                                <div class="level-left">
                                    <span class="price-tag">$<?php echo number_format($item->price ?? 0); ?></span>
                                </div>
                                <div class="level-right">
                                    <?php if($item->moderation == 1 || $isAdmin): ?>
                                        <a href="/store/view/<?php echo $item->id; ?>" class="button is-small is-primary is-outlined">View</a>
                                    <?php else: ?>
                                        <span class="button is-small is-static" title="This item is not available for viewing yet">View</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if($isAdmin): ?>
                            <div class="admin-controls">
                                <div class="tags">
                                    <span class="tag <?php echo $item->moderation == 1 ? 'is-success' : ($item->moderation == 2 ? 'is-danger' : 'is-warning'); ?> is-light">
                                        <?php 
                                        switch($item->moderation) {
                                            case 0: echo 'Pending'; break;
                                            case 1: echo 'Approved'; break;
                                            case 2: echo 'Rejected'; break;
                                            default: echo 'Unknown';
                                        }
                                        ?>
                                    </span>
                                    <a href="/admin/review-item.php?id=<?php echo $item->id; ?>" class="tag is-info is-light">Review</a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="column is-12">
            <div class="notification is-warning is-light has-text-centered">
                <h4 class="title is-5">No items found!</h4>
                <p>Try adjusting your search or category filter.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if($totalPages > 1): ?>
<nav class="pagination is-centered mt-5" role="navigation" aria-label="pagination">
    <a class="pagination-previous" 
       onclick="getItems(<?php echo $category ?? 'null'; ?>, <?php echo max(1, $page-1); ?>, '<?php echo addslashes($query ?? ''); ?>', <?php echo $showAll ? 'true' : 'false'; ?>)"
       <?php if($page <= 1): ?>disabled<?php endif; ?>>
        Previous
    </a>
    <a class="pagination-next" 
       onclick="getItems(<?php echo $category ?? 'null'; ?>, <?php echo min($totalPages, $page+1); ?>, '<?php echo addslashes($query ?? ''); ?>', <?php echo $showAll ? 'true' : 'false'; ?>)"
       <?php if($page >= $totalPages): ?>disabled<?php endif; ?>>
        Next page
    </a>
    
    <ul class="pagination-list">
        <?php if($page > 2): ?>
            <li><a class="pagination-link" onclick="getItems(<?php echo $category ?? 'null'; ?>, 1, '<?php echo addslashes($query ?? ''); ?>', <?php echo $showAll ? 'true' : 'false'; ?>)">1</a></li>
            <?php if($page > 3): ?><li><span class="pagination-ellipsis">&hellip;</span></li><?php endif; ?>
        <?php endif; ?>
        
        <?php for($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++): ?>
            <li>
                <a class="pagination-link <?php echo $i == $page ? 'is-current' : ''; ?>" 
                   onclick="getItems(<?php echo $category ?? 'null'; ?>, <?php echo $i; ?>, '<?php echo addslashes($query ?? ''); ?>', <?php echo $showAll ? 'true' : 'false'; ?>)">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <?php if($page < $totalPages - 1): ?>
            <?php if($page < $totalPages - 2): ?><li><span class="pagination-ellipsis">&hellip;</span></li><?php endif; ?>
            <li><a class="pagination-link" onclick="getItems(<?php echo $category ?? 'null'; ?>, <?php echo $totalPages; ?>, '<?php echo addslashes($query ?? ''); ?>', <?php echo $showAll ? 'true' : 'false'; ?>)"><?php echo $totalPages; ?></a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<script>
// Re-initialize avatars after AJAX load
if (typeof initializeDashboardAvatars3D === 'function') {
    setTimeout(initializeDashboardAvatars3D, 100);
}
</script>