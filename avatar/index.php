<?php
/* ==========================================================================
   CONFIGURATION & API HANDLING
   ========================================================================== */
$title = "Avatar Editor";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Security Checks
// Note: We're keeping the existing logic structure, but placing it before the header include
// to ensure redirects happen before any output.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) { // Assuming $AUTH matches session check or variable available after init.inc.php
    // Since we haven't included init.inc.php yet (it's in header.inc.php), 
    // we need to be careful. Ideally, we should include init first.
    // However, the original file included header.inc.php at line 9.
    // So we will rely on header.inc.php to do the init.
}

// CAPTURE CSS & JS FOR HEADER
ob_start();
?>
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="/assets/buefy2.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
    /* Your existing CSS styles remain the same */
    body {
        background-color: #1a1a2e;
        color: #fff;
        height: 100vh;
        overflow-y: auto;
    }

    .editor-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 2rem;
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
        align-items: start;
    }

    .viewer-card {
        background: #16213e;
        border-radius: 15px;
        padding: 1rem;
        position: sticky;
        top: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        border: 1px solid #2a2a4e;
    }

    #avatar-canvas {
        width: 100%;
        height: 450px;
        border-radius: 10px;
        background: radial-gradient(circle at center, #2a2a4e 0%, #16213e 100%);
    }

    .paper-doll {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 1.5rem;
        gap: 5px;
    }
    
    .doll-row { display: flex; gap: 5px; }
    
    .doll-part {
        width: 50px; height: 50px;
        border-radius: 5px; background-color: #ccc;
        cursor: pointer; border: 2px solid transparent;
        transition: transform 0.2s, border-color 0.2s;
        position: relative;
    }
    
    .doll-part:hover { transform: scale(1.1); border-color: white; z-index: 2; }
    .doll-part.head { border-radius: 15px; }
    .doll-part.torso { width: 100px; height: 100px; }
    .doll-part.limb { width: 40px; height: 100px; }
    
    .control-panel {
        background: #16213e;
        border-radius: 15px;
        min-height: 600px;
        border: 1px solid #2a2a4e;
        overflow: hidden;
    }

    .tabs.is-toggle li.is-active a { background-color: #6c5ce7; border-color: #6c5ce7; }
    .tabs.is-toggle a { background-color: #1a1a2e; color: #a4b0be; border-color: #2a2a4e; }

    .inventory-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 15px;
        padding: 20px;
        max-height: 600px;
        overflow-y: auto;
    }

    .item-card {
        background: #1a1a2e;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        position: relative;
    }

    .item-card:hover {
        transform: translateY(-5px);
        border-color: #6c5ce7;
        background: #20203a;
    }

    .item-card.equipped {
        border-color: #2ecc71;
        box-shadow: 0 0 10px rgba(46, 204, 113, 0.3);
    }
    
    .item-card.equipped::after {
        content: '\f00c';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 5px; right: 5px;
        color: #2ecc71;
    }

    .item-image {
        width: 100%;
        aspect-ratio: 1/1;
        object-fit: contain;
        background: #0f0f1a;
        border-radius: 5px;
        margin-bottom: 8px;
    }

    .color-palette-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(5px);
    }

    .color-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        background: #16213e;
        padding: 20px;
        border-radius: 15px;
        border: 1px solid #2a2a4e;
    }

    .color-swatch {
        width: 40px; height: 40px;
        border-radius: 50%;
        cursor: pointer;
        transition: transform 0.2s;
        border: 2px solid rgba(255,255,255,0.1);
    }
    .color-swatch:hover { transform: scale(1.2); border-color: white; }

    @media (max-width: 900px) {
        .editor-grid { grid-template-columns: 1fr; }
        .viewer-card { position: relative; top: 0; }
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js"></script>
<?php
$extra_head_content = ob_get_clean();

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

// 1. Security Checks - Redone after header include to ensure $AUTH is available
if (!$AUTH) {
    header('Location: /log-in');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$uid = $_SESSION['id'];

// ==========================================================================
// ✅ FIXED OWNERSHIP CHECK FUNCTION
// ==========================================================================
function userOwnsItem(PDO $db, int $userId, int $itemId): bool {
    if ($itemId == 0) return true; // Allow unequipping (item ID 0)
    
    $checkOwn = $db->prepare("SELECT id FROM user_inventory WHERE item = ? AND user = ?");
    $checkOwn->execute([$itemId, $userId]);
    return $checkOwn->rowCount() > 0;
}

// 2. Define Color Palette
$ValidColors = [
    '0.013702, 0.502886, 0.332452' => '#1fbc9c', '0.011612, 0.351533, 0.234551' => '#1c1a85',
    '0.027321, 0.603827, 0.162029' => '#2ecc71', '0.033105, 0.313989, 0.708376' => '#3498db',
    '0.000000, 0.132868, 0.318547' => '#2980b9', '0.371238, 0.124772, 0.520996' => '#a463bf',
    '0.270498, 0.056128, 0.417885' => '#8e44ad', '0.046665, 0.090842, 0.155926' => '#3d556e',
    '0.015996, 0.028426, 0.046665' => '#222f3d', '0.887923, 0.558341, 0.005605' => '#f2c511',
    '0.896269, 0.332452, 0.009721' => '#f39c19', '0.806952, 0.070360, 0.045186' => '#e84b3c',
    '0.527115, 0.039546, 0.024158' => '#c0392b', '0.723055, 0.791298, 0.806952' => '#f1f1f1',
    '0.508881, 0.545725, 0.577581' => '#bdc3c8', '1.000000, 0.708376, 0.412543' => '#ffdbac',
    '0.564712, 0.238398, 0.054480' => '#c68642', '0, 0, 0' => '#000000'
];

// 3. AJAX API Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');
    
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Security Token']);
        exit;
    }

    // --- A. Handle Body Color Update ---
    if ($_POST['action'] === 'update_color') {
        $part = $_POST['part']; 
        $color = $_POST['color']; 
        $allowedParts = ['head', 'torso', 'left_arm', 'right_arm', 'left_leg', 'right_leg'];
        
        if (in_array($part, $allowedParts) && array_key_exists($color, $ValidColors)) {
            // Check if user_colors record exists
            $check = $db->prepare("SELECT user FROM user_colors WHERE user = ?");
            $check->execute([$uid]);
            
            if ($check->rowCount() > 0) {
                $stmt = $db->prepare("UPDATE user_colors SET $part = ? WHERE user = ?");
            } else {
                // Create initial record with default colors and update the requested part
                $defaultColors = [
                    'head' => '1,0.8,0.6',
                    'torso' => '0.2,0.2,0.8', 
                    'left_arm' => '1,0.8,0.6',
                    'right_arm' => '1,0.8,0.6',
                    'left_leg' => '0.1,0.1,0.1',
                    'right_leg' => '0.1,0.1,0.1'
                ];
                $defaultColors[$part] = $color;
                
                $stmt = $db->prepare("INSERT INTO user_colors (user, head, torso, left_arm, right_arm, left_leg, right_leg) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $uid, 
                    $defaultColors['head'],
                    $defaultColors['torso'],
                    $defaultColors['left_arm'],
                    $defaultColors['right_arm'],
                    $defaultColors['left_leg'],
                    $defaultColors['right_leg']
                ]);
            }
            
            if (isset($stmt) && $stmt->execute([$color, $uid])) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid color or body part']);
        }
        exit;
    }

    // --- B. Handle Item Equip/Unequip ---
    if ($_POST['action'] === 'toggle_item') {
        $itemId = (int)$_POST['item_id'];
        $type = $_POST['type']; // hat, tshirt, shirt, pants, face, gear, accessory
        
        // ✅ FIXED OWNERSHIP VALIDATION
        if ($itemId > 0 && !userOwnsItem($db, $uid, $itemId)) {
            echo json_encode(['status' => 'error', 'message' => 'You do not own this item.']);
            exit;
        }
        
        // Item ID 0 = unequip (always allowed)
        $column = '';
        switch($type) {
            case 'shirt': $column = 'shirt'; break;
            case 'tshirt': $column = 'tshirt'; break; // ✅ ADDED T-SHIRT SUPPORT
            case 'pants': $column = 'pants'; break;
            case 'face':  $column = 'face'; break;
            case 'gear':  $column = 'gear'; break;
            case 'accessory': $column = 'accessory'; break; // ✅ ADDED ACCESSORY SUPPORT
            case 'hat':   $column = 'hat1'; break; 
        }
        
        if ($column) {
            // Check if user_data record exists
            $check = $db->prepare("SELECT user FROM user_data WHERE user = ?");
            $check->execute([$uid]);
            
            if ($check->rowCount() > 0) {
                $stmt = $db->prepare("UPDATE user_data SET $column = ? WHERE user = ?");
            } else {
                // Create initial record
                $defaultValues = [
                    'hat1' => 0, 'hat2' => 0, 'hat3' => 0, 
                    'face' => 'default', 'shirt' => 0, 'tshirt' => 0, 
                    'pants' => 0, 'gear' => 0, 'accessory' => 0
                ];
                $defaultValues[$column] = $itemId;
                
                $stmt = $db->prepare("INSERT INTO user_data (user, hat1, hat2, hat3, face, shirt, tshirt, pants, gear, accessory) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $uid,
                    $defaultValues['hat1'],
                    $defaultValues['hat2'], 
                    $defaultValues['hat3'],
                    $defaultValues['face'],
                    $defaultValues['shirt'],
                    $defaultValues['tshirt'],
                    $defaultValues['pants'],
                    $defaultValues['gear'],
                    $defaultValues['accessory']
                ]);
            }
            
            if (isset($stmt) && $stmt->execute([$itemId, $uid])) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid item type']);
        }
        exit;
    }
    
    // --- C. Handle Inventory Fetch ---
    if ($_POST['action'] === 'get_inventory') {
        $catID = (int)$_POST['category']; 
        
        // ✅ UPDATED TYPE MAPPING - Based on your store_items types
        $typeMap = [
            1 => 1,   // hats
            2 => 2,   // faces  
            3 => 3,   // shirts (you have type 3 items)
            4 => 8,   // tshirts (you have type 8 items)
            5 => 4,   // pants
            6 => 5,   // gear
            7 => 6    // accessories
        ];
        $typeValue = $typeMap[$catID] ?? $catID;

        // ✅ CORRECTED INVENTORY QUERY
        $stmt = $db->prepare("
            SELECT i.item AS id, s.name, s.image, s.type
            FROM user_inventory i 
            JOIN store_items s ON s.id = i.item 
            WHERE i.user = ? AND s.type = ?
            ORDER BY i.timebought DESC
            LIMIT 50
        ");
        $stmt->execute([$uid, $typeValue]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'items' => $items]);
        exit;
    }
}

