<?php
$title = "Avatar Editor";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

if (!$AUTH) {
    header('Location: /log-in');
    exit;
}

// Flood control
if ($my->flood >= time()) {
    die('Please wait another ' . ($my->flood - time()) . ' seconds before changing your avatar.');
}

$uid = $_SESSION['id'];
if (isset($_GET['ldr'])) {
    $uid = intval($_GET['ldr']);
}

// Get user colors
$getColors = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
$getColors->bindValue(1, $uid, PDO::PARAM_INT);
$getColors->execute();
$gc = $getColors->fetch(PDO::FETCH_OBJ);

if (!$gc) {
    die("No avatar color data found");
}

// Get user wearables
$getWear = $db->prepare('SELECT * FROM user_data WHERE user = ?');
$getWear->bindValue(1, $uid, PDO::PARAM_INT);
$getWear->execute();
$gw = $getWear->fetch(PDO::FETCH_OBJ);

// Color mapping
$Colors = [
    '0.013702, 0.502886, 0.332452' => '31, 188, 156',
    '0.011612, 0.351533, 0.234551' => '28, 160, 133',
    '0.027321, 0.603827, 0.162029' => '46, 204, 112',
    '0.033105, 0.313989, 0.708376' => '51, 152, 219',
    '0.000000, 0.132868, 0.318547' => '41, 128, 185',
    '0.371238, 0.124772, 0.520996' => '164, 99, 191',
    '0.270498, 0.056128, 0.417885' => '142, 67, 173',
    '0.046665, 0.090842, 0.155926' => '61, 85, 110',
    '0.015996, 0.028426, 0.046665' => '34, 47, 61',
    '0.887923, 0.558341, 0.005605' => '242, 197, 17',
    '0.896269, 0.332452, 0.009721' => '243, 156, 25',
    '0.806952, 0.070360, 0.045186' => '232, 75, 60',
    '0.527115, 0.039546, 0.024158' => '192, 56, 43',
    '0.723055, 0.791298, 0.806952' => '241, 241, 241',
    '0.508881, 0.545725, 0.577581' => '189, 195, 200',
    '1.000000, 0.708376, 0.412543' => '255, 219, 172',
    '0.564712, 0.238398, 0.054480' => '198, 134, 66',
    '0, 0, 0' => '0, 0, 0'
];

// Prepare avatar data for JavaScript
$avatarData = [
    'headColor' => $gc->head == 'FFFFFF' ? [1,1,1] : hex2rgb($gc->head),
    'torsoColor' => $gc->torso == '3398db' ? [0.2,0.6,0.86] : hex2rgb($gc->torso),
    'leftArmColor' => $gc->left_arm == 'FFFFFF' ? [1,1,1] : hex2rgb($gc->left_arm),
    'rightArmColor' => $gc->right_arm == 'FFFFFF' ? [1,1,1] : hex2rgb($gc->right_arm),
    'leftLegColor' => $gc->left_leg == 'FFFFFF' ? [1,1,1] : hex2rgb($gc->left_leg),
    'rightLegColor' => $gc->right_leg == 'FFFFFF' ? [1,1,1] : hex2rgb($gc->right_leg),
    'faceTexture' => $gw->face ? '/JGFJFSHFSAH/core/rendering/faces/'.$gw->face.'.png' : '',
    'shirtTexture' => $gw->shirt ? '/admin/images/store/'.$gw->shirt.'.png' : '',
    'tshirtTexture' => $gw->tshirt ? '/admin/images/store/'.$gw->tshirt.'.png' : '',
    'pantsTexture' => $gw->pants ? '/admin/images/store/'.$gw->pants.'.png' : '',
    'hats' => array_filter([$gw->hat1, $gw->hat2, $gw->hat3]),
    'gear' => $gw->gear ?: '',
    'accessory' => $gw->accessory ?: '',
    'headModel' => $gw->head ?: '',
    'baseModel' => !empty($gw->gear) ? 
        '/JGFJFSHFSAH/core/rendering/avatar/avatar10.gltf' : 
        '/JGFJFSHFSAH/core/rendering/avatar/avatar.gltf',
    'userId' => $uid
];

// Update flood control
$flood = $db->prepare('UPDATE users SET flood = ? WHERE id = ?');
$flood->bindValue(1, time() + 5, PDO::PARAM_STR);
$flood->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
$flood->execute();

// Helper function to convert hex to RGB with null safety
function hex2rgb($hex) {
    if (empty($hex)) return [1, 1, 1];
    
    $hex = str_replace('#', '', $hex);
    
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    
    if (!preg_match('/^[a-f0-9]{6}$/i', $hex)) {
        return [1, 1, 1];
    }
    
    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;
    
    return [$r, $g, $b];
}
?>

<!-- Add CSS for color modals -->
<style>
    .color-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-background {
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        position: relative;
        z-index: 1001;
        max-width: 500px;
        width: 90%;
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 20px rgba(0,0,0,0.2);
    }
    
    .color-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        margin: 20px 0;
    }
    
    .color-swatch {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .color-swatch:hover {
        transform: scale(1.1);
        box-shadow: 0 0 0 2px white;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
    }
</style>

<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-3">
                <div class="columns is-mobile is-marginless-bottom is-paddingless-bottom">
                    <div class="column is-6 is-paddingless-bottom">
                        <div class="novello-title is-size-5">Character</div>
                    </div>
                    <div class="column is-6 is-paddingless-bottom">
                        <button id="btnRefresh" class="button is-small is-info is-pulled-right"><b>Refresh</b></button>
                    </div>
                </div>
                <div class="box has-text-centered" id="characterBox" style="height: 300px; position: relative;">
                    <div id="loading" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);">
                        Loading Avatar...
                    </div>
                    <canvas id="avatarCanvas" style="width: 100%; height: 100%; display: none;"></canvas>
                </div>
                
                <div class="novello-title is-size-5">Colors</div>
                <div class="box has-text-centered">
                    <div onclick="showColorModal('head')" id="head" class="color_head character_color"
                         style="background-color: rgb(<?= $Colors[$gc->head] ?? '255,255,255' ?>);"></div>
                    <div class="push-5"></div>
                    <div onclick="showColorModal('right_arm')" id="right_arm" class="color_limb character_color"
                         style="background-color: rgb(<?= $Colors[$gc->right_arm] ?? '255,255,255' ?>);"></div>
                    <div onclick="showColorModal('torso')" id="torso" class="color_body character_color"
                         style="background-color: rgb(<?= $Colors[$gc->torso] ?? '255,255,255' ?>);"></div>
                    <div onclick="showColorModal('left_arm')" id="left_arm" class="color_limb character_color"
                         style="background-color: rgb(<?= $Colors[$gc->left_arm] ?? '255,255,255' ?>);"></div>
                    <div class="push-character-legs"></div>
                    <div onclick="showColorModal('right_leg')" id="right_leg" class="color_limb character_color"
                         style="background-color: rgb(<?= $Colors[$gc->right_leg] ?? '255,255,255' ?>);"></div>
                    <div onclick="showColorModal('left_leg')" id="left_leg" class="color_limb character_color"
                         style="background-color: rgb(<?= $Colors[$gc->left_leg] ?? '255,255,255' ?>);"></div>
                </div>
            </div>
            
            <div class="column is-9">
                <div class="novello-title is-size-5">Inventory</div>
                <div class="box">
                    <div class="tabs is-marginless is-centered" id="inventory_tabs">
                        <ul>
                            <li id="clothing_hover_div" onmouseover="showInventoryTab('clothing')" class="is-active"><a>Clothing</a></li>
                            <li id="accessories_hover_div" onmouseover="showInventoryTab('accessories')"><a>Accessories</a></li>
                            <li id="body_hover_div" onmouseover="showInventoryTab('body')"><a>Body</a></li>
                        </ul>
                    </div>
                    
                    <div id="clothing_choose_div" class="tabs is-marginless is-centered" style="display: none;">
                        <ul>
                            <li onclick="get_inventory(2, 1);setActiveTab(this, 'clothing_hover_div', 'cat2')" id="cat2"><a>Faces</a></li>
                            <li onclick="get_inventory(3, 1);setActiveTab(this, 'clothing_hover_div', 'cat3')" id="cat3" class="is-active"><a>Shirts</a></li>
                            <li onclick="get_inventory(4, 1);setActiveTab(this, 'clothing_hover_div', 'cat4')" id="cat4"><a>Pants</a></li>
                            <li onclick="get_inventory(8, 1);setActiveTab(this, 'clothing_hover_div', 'cat8')" id="cat8"><a>Tshirts</a></li>
                        </ul>
                    </div>
                    
                    <div id="accessories_choose_div" class="tabs is-marginless is-centered" style="display: none;">
                        <ul>
                            <li onclick="get_inventory(1, 1);setActiveTab(this, 'accessories_hover_div', 'cat1')" id="cat1"><a>Hats</a></li>
                            <li onclick="get_inventory(5, 1);setActiveTab(this, 'accessories_hover_div', 'cat5')" id="cat5"><a>Gears</a></li>
                            <li onclick="get_inventory(6, 1);setActiveTab(this, 'accessories_hover_div', 'cat6')" id="cat6"><a>Accessories</a></li>
                            <li onclick="get_inventory(7, 1);setActiveTab(this, 'accessories_hover_div', 'cat7')" id="cat7"><a>Heads</a></li>
                        </ul>
                    </div>
                    
                    <div id="body_choose_div" class="tabs is-marginless is-centered" style="display: none;">
                        <ul>
                            <li onclick="showColorModal('head');setActiveTab(this, 'body_hover_div', 'color_head_choose')" id="color_head_choose"><a>Head</a></li>
                            <li onclick="showColorModal('torso');setActiveTab(this, 'body_hover_div', 'color_torso_choose')" id="color_torso_choose"><a>Torso</a></li>
                            <li onclick="showColorModal('left_arm');setActiveTab(this, 'body_hover_div', 'color_leftarm_choose')" id="color_leftarm_choose"><a>Left Arm</a></li>
                            <li onclick="showColorModal('right_arm');setActiveTab(this, 'body_hover_div', 'color_rightarm_choose')" id="color_rightarm_choose"><a>Right Arm</a></li>
                            <li onclick="showColorModal('left_leg');setActiveTab(this, 'body_hover_div', 'color_leftleg_choose')" id="color_leftleg_choose"><a>Left Leg</a></li>
                            <li onclick="showColorModal('right_leg');setActiveTab(this, 'body_hover_div', 'color_rightleg_choose')" id="color_rightleg_choose"><a>Right Leg</a></li>
                        </ul>
                    </div>
                    
                    <div id="color-modals">
                        <!-- Color modals will be dynamically inserted here -->
                    </div>
                    
                    <div class="is-boxed is-centered">
                        <div id="1" class="tab-content"></div>
                        <div id="2" class="tab-content"></div>
                        <div id="3" class="tab-content"></div>
                        <div id="4" class="tab-content"></div>
                        <div id="5" class="tab-content"></div>
                        <div id="6" class="tab-content"></div>
                        <div id="7" class="tab-content"></div>
                        <div id="8" class="tab-content"></div>
                    </div>
                </div>
                
                <div class="push-5"></div>
                <div class="novello-title is-size-5">Wearing</div>
                <div class="box">
                    <div id="wearing">
                        <!-- Wearing items will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Include avatar.js script -->