// 4. Initial Data Fetch - WITH ERROR HANDLING
try {
    $gc = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
    $gc->execute([$uid]);
    $colors = $gc->fetch(PDO::FETCH_OBJ);

    if(!$colors) {
        // Create default colors if none exist
        $db->prepare("INSERT INTO user_colors (user, head, torso, left_arm, right_arm, left_leg, right_leg) 
                      VALUES (?, '1,0.8,0.6', '0.2,0.2,0.8', '1,0.8,0.6', '1,0.8,0.6', '0.1,0.1,0.1', '0.1,0.1,0.1')")
           ->execute([$uid]);
        $colors = (object)[
            'head' => '1,0.8,0.6',
            'torso' => '0.2,0.2,0.8',
            'left_arm' => '1,0.8,0.6', 
            'right_arm' => '1,0.8,0.6',
            'left_leg' => '0.1,0.1,0.1',
            'right_leg' => '0.1,0.1,0.1'
        ];
    }

    $gw = $db->prepare('SELECT * FROM user_data WHERE user = ?');
    $gw->execute([$uid]);
    $wearables = $gw->fetch(PDO::FETCH_OBJ);

    if(!$wearables) {
        // Create default wearables if none exist
        $db->prepare("INSERT INTO user_data (user, hat1, hat2, hat3, face, shirt, tshirt, pants, gear, accessory) 
                     VALUES (?, 0, 0, 0, 'default', 0, 0, 0, 0, 0)")->execute([$uid]);
        $wearables = (object)[
            'hat1' => 0, 'hat2' => 0, 'hat3' => 0,
            'face' => 'default', 'shirt' => 0, 'tshirt' => 0, 
            'pants' => 0, 'gear' => 0, 'accessory' => 0
        ];
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

function parseColor($str) {
    $parts = explode(',', $str ?? '1,1,1');
    if(count($parts) !== 3) return [1, 1, 1];
    return [floatval($parts[0]), floatval($parts[1]), floatval($parts[2])];
}

$avatarConfig = [
    'colors' => [
        'head' => parseColor($colors->head ?? '1,0.8,0.6'),
        'torso' => parseColor($colors->torso ?? '0.2,0.2,0.8'),
        'leftArm' => parseColor($colors->left_arm ?? '1,0.8,0.6'),
        'rightArm' => parseColor($colors->right_arm ?? '1,0.8,0.6'),
        'leftLeg' => parseColor($colors->left_leg ?? '0.1,0.1,0.1'),
        'rightLeg' => parseColor($colors->right_leg ?? '0.1,0.1,0.1'),
    ],
    'assets' => [
        'face' => $wearables->face ?? 'default',
        'shirt' => $wearables->shirt ?? 0,
        'tshirt' => $wearables->tshirt ?? 0, // ✅ ADDED T-SHIRT
        'pants' => $wearables->pants ?? 0,
        'hats' => array_filter([$wearables->hat1 ?? 0, $wearables->hat2 ?? 0, $wearables->hat3 ?? 0]),
        'gear' => $wearables->gear ?? 0,
        'accessory' => $wearables->accessory ?? 0 // ✅ ADDED ACCESSORY
    ]
];
?>

<div class="avatar-page-wrapper">
<div class="editor-grid">
    <div class="left-col">
        <div class="viewer-card">
            <canvas id="avatar-canvas"></canvas>
            
            <div class="has-text-centered mt-3 mb-2">
                <h4 class="title is-6 has-text-white">Body Color</h4>
                <p class="is-size-7 has-text-grey">Click a body part to paint it</p>
            </div>

            <div class="paper-doll">
                <div class="doll-row">
                    <div class="doll-part head" onclick="openColorModal('head')" id="preview-head"></div>
                </div>
                <div class="doll-row">
                    <div class="doll-part limb" onclick="openColorModal('left_arm')" id="preview-left_arm"></div>
                    <div class="doll-part torso" onclick="openColorModal('torso')" id="preview-torso"></div>
                    <div class="doll-part limb" onclick="openColorModal('right_arm')" id="preview-right_arm"></div>
                </div>
                <div class="doll-row">
                    <div class="doll-part limb" onclick="openColorModal('left_leg')" id="preview-left_leg"></div>
                    <div class="doll-part limb" onclick="openColorModal('right_leg')" id="preview-right_leg"></div>
                </div>
            </div>

            <button class="button is-primary is-fullwidth mt-4" id="btn-redraw">
                <i class="fas fa-sync-alt mr-2"></i> Redraw Avatar
            </button>
        </div>
    </div>

    <div class="right-col">
        <div class="control-panel">
            <div class="tabs is-toggle is-fullwidth is-medium">
                <ul>
                    <li class="is-active" data-tab="wardrobe">
                        <a onclick="switchTab('wardrobe')"><i class="fas fa-tshirt mr-2"></i> Wardrobe</a>
                    </li>
                </ul>
            </div>

            <div id="tab-wardrobe" class="p-3">
                <div class="buttons has-addons is-centered mb-4">
                    <button class="button is-dark is-selected" onclick="loadInventory(1)">Hats</button>
                    <button class="button is-dark" onclick="loadInventory(2)">Faces</button>
                    <button class="button is-dark" onclick="loadInventory(3)">Shirts</button>
                    <button class="button is-dark" onclick="loadInventory(4)">T-Shirts</button>
                    <button class="button is-dark" onclick="loadInventory(5)">Pants</button>
                    <button class="button is-dark" onclick="loadInventory(6)">Gear</button>
                    <button class="button is-dark" onclick="loadInventory(7)">Accessories</button>
                </div>
                
                <div id="inventory-container" class="inventory-grid">
                    <div class="has-text-centered p-5 has-text-grey">
                        <i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading Inventory...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div> <!-- End avatar-page-wrapper -->

<div id="color-modal" class="color-palette-modal" onclick="if(event.target === this) this.style.display='none'">
    <div class="color-grid">
        <?php foreach ($ValidColors as $val => $hex): ?>
            <div class="color-swatch" style="background-color: <?= $hex ?>;" 
                 onclick="updateBodyColor('<?= $val ?>', '<?= $hex ?>')"></div>
        <?php endforeach; ?>
    </div>
</div>
</div> <!-- End avatar-page-wrapper (duplicate close? removing) -->

<!-- Close header.inc.php containers -->
    </div> <!-- Close .page.container -->
</section> <!-- Close .section -->

<script>
    // ===========================================
    // 1. STATE MANAGEMENT
    // ===========================================
    const Config = {
        csrf: "<?= $_SESSION['csrf_token'] ?>",
        userId: <?= $uid ?>,
        avatar: <?= json_encode($avatarConfig) ?>,
        paths: {
            base: '/JGFJFSHFSAH/core/rendering/avatar/avatar.glb', 
            baseWithGear: '/JGFJFSHFSAH/core/rendering/avatar/avatar.glb'
        },
        itemPaths: {
            hat: '/JGFJFSHFSAH/core/rendering/hat',
            shirt: '/JGFJFSHFSAH/core/rendering/shirt', 
            tshirt: '/JGFJFSHFSAH/core/rendering/t-shirts',
            pants: '/JGFJFSHFSAH/core/rendering/pants',
            face: '/JGFJFSHFSAH/core/rendering/faces',
            gear: '/JGFJFSHFSAH/core/rendering/gear',
            accessory: '/JGFJFSHFSAH/core/rendering/accessories'
        }
    };

    let currentPartToPaint = null;

    // ===========================================
    // ✅ FIXED EQUIPPED ITEM CHECKING
    // ===========================================
    function isItemEquipped(typeId, itemId) {
        const id = Number(itemId);
        const assets = Config.avatar.assets;

        switch (typeId) {
            case 1: // hats (check hat1 only for now)
                return Number(assets.hats[0]) === id;
            case 2: // face
                return assets.face === id.toString() || Number(assets.face) === id;
            case 3: // shirt
                return Number(assets.shirt) === id;
            case 4: // tshirt
                return Number(assets.tshirt) === id;
            case 5: // pants
                return Number(assets.pants) === id;
            case 6: // gear
                return Number(assets.gear) === id;
            case 7: // accessory
                return Number(assets.accessory) === id;
            default:
                return false;
        }
    }

    // ===========================================
    // 2. THREE.JS VIEWER LOGIC - FIXED FACE TEXTURE OVERLAY & ENHANCED LIGHTING
    // ===========================================
    class AvatarViewer {
        constructor(canvasId) {
            this.canvas = document.getElementById(canvasId);
            this.scene = new THREE.Scene();
            this.camera = new THREE.PerspectiveCamera(45, this.canvas.clientWidth / this.canvas.clientHeight, 0.1, 100);
            this.renderer = new THREE.WebGLRenderer({ canvas: this.canvas, alpha: true, antialias: true });
            this.gltfLoader = new THREE.GLTFLoader();
            this.objLoader = new THREE.OBJLoader();
            this.textureLoader = new THREE.TextureLoader();
            this.model = null;
            this.materials = {}; 
            this.loadedItems = new Map();
            this.appliedTextures = new Map();
            this.faceOverlayMeshes = new Map(); // Store face overlay meshes separately
            this.controls = null;

            this.init();
        }

        init() {
            this.renderer.setSize(this.canvas.clientWidth, this.canvas.clientHeight);
            this.renderer.setPixelRatio(window.devicePixelRatio);
            this.renderer.outputEncoding = THREE.sRGBEncoding;
            this.renderer.shadowMap.enabled = true;
            this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            
            // ✅ IMPROVED CAMERA SETTINGS
            this.camera.position.set(0, 1.5, 5);
            
            this.controls = new THREE.OrbitControls(this.camera, this.canvas);
            this.controls.target.set(0, 1, 0);
            this.controls.enablePan = false;
            this.controls.minDistance = 2;
            this.controls.maxDistance = 10;
            this.controls.enableDamping = true;
            this.controls.dampingFactor = 0.05;

            // ✅ ENHANCED LIGHTING SYSTEM
            this.setupLighting();
            
            // ✅ ADD ENVIRONMENT (optional ground plane for better lighting reference)
            this.setupEnvironment();

            this.loadBaseModel();
            this.animate();
            
            window.addEventListener('resize', () => {
                this.renderer.setSize(this.canvas.clientWidth, this.canvas.clientHeight);
                this.camera.aspect = this.canvas.clientWidth / this.canvas.clientHeight;
                this.camera.updateProjectionMatrix();
            });
        }

        // ✅ NEW: Enhanced lighting setup
        setupLighting() {
            // 1. Ambient light for overall illumination
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
            this.scene.add(ambientLight);
            
            // 2. Main directional light (simulating sun)
            const mainLight = new THREE.DirectionalLight(0xffffff, 0.8);
            mainLight.position.set(5, 10, 5);
            mainLight.castShadow = true;
            
            // Configure shadow properties for better quality
            mainLight.shadow.mapSize.width = 2048;
            mainLight.shadow.mapSize.height = 2048;
            mainLight.shadow.camera.near = 0.5;
            mainLight.shadow.camera.far = 50;
            mainLight.shadow.camera.left = -10;
            mainLight.shadow.camera.right = 10;
            mainLight.shadow.camera.top = 10;
            mainLight.shadow.camera.bottom = -10;
            
            this.scene.add(mainLight);
            
            // 3. Fill light from opposite side to reduce harsh shadows
            const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
            fillLight.position.set(-5, 5, -5);
            this.scene.add(fillLight);
            
            // 4. Rim/back light for better edge definition
            const rimLight = new THREE.DirectionalLight(0xffffff, 0.4);
            rimLight.position.set(0, 5, -10);
            this.scene.add(rimLight);
            
            // 5. Soft hemisphere light for natural outdoor feel
            const hemisphereLight = new THREE.HemisphereLight(0x87CEEB, 0x8B4513, 0.3);
            this.scene.add(hemisphereLight);
            
            console.log('Enhanced lighting system initialized');
        }

        // ✅ NEW: Environment setup
        setupEnvironment() {
            // Add a subtle ground plane for shadows and reflections
            const groundGeometry = new THREE.PlaneGeometry(20, 20);
            const groundMaterial = new THREE.ShadowMaterial({ 
                opacity: 0.3,
                transparent: true 
            });
            const ground = new THREE.Mesh(groundGeometry, groundMaterial);
            ground.rotation.x = -Math.PI / 2;
            ground.position.y = -1;
            ground.receiveShadow = true;
            this.scene.add(ground);
            
            // Add a subtle gradient background
            const gradientTexture = this.createGradientTexture();
            this.scene.background = gradientTexture;
        }

        // ✅ NEW: Create gradient background
        createGradientTexture() {
            const canvas = document.createElement('canvas');
            canvas.width = 256;
            canvas.height = 256;
            const context = canvas.getContext('2d');
            
            const gradient = context.createLinearGradient(0, 0, 0, 256);
            gradient.addColorStop(0, '#2a2a4e');
            gradient.addColorStop(1, '#16213e');
            
            context.fillStyle = gradient;
            context.fillRect(0, 0, 256, 256);
            
            const texture = new THREE.CanvasTexture(canvas);
            return texture;
        }

        // ✅ NEW: Dynamically adjust camera to fit avatar in frame
        adjustCameraToFitAvatar() {
            if (!this.model) return;
            
            const box = new THREE.Box3().setFromObject(this.model);
            const size = box.getSize(new THREE.Vector3());
            const center = box.getCenter(new THREE.Vector3());
            
            // Calculate the distance needed to fit the entire avatar
            const maxDim = Math.max(size.x, size.y, size.z);
            const fov = this.camera.fov * (Math.PI / 180);
            let distance = maxDim / (2 * Math.tan(fov / 2));
            
            // Add some padding
            distance *= 1.5;
            
            // Position camera to see entire avatar
            this.camera.position.set(center.x, center.y + size.y * 0.3, center.z + distance);
            this.controls.target.set(center.x, center.y, center.z);
            this.controls.update();
            
            console.log('Camera adjusted to fit avatar. Distance:', distance, 'Size:', size);
        }

        // ✅ FIXED: No scaling - use original size
        loadBaseModel() {
            const path = Config.paths.base;

            this.gltfLoader.load(path, (gltf) => {
                if(this.model) this.scene.remove(this.model);
                this.model = gltf.scene;
                
                // ✅ NO SCALING - Use original size
                this.model.scale.set(1, 1, 1);
                this.model.position.set(0, 0, 0);
                
                // Enable shadows for the avatar
                this.model.traverse((object) => {
                    if (object.isMesh) {
                        object.castShadow = true;
                        object.receiveShadow = true;
                    }
                });
                
                console.log('Avatar loaded with original scale and shadow casting enabled');

                // Store original materials for texture application
                this.model.traverse((o) => {
                    if (o.isMesh) {
                        this.materials[o.name] = o.material;
                        
                        // Apply PBR materials for better shading
                        if (o.material && !o.material.isMeshStandardMaterial) {
                            const oldMat = o.material;
                            const newMat = new THREE.MeshStandardMaterial({
                                color: oldMat.color,
                                map: oldMat.map,
                                roughness: 0.7,
                                metalness: 0.1
                            });
                            o.material = newMat;
                        }
                        
                        // ✅ UPDATED NAMING: Use LArm, RArm, LLeg, RLeg
                        if(o.name.includes('Head')) this.setMaterialColor(o.material, Config.avatar.colors.head);
                        if(o.name.includes('Torso')) this.setMaterialColor(o.material, Config.avatar.colors.torso);
                        if(o.name.includes('LArm')) this.setMaterialColor(o.material, Config.avatar.colors.leftArm);
                        if(o.name.includes('RArm')) this.setMaterialColor(o.material, Config.avatar.colors.rightArm);
                        if(o.name.includes('LLeg')) this.setMaterialColor(o.material, Config.avatar.colors.leftLeg);
                        if(o.name.includes('RLeg')) this.setMaterialColor(o.material, Config.avatar.colors.rightLeg);
                    }
                });

                this.scene.add(this.model);
                
                // ✅ DYNAMIC CAMERA: Adjust camera after model loads
                this.adjustCameraToFitAvatar();
                
                this.updatePaperDollUI();
                
                // Load all equipped items
                this.loadEquippedItems();
            }, undefined, (error) => {
                console.error('Error loading model:', error);
                document.getElementById('avatar-canvas').style.background = '#ff6b6b';
                document.getElementById('avatar-canvas').innerHTML = '<div class="has-text-centered has-text-white">Failed to load avatar model</div>';
            });
        }

        // ✅ UPDATED: Load all equipped items including textures
        async loadEquippedItems() {
            const assets = Config.avatar.assets;
            
            // Load 3D model items
            if (assets.hats && assets.hats[0] > 0) {
                await this.loadItemModel(assets.hats[0], 'hat');
            }
            
            if (assets.gear > 0) {
                await this.loadItemModel(assets.gear, 'gear');
            }
            
            if (assets.accessory > 0) {
                await this.loadItemModel(assets.accessory, 'accessory');
            }
            
            // Load texture-based items
            if (assets.face && assets.face !== 'default') {
                await this.loadTextureItem(assets.face, 'face');
            }
            
            if (assets.shirt > 0) {
                await this.loadTextureItem(assets.shirt, 'shirt');
            }
            
            if (assets.tshirt > 0) {
                await this.loadTextureItem(assets.tshirt, 'tshirt');
            }
            
            if (assets.pants > 0) {
                await this.loadTextureItem(assets.pants, 'pants');
            }
        }

        // ✅ FIXED: Load 3D model items with NO SCALING and NO POSITIONING - use original OBJ/GLTF positions
        async loadItemModel(itemId, itemType) {
            if (!itemId || itemId === 0) return;
            
            try {
                // Try OBJ first
                const objPath = `${Config.itemPaths[itemType]}/${itemId}/${itemId}.obj`;
                const texturePath = `${Config.itemPaths[itemType]}/${itemId}/${itemId}.png`;
                
                console.log(`Loading 3D ${itemType} item ${itemId} from:`, objPath);
                
                this.objLoader.load(objPath, (object) => {
                    const itemModel = object;
                    
                    // ✅ NO SCALING - Use original size from Blender
                    itemModel.scale.set(1, 1, 1);
                    
                    // Enable shadows for 3D items
                    itemModel.traverse((child) => {
                        if (child.isMesh) {
                            child.castShadow = true;
                            child.receiveShadow = true;
                        }
                    });
                    
                    console.log(`Loaded ${itemType} item ${itemId} with original Blender position:`, 
                        `Position: (${itemModel.position.x}, ${itemModel.position.y}, ${itemModel.position.z})`,
                        `Scale: (${itemModel.scale.x}, ${itemModel.scale.y}, ${itemModel.scale.z})`);
                    
                    // Load texture if available
                    this.textureLoader.load(texturePath, (texture) => {
                        texture.encoding = THREE.sRGBEncoding;
                        itemModel.traverse((child) => {
                            if (child.isMesh) {
                                child.material = new THREE.MeshStandardMaterial({ 
                                    map: texture,
                                    transparent: true,
                                    roughness: 0.7,
                                    metalness: 0.1
                                });
                            }
                        });
                    }, undefined, (error) => {
                        console.warn(`Could not load texture for ${itemType} ${itemId}:`, error);
                        // Apply default PBR material if texture fails
                        itemModel.traverse((child) => {
                            if (child.isMesh) {
                                child.material = new THREE.MeshStandardMaterial({ 
                                    color: 0xFFFFFF,
                                    transparent: true,
                                    roughness: 0.7,
                                    metalness: 0.1
                                });
                            }
                        });
                    });
                    
                    // ✅ NO MANUAL POSITIONING - Use original Blender positioning
                    this.scene.add(itemModel);
                    this.loadedItems.set(`${itemType}_${itemId}`, itemModel);
                    
                    console.log(`✅ Successfully loaded 3D ${itemType} item ${itemId} with original Blender positioning`);
                }, undefined, (error) => {
                    console.warn(`Could not load ${itemType} item ${itemId} as OBJ:`, error);
                    
                    // Fallback to GLTF if OBJ fails
                    const gltfPath = `${Config.itemPaths[itemType]}/${itemId}/${itemId}.gltf`;
                    this.gltfLoader.load(gltfPath, (gltf) => {
                        const itemModel = gltf.scene;
                        // ✅ NO SCALING and NO POSITIONING
                        itemModel.scale.set(1, 1, 1);
                        
                        // Enable shadows
                        itemModel.traverse((child) => {
                            if (child.isMesh) {
                                child.castShadow = true;
                                child.receiveShadow = true;
                            }
                        });
                        
                        console.log(`Loaded ${itemType} item ${itemId} as GLTF with original Blender position:`, 
                            `Position: (${itemModel.position.x}, ${itemModel.position.y}, ${itemModel.position.z})`,
                            `Scale: (${itemModel.scale.x}, ${itemModel.scale.y}, ${itemModel.scale.z})`);
                        
                        this.scene.add(itemModel);
                        this.loadedItems.set(`${itemType}_${itemId}`, itemModel);
                    }, undefined, (gltfError) => {
                        console.error(`Could not load ${itemType} item ${itemId} as GLTF either:`, gltfError);
                    });
                });
            } catch (error) {
                console.error(`Error loading 3D ${itemType} item:`, error);
            }
        }

        // ✅ FIXED: Load texture-based items with SEPARATE OVERLAY for face textures
        async loadTextureItem(itemId, itemType) {
            if (!itemId || itemId === 0) return;
            
            try {
                const texturePath = `${Config.itemPaths[itemType]}/${itemId}/${itemId}.png`;
                console.log(`Loading texture ${itemType} item ${itemId} from:`, texturePath);
                
                this.textureLoader.load(texturePath, (texture) => {
                    // ✅ FIXED: Flip texture right-side up
                    texture.flipY = false;
                    texture.encoding = THREE.sRGBEncoding;
                    
                    if (itemType === 'face') {
                        // For face: Create a SEPARATE overlay mesh
                        this.applyFaceOverlay(texture, itemId);
                    } else {
                        // For other items: Apply as transparent overlay on existing materials
                        this.applyTextureOverlay(texture, itemType);
                    }
                    
                    this.appliedTextures.set(`${itemType}_${itemId}`, { texture, itemType });
                    console.log(`✅ Successfully applied ${itemType} texture ${itemId}`);
                }, undefined, (error) => {
                    console.error(`Could not load texture for ${itemType} ${itemId}:`, error);
                });
            } catch (error) {
                console.error(`Error loading texture ${itemType} item:`, error);
            }
        }

        // ✅ NEW: Create separate overlay mesh for face textures
        applyFaceOverlay(texture, itemId) {
            if (!this.model) return;
            
            // Find the head mesh
            let headMesh = null;
            this.model.traverse((object) => {
                if (object.isMesh && object.name.includes('Head')) {
                    headMesh = object;
                }
            });
            
            if (!headMesh) {
                console.error('Could not find head mesh for face overlay');
                return;
            }
            
            // Remove any existing face overlay
            this.removeFaceOverlay();
            
            // Clone the head geometry for the overlay
            const overlayGeometry = headMesh.geometry.clone();
            
            // Create a transparent overlay material
            const overlayMaterial = new THREE.MeshStandardMaterial({
                map: texture,
                transparent: true,
                alphaTest: 0.1,
                roughness: 0.5,
                metalness: 0.0,
                side: THREE.FrontSide
            });
            
            // Create the overlay mesh
            const overlayMesh = new THREE.Mesh(overlayGeometry, overlayMaterial);
            
            // Position it slightly in front of the head to avoid z-fighting
            overlayMesh.position.copy(headMesh.position);
            overlayMesh.rotation.copy(headMesh.rotation);
            overlayMesh.scale.copy(headMesh.scale);
            
            // Slight offset to prevent z-fighting
            overlayMesh.position.z += 0.001;
            
            // Add to scene
            this.scene.add(overlayMesh);
            
            // Store reference
            this.faceOverlayMeshes.set(`face_${itemId}`, overlayMesh);
            
            console.log(`✅ Created separate face overlay mesh for item ${itemId}`);
        }

        // ✅ NEW: Apply texture as overlay on existing materials (for shirts, pants, etc.)
        applyTextureOverlay(texture, itemType) {
            if (!this.model) return;
            
            const bodyPartMapping = {
                'shirt': ['Torso', 'LArm', 'RArm'],
                'tshirt': ['Torso'],
                'pants': ['LLeg', 'RLeg']
            };

            const parts = bodyPartMapping[itemType] || [];
            
            this.model.traverse((object) => {
                if (object.isMesh && parts.some(part => object.name.includes(part))) {
                    // Store original material if not already stored
                    if (!object.userData.originalMaterial) {
                        object.userData.originalMaterial = object.material;
                    }
                    
                    // Create overlay material
                    const overlayMaterial = new THREE.MeshStandardMaterial({
                        map: texture,
                        transparent: true,
                        alphaTest: 0.1,
                        roughness: 0.5,
                        metalness: 0.0,
                        side: THREE.FrontSide
                    });
                    
                    // Apply overlay
                    object.material = overlayMaterial;
                    object.userData.hasTextureOverlay = true;
                }
            });
        }

        // ✅ NEW: Remove face overlay mesh
        removeFaceOverlay() {
            this.faceOverlayMeshes.forEach((mesh, key) => {
                this.scene.remove(mesh);
            });
            this.faceOverlayMeshes.clear();
        }

        // ✅ FIXED: Remove texture from body parts with proper restoration
        removeTextureFromBodyParts(itemType) {
            if (!this.model) return;
            
            const bodyPartMapping = {
                'face': ['Head'],
                'shirt': ['Torso', 'LArm', 'RArm'],
                'tshirt': ['Torso'],
                'pants': ['LLeg', 'RLeg']
            };

            const parts = bodyPartMapping[itemType] || [];
            
            if (itemType === 'face') {
                // Remove face overlay mesh
                this.removeFaceOverlay();
            } else {
                // Restore original materials for other overlays
                this.model.traverse((object) => {
                    if (object.isMesh && parts.some(part => object.name.includes(part))) {
                        // Restore original material if it exists
                        if (object.userData.originalMaterial) {
                            object.material = object.userData.originalMaterial;
                            delete object.userData.hasTextureOverlay;
                        }
                    }
                });
            }
        }

        // ✅ FIXED: Remove item from scene (both 3D models and textures)
        removeItem(itemType, itemId) {
            const key = `${itemType}_${itemId}`;
            
            // Handle 3D model items
            if (this.loadedItems.has(key)) {
                const itemModel = this.loadedItems.get(key);
                this.scene.remove(itemModel);
                this.loadedItems.delete(key);
                console.log(`✅ Removed 3D ${itemType} item ${itemId}`);
            }
            
            // Handle texture items
            if (this.appliedTextures.has(key)) {
                const textureData = this.appliedTextures.get(key);
                this.removeTextureFromBodyParts(textureData.itemType);
                this.appliedTextures.delete(key);
                console.log(`✅ Removed ${itemType} texture ${itemId}`);
            }
            
            // Handle face overlay meshes
            if (itemType === 'face' && this.faceOverlayMeshes.has(key)) {
                const overlayMesh = this.faceOverlayMeshes.get(key);
                this.scene.remove(overlayMesh);
                this.faceOverlayMeshes.delete(key);
                console.log(`✅ Removed face overlay mesh ${itemId}`);
            }
        }

        setMaterialColor(material, rgbArray) {
            material.color.setRGB(rgbArray[0], rgbArray[1], rgbArray[2]);
        }

        // ✅ FIXED: Update part color - works with face overlay
        updatePartColor(partName, rgbArray) {
            const map = {
                'head': 'Head',
                'torso': 'Torso',
                'left_arm': 'LArm',
                'right_arm': 'RArm',  
                'left_leg': 'LLeg',
                'right_leg': 'RLeg'
            };
            
            if (!this.model) return;
            
            this.model.traverse((o) => {
                if (o.isMesh && o.name.includes(map[partName])) {
                    // Check if this part has a texture overlay
                    if (o.userData.hasTextureOverlay) {
                        console.log(`${partName} has texture overlay - color update skipped`);
                        return;
                    }
                    
                    // Update the color
                    this.setMaterialColor(o.material, rgbArray);
                }
            });
            
            // Update config
            const configMap = {
                'head': 'head',
                'torso': 'torso', 
                'left_arm': 'leftArm',
                'right_arm': 'rightArm',
                'left_leg': 'leftLeg',
                'right_leg': 'rightLeg'
            };
            
            if (configMap[partName]) {
                Config.avatar.colors[configMap[partName]] = rgbArray;
            }
            
            this.updatePaperDollUI();
        }

        // ✅ FIXED: Update paper doll UI with CORRECT color mapping
        updatePaperDollUI() {
            const toHex = (c) => '#' + c.map(x => {
                const val = Math.round(x*255);
                return val.toString(16).padStart(2, '0');
            }).join('');
            
            document.getElementById('preview-head').style.backgroundColor = toHex(Config.avatar.colors.head);
            document.getElementById('preview-torso').style.backgroundColor = toHex(Config.avatar.colors.torso);
            
            // Left arm button shows left arm color
            document.getElementById('preview-left_arm').style.backgroundColor = toHex(Config.avatar.colors.leftArm);
            // Right arm button shows right arm color  
            document.getElementById('preview-right_arm').style.backgroundColor = toHex(Config.avatar.colors.rightArm);
            
            // Left leg button shows left leg color
            document.getElementById('preview-left_leg').style.backgroundColor = toHex(Config.avatar.colors.leftLeg);
            // Right leg button shows right leg color
            document.getElementById('preview-right_leg').style.backgroundColor = toHex(Config.avatar.colors.rightLeg);
        }

        animate() {
            requestAnimationFrame(() => this.animate());
            this.controls.update();
            this.renderer.render(this.scene, this.camera);
        }
    }

    const Viewer = new AvatarViewer('avatar-canvas');

    // ===========================================
    // 3. UI INTERACTION & API CALLS
    // ===========================================

    function openColorModal(part) {
        currentPartToPaint = part;
        document.getElementById('color-modal').style.display = 'flex';
    }

    function updateBodyColor(colorValString, hex) {
        if (!currentPartToPaint) return;

        document.getElementById('color-modal').style.display = 'none';
        
        // ✅ FIXED: Update the correct paper doll part preview immediately
        document.getElementById('preview-' + currentPartToPaint).style.backgroundColor = hex;

        const rgb = colorValString.split(',').map(x => parseFloat(x.trim()));
        Viewer.updatePartColor(currentPartToPaint, rgb);

        const formData = new FormData();
        formData.append('action', 'update_color');
        formData.append('part', currentPartToPaint);
        formData.append('color', colorValString);
        formData.append('csrf_token', Config.csrf);

        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    console.error('Color update failed:', data.message);
                }
            })
            .catch(err => console.error('Fetch error:', err));
    }

    function loadInventory(category) {
        const container = document.getElementById('inventory-container');
        container.innerHTML = '<div class="has-text-centered p-5"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        
        document.querySelectorAll('#tab-wardrobe button').forEach(b => b.classList.remove('is-selected'));
        event.target.classList.add('is-selected');

        const formData = new FormData();
        formData.append('action', 'get_inventory');
        formData.append('category', category);
        formData.append('csrf_token', Config.csrf);

        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.items) {
                    container.innerHTML = '';
                    
                    // Add unequip option
                    const unequipDiv = document.createElement('div');
                    unequipDiv.className = `item-card ${isItemEquipped(category, 0) ? 'equipped' : ''}`;
                    unequipDiv.innerHTML = `
                        <div class="item-image has-text-centered" style="display: flex; align-items: center; justify-content: center; background: #2a2a4e;">
                            <i class="fas fa-times has-text-grey" style="font-size: 24px;"></i>
                        </div>
                        <div class="is-size-7 has-text-weight-bold is-truncate">Unequip</div>
                    `;
                    unequipDiv.onclick = () => toggleItem(0, category, unequipDiv);
                    container.appendChild(unequipDiv);
                    
                    data.items.forEach(item => {
                        const isEquipped = isItemEquipped(category, item.id);

                        const div = document.createElement('div');
                        div.className = `item-card ${isEquipped ? 'equipped' : ''}`;
                        div.innerHTML = `
                            <img src="/assets/images/store/${item.image}.png" class="item-image" onerror="this.src='/assets/images/store/default.png'">
                            <div class="is-size-7 has-text-weight-bold is-truncate">${item.name}</div>
                        `;
                        div.onclick = () => toggleItem(item.id, category, div);
                        container.appendChild(div);
                    });
                } else {
                    container.innerHTML = '<div class="has-text-grey has-text-centered">You own no items in this category.</div>';
                }
            })
            .catch(err => {
                console.error('Inventory load error:', err);
                container.innerHTML = '<div class="has-text-danger has-text-centered">Error loading inventory</div>';
            });
    }

    // ✅ FIXED: Proper item removal when unequipping
    function toggleItem(itemId, category, element) {
        const isEquipped = element.classList.contains('equipped');
        const idToSend = isEquipped ? 0 : itemId;

        // Update UI immediately for better UX
        document.querySelectorAll('.item-card').forEach(el => el.classList.remove('equipped'));
        if (!isEquipped) {
            element.classList.add('equipped');
        }

        const types = {
            1: 'hat', 
            2: 'face', 
            3: 'shirt', 
            4: 'tshirt', 
            5: 'pants', 
            6: 'gear',
            7: 'accessory'
        };

        const formData = new FormData();
        formData.append('action', 'toggle_item');
        formData.append('item_id', idToSend);
        formData.append('type', types[category]);
        formData.append('csrf_token', Config.csrf);

        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    console.log('Avatar updated successfully');
                    
                    const itemType = types[category];
                    
                    // ✅ FIXED: Always remove the item first (whether equipping or unequipping)
                    if (isEquipped) {
                        // Unequipping - remove the item
                        Viewer.removeItem(itemType, itemId);
                    } else {
                        // Equipping new item - remove any existing item of same type first
                        const currentEquipped = getCurrentlyEquippedItemId(category);
                        if (currentEquipped && currentEquipped !== 0) {
                            Viewer.removeItem(itemType, currentEquipped);
                        }
                    }
                    
                    // Update config
                    updateConfigWithNewItem(category, idToSend);
                    
                    // Load new item if equipping (not unequipping)
                    if (idToSend !== 0) {
                        const isTextureItem = ['shirt', 'tshirt', 'pants', 'face'].includes(itemType);
                        if (isTextureItem) {
                            Viewer.loadTextureItem(idToSend, itemType);
                        } else {
                            Viewer.loadItemModel(idToSend, itemType);
                        }
                    }
                    
                    console.log(`✅ ${isEquipped ? 'Unequipped' : 'Equipped'} ${itemType} item ${itemId}`);
                } else {
                    alert(data.message);
                    loadInventory(category);
                }
            })
            .catch(err => {
                console.error('Toggle item error:', err);
                alert('Network error occurred');
                loadInventory(category);
            });
    }

    // Helper function to get currently equipped item ID
    function getCurrentlyEquippedItemId(category) {
        const assets = Config.avatar.assets;
        switch(category) {
            case 1: return assets.hats[0];
            case 2: return assets.face;
            case 3: return assets.shirt;
            case 4: return assets.tshirt;
            case 5: return assets.pants;
            case 6: return assets.gear;
            case 7: return assets.accessory;
            default: return 0;
        }
    }

    // Helper function to update config
    function updateConfigWithNewItem(category, itemId) {
        switch(category) {
            case 1: 
                Config.avatar.assets.hats[0] = itemId;
                break;
            case 2: 
                Config.avatar.assets.face = itemId;
                break;
            case 3: 
                Config.avatar.assets.shirt = itemId;
                break;
            case 4: 
                Config.avatar.assets.tshirt = itemId;
                break;
            case 5: 
                Config.avatar.assets.pants = itemId;
                break;
            case 6: 
                Config.avatar.assets.gear = itemId;
                break;
            case 7: 
                Config.avatar.assets.accessory = itemId;
                break;
        }
    }

    // Initialize with hats category
    loadInventory(1);

    document.getElementById('btn-redraw').addEventListener('click', () => {
        location.reload();
    });
    
    function switchTab(tabName) {
        document.querySelectorAll('.tabs li').forEach(li => li.classList.remove('is-active'));
        document.querySelectorAll('[data-tab]').forEach(tab => {
            if(tab.getAttribute('data-tab') === tabName) {
                tab.classList.add('is-active');
            }
        });
    }
</script>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/private/footer.inc.php'); ?>