<script src="/assets/js/avatar.js?r=<?php echo bin2hex(random_bytes(16)); ?>"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// PHP-generated avatar data
const initialAvatarData = <?= json_encode($avatarData) ?>;
const colorMap = <?= json_encode($Colors) ?>;
const colorValues = <?= json_encode(array_values($Colors)) ?>;

// Three.js variables
let scene, camera, renderer, controls;
let avatar = null;
let currentHats = [];
let currentGear = null;
let currentAccessory = null;
let currentHead = null;
let textureCache = {};
let modelCache = {};
let avatarData = {...initialAvatarData};

// Initialize Three.js
function initThreeJS() {
    // Create scene
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf0f0f0);
    
    // Create camera
    const container = document.getElementById('characterBox');
    const aspectRatio = container.clientWidth / container.clientHeight;
    camera = new THREE.PerspectiveCamera(45, aspectRatio, 0.1, 1000);
    camera.position.set(0, 1.2, 2.5);
    
    // Create renderer
    const canvas = document.getElementById('avatarCanvas');
    renderer = new THREE.WebGLRenderer({ 
        canvas: canvas,
        antialias: true,
        alpha: true
    });
    
    // Set initial size
    updateRendererSize();
    
    // Add lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambientLight);
    
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
    directionalLight.position.set(1, 2, 3);
    scene.add(directionalLight);
    
    // Add orbit controls
    controls = new THREE.OrbitControls(camera, canvas);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.minDistance = 1;
    controls.maxDistance = 5;
    controls.target.set(0, 1, 0);
    
    // Show canvas
    canvas.style.display = 'block';
    document.getElementById('loading').style.display = 'none';
    
    // Load the avatar
    loadAvatar();
}

// Update renderer size to fit container
function updateRendererSize() {
    const container = document.getElementById('characterBox');
    const canvas = document.getElementById('avatarCanvas');
    
    const width = container.clientWidth;
    const height = container.clientHeight;
    
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    
    renderer.setSize(width, height);
    renderer.setPixelRatio(window.devicePixelRatio);
}

// Function to update avatar colors without reloading
function updateAvatarColors() {
    if (!avatar) return;

    // Update colors on base avatar
    applyColors(avatar, avatarData);
    
    // Update colors on custom head if exists
    if (currentHead) {
        currentHead.traverse(child => {
            if (child.isMesh) {
                child.material.color.setRGB(
                    avatarData.headColor[0],
                    avatarData.headColor[1],
                    avatarData.headColor[2]
                );
                child.material.needsUpdate = true;
            }
        });
    }
}

// Load the avatar with current configuration
async function loadAvatar() {
    try {
        // Clear previous avatar
        if (avatar) scene.remove(avatar);
        currentHats.forEach(hat => scene.remove(hat));
        if (currentGear) scene.remove(currentGear);
        if (currentAccessory) scene.remove(currentAccessory);
        if (currentHead) scene.remove(currentHead);
        
        // Load base avatar
        avatar = await loadGLTF(avatarData.baseModel);
        applyColors(avatar, avatarData);
        applyTextures(avatar, avatarData);
        scene.add(avatar);
        
        // Load custom head
        if (avatarData.headModel) {
            currentHead = await loadHead(avatarData.headModel, avatarData);
            scene.add(currentHead);
        }
        
        // Load hats
        for (const hatId of avatarData.hats) {
            if (hatId) {
                const hat = await loadHat(hatId);
                if (hat) {
                    currentHats.push(hat);
                    scene.add(hat);
                }
            }
        }
        
        // Load gear
        if (avatarData.gear) {
            currentGear = await loadGear(avatarData.gear);
            if (currentGear) scene.add(currentGear);
        }
        
        // Load accessory
        if (avatarData.accessory) {
            currentAccessory = await loadAccessory(avatarData.accessory);
            if (currentAccessory) scene.add(currentAccessory);
        }
        
        // Start animation
        animate();
        
    } catch (error) {
        console.error('Error loading avatar:', error);
        const loadingEl = document.getElementById('loading');
        loadingEl.textContent = 'Error loading avatar: ' + error.message;
        loadingEl.style.display = 'block';
        loadingEl.style.color = 'red';
        loadingEl.style.fontWeight = 'bold';
        loadingEl.style.fontSize = '16px';
        loadingEl.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        loadingEl.style.padding = '10px';
        loadingEl.style.borderRadius = '5px';
        loadingEl.style.zIndex = '100';
    }
}

// Animation loop
function animate() {
    requestAnimationFrame(animate);
    if (controls) controls.update();
    if (renderer && scene && camera) renderer.render(scene, camera);
}

// Show inventory tab
function showInventoryTab(tab) {
    document.getElementById('clothing_choose_div').style.display = 'none';
    document.getElementById('accessories_choose_div').style.display = 'none';
    document.getElementById('body_choose_div').style.display = 'none';
    
    if (tab === 'clothing') {
        document.getElementById('clothing_choose_div').style.display = 'block';
    } else if (tab === 'accessories') {
        document.getElementById('accessories_choose_div').style.display = 'block';
    } else if (tab === 'body') {
        document.getElementById('body_choose_div').style.display = 'block';
    }
}

// Set active tab
function setActiveTab(element, mainTabId, tabId) {
    // Remove active classes
    document.querySelectorAll('#inventory_tabs ul li').forEach(li => {
        li.classList.remove('is-active');
    });
    document.querySelectorAll('#clothing_choose_div ul li').forEach(li => {
        li.classList.remove('is-active');
    });
    document.querySelectorAll('#accessories_choose_div ul li').forEach(li => {
        li.classList.remove('is-active');
    });
    document.querySelectorAll('#body_choose_div ul li').forEach(li => {
        li.classList.remove('is-active');
    });
    
    // Add active class to clicked tab
    element.classList.add('is-active');
    document.getElementById(mainTabId).classList.add('is-active');
}

// Create color modals
function createColorModals() {
    const parts = ['head', 'torso', 'left_arm', 'right_arm', 'left_leg', 'right_leg'];
    const partNames = {
        'head': 'Head',
        'torso': 'Torso',
        'left_arm': 'Left Arm',
        'right_arm': 'Right Arm',
        'left_leg': 'Left Leg',
        'right_leg': 'Right Leg'
    };
    
    const container = document.getElementById('color-modals');
    
    parts.forEach(part => {
        const modal = document.createElement('div');
        modal.id = `${part}_color_div`;
        modal.className = 'color-modal';
        modal.style.display = 'none';
        
        modal.innerHTML = `
            <div class="modal-background" onclick="hideColorModal('${part}')"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="title is-4">${partNames[part]} Color</h3>
                    <button class="close-modal" onclick="hideColorModal('${part}')">×</button>
                </div>
                <div class="color-grid">
                    ${colorValues.map((color, index) => `
                        <div class="color-swatch" 
                             style="background-color: rgb(${color});"
                             onclick="selectColor(${index + 1}, '${part}')">
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        container.appendChild(modal);
    });
}

// Show color modal
// AFTER (safe)
function showColorModal(part) {
    // Hide all modals first
    document.querySelectorAll('.color-modal').forEach(modal => {
        modal.style.display = 'none';
    });
    
    // Show the requested modal
    const modal = document.getElementById(`${part}_color_div`);
    if (modal) {
        modal.style.display = 'flex';
    }
    
    // Safely update tab state
    const tabElement = document.getElementById(`color_${part}_choose`);
    if (tabElement) {
        tabElement.classList.add('is-active');
    }
    
    // Also update body tab state
    showInventoryTab('body');
}

// Hide color modal
function hideColorModal(part) {
    const modal = document.getElementById(`${part}_color_div`);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Select a color
function selectColor(indicator, part) {
    try {
        // Update the color swatch preview
        const swatch = document.getElementById(part);
        if (swatch) {
            swatch.style.backgroundColor = `rgb(${colorValues[indicator - 1]})`;
        }
        
        // Call the color change function
        if (typeof color === 'function') {
            color(indicator, part);
        }
        
        // Hide the modal
        hideColorModal(part);
    } catch (error) {
        console.error('Error selecting color:', error);
    }
}

// ======== AJAX Functions ======== //
// Fetch updated avatar data from server
async function fetchAvatarData() {
    try {
        const response = await fetch('/api/rendering/get-avatar.php?uid=' + avatarData.userId);
        const data = await response.json();
        avatarData = data;
    } catch (error) {
        console.error('Error fetching avatar data:', error);
    }
}

// Refresh 3D avatar after updates
function refresh3DAvatar() {
    // Show loading indicator
    document.getElementById('loading').style.display = 'block';
    document.getElementById('avatarCanvas').style.display = 'none';
    
    // Refetch the latest avatar data from server
    fetchAvatarData().then(() => {
        loadAvatar();
    });
}

// ========== Three.js Helper Functions ========== //
async function loadGLTF(path) {
    return new Promise((resolve, reject) => {
        new THREE.GLTFLoader().load(
            path,
            gltf => resolve(gltf.scene),
            undefined,
            error => {
                console.error('Error loading GLTF:', error);
                reject(error);
            }
        );
    });
}

async function loadOBJ(path) {
    return new Promise((resolve, reject) => {
        new THREE.OBJLoader().load(path, 
            obj => resolve(obj), 
            undefined, 
            reject
        );
    });
}

async function loadTexture(path) {
    if (!path) return null;
    if (textureCache[path]) return textureCache[path];
    
    return new Promise((resolve, reject) => {
        new THREE.TextureLoader().load(path, 
            texture => {
                texture.encoding = THREE.sRGBEncoding;
                textureCache[path] = texture;
                resolve(texture);
            }, 
            undefined, 
            reject
        );
    });
}

// Modified applyColors function
function applyColors(model, data) {
    const parts = {
        'Head': data.headColor,
        'Torso': data.torsoColor,
        'LArm': data.leftArmColor,
        'RArm': data.rightArmColor,
        'LLeg': data.leftLegColor,
        'RLeg': data.rightLegColor
    };
    
    model.traverse(child => {
        if (child.isMesh && parts[child.name]) {
            // Convert to Three.js Color object
            const color = new THREE.Color(
                parts[child.name][0],
                parts[child.name][1],
                parts[child.name][2]
            );
            
            // Update material color
            if (Array.isArray(child.material)) {
                child.material.forEach(mat => {
                    mat.color = color;
                    mat.needsUpdate = true;
                });
            } else {
                child.material.color = color;
                child.material.needsUpdate = true;
            }
        }
    });
}

async function applyTextures(model, data) {
    // Load textures
    const textures = await Promise.all([
        loadTexture(data.faceTexture),
        loadTexture(data.shirtTexture),
        loadTexture(data.tshirtTexture),
        loadTexture(data.pantsTexture)
    ]);
    
    // Apply textures
    model.traverse(child => {
        if (!child.isMesh) return;
        
        if (child.name === 'Head' && textures[0]) {
            child.material.map = textures[0];
        } 
        else if ((child.name === 'LArm' || child.name === 'RArm') && textures[1]) {
            child.material.map = textures[1];
        }
        else if (child.name === 'Torso') {
            if (textures[1]) child.material.map = textures[1];
            
            // Add t-shirt layer if exists
            if (textures[2]) {
                const tshirtGeometry = child.geometry.clone();
                const tshirtMesh = new THREE.Mesh(tshirtGeometry, new THREE.MeshBasicMaterial({
                    map: textures[2],
                    transparent: true
                }));
                tshirtMesh.name = 'Tshirt';
                tshirtMesh.position.copy(child.position);
                child.parent.add(tshirtMesh);
            }
        }
        else if ((child.name === 'LLeg' || child.name === 'RLeg') && textures[3]) {
            child.material.map = textures[3];
        }
    });
}

async function loadHead(headId, data) {
    try {
        const headObj = await loadOBJ(
            `/JGFJFSHFSAH/core/rendering/head/${headId}/${headId}.obj`
        );
        
        const faceTex = await loadTexture(data.faceTexture);
        
        headObj.traverse(child => {
            if (child.isMesh) {
                child.material = new THREE.MeshStandardMaterial({ 
                    map: faceTex,
                    color: new THREE.Color(...data.headColor)
                });
            }
        });
        
        return headObj;
    } catch (error) {
        console.error('Error loading head:', error);
        return null;
    }
}

async function loadHat(hatId) {
    try {
        const hatObj = await loadOBJ(
            `/JGFJFSHFSAH/core/rendering/hat/${hatId}/${hatId}.obj`
        );
        
        const hatTex = await loadTexture(
            `/JGFJFSHFSAH/core/rendering/hat/${hatId}/${hatId}.png`
        );
        
        hatObj.traverse(child => {
            if (child.isMesh) {
                child.material = new THREE.MeshStandardMaterial({ map: hatTex });
            }
        });
        
        // Position hat
        hatObj.position.y = 1.5;
        return hatObj;
    } catch (error) {
        console.error('Error loading hat:', error);
        return null;
    }
}

async function loadGear(gearId) {
    try {
        const gearObj = await loadOBJ(
            `/JGFJFSHFSAH/core/rendering/gear/${gearId}/${gearId}.obj`
        );
        
        const gearTex = await loadTexture(
            `/JGFJFSHFSAH/core/rendering/gear/${gearId}/${gearId}.png`
        );
        
        gearObj.traverse(child => {
            if (child.isMesh) {
                child.material = new THREE.MeshStandardMaterial({ map: gearTex });
            }
        });
        
        gearObj.position.set(0, 1, -0.2);
        return gearObj;
    } catch (error) {
        console.error('Error loading gear:', error);
        return null;
    }
}

async function loadAccessory(accessoryId) {
    try {
        const accObj = await loadOBJ(
            `/JGFJFSHFSAH/core/rendering/accessories/${accessoryId}/${accessoryId}.obj`
        );
        
        const accTex = await loadTexture(
            `/JGFJFSHFSAH/core/rendering/accessories/${accessoryId}/${accessoryId}.png`
        );
        
        accObj.traverse(child => {
            if (child.isMesh) {
                child.material = new THREE.MeshStandardMaterial({ map: accTex });
            }
        });
        
        accObj.position.set(0.2, 1, 0);
        return accObj;
    } catch (error) {
        console.error('Error loading accessory:', error);
        return null;
    }
}

// Initialize when page loads
window.addEventListener('DOMContentLoaded', () => {
    initThreeJS();
    createColorModals();
    
    // Event listeners
    document.getElementById('btnRefresh')?.addEventListener('click', refreshAvatar);
    
    // Initialize inventory
    get_inventory(3, 1);
    get_wearing();
});

// Clean up on page unload
window.addEventListener('beforeunload', () => {
    if (renderer) {
        renderer.dispose();
        while(scene.children.length > 0){ 
            scene.remove(scene.children[0]); 
        }
    }
});

// Add resize handler
window.addEventListener('resize', () => {
    if (renderer && camera) {
        updateRendererSize();
    }
});

// Override avatar.js functions to work with 3D
window.refreshAvatar = function() {
    // Call original refreshAvatar from avatar.js
    originalRefreshAvatar();
    
    // Refresh 3D avatar
    refresh3DAvatar();
};

window.wear = function(item) {
    // Call original wear from avatar.js
    originalWear(item);
    
    // Refresh 3D avatar
    refresh3DAvatar();
};

window.remove = function(item) {
    // Call original remove from avatar.js
    originalRemove(item);
    
    // Refresh 3D avatar
    refresh3DAvatar();
};

// Create normalized color values for Three.js (0-1 range)
const colorValuesNormalized = colorValues.map(rgbStr => {
    const [r, g, b] = rgbStr.split(',').map(Number);
    return [r/255, g/255, b/255];
});

// AFTER (fixed)
window.color = function(indicator, part) {
    // Call original color function from avatar.js
    if (typeof originalColor === 'function') {
        originalColor(indicator, part);
    }
    
    // Update local avatarData immediately
    const colorIndex = indicator - 1;
    if (colorIndex >= 0 && colorIndex < colorValuesNormalized.length) {
        const normalizedColor = colorValuesNormalized[colorIndex];
        
        switch(part) {
            case 'head': 
                avatarData.headColor = normalizedColor;
                break;
            case 'torso': 
                avatarData.torsoColor = normalizedColor;
                break;
            case 'left_arm': 
                avatarData.leftArmColor = normalizedColor;
                break;
            case 'right_arm': 
                avatarData.rightArmColor = normalizedColor;
                break;
            case 'left_leg': 
                avatarData.leftLegColor = normalizedColor;
                break;
            case 'right_leg': 
                avatarData.rightLegColor = normalizedColor;
                break;
        }
        
        // Update 3D model colors in real-time
        updateAvatarColors();
    }
};

// PRESERVE ORIGINAL FUNCTIONS FIRST
const originalRefreshAvatar = window.refreshAvatar;
const originalWear = window.wear;
const originalRemove = window.remove;
const originalColor = window.color;

// THEN OVERRIDE THEM
window.refreshAvatar = function() {
    if (typeof originalRefreshAvatar === 'function') {
        originalRefreshAvatar();
    }
    refresh3DAvatar();
};

window.wear = function(item) {
    if (typeof originalWear === 'function') {
        originalWear(item);
    }
    refresh3DAvatar();
};

window.remove = function(item) {
    if (typeof originalRemove === 'function') {
        originalRemove(item);
    }
    refresh3DAvatar();
};

window.color = function(indicator, part) {
    if (typeof originalColor === 'function') {
        originalColor(indicator, part);
    }
    refresh3DAvatar();
};
</script>

<?php
$app->footer();
?>