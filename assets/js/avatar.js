/**
 * ============================================================================
 * PART 1: LEGACY UI HANDLERS
 * Keeps your existing inventory/button logic working
 * ============================================================================
 */

var last_category;
var last_page;

// Initialize on load
window.onload = function() {
    // Load default inventory tab
    if(document.getElementById('3')) {
        get_inventory(3, 1); 
    }
    get_wearing();
    
    // Initialize 3D Avatar if canvas exists
    if(document.getElementById('avatarCanvas')) {
        // Get User ID from URL or global variable
        const userId = typeof currentUserId !== 'undefined' ? currentUserId : (window.location.pathname.split('/').pop() || 1);
        window.renderer = new AvatarRenderer('avatarCanvas', userId);
    }
};

function removeAllChildNodes(parent) {
    while (parent.firstChild) {
        parent.removeChild(parent.firstChild);
    }
}

function hide_avatar_divs() {
    const ids = ['1','2','3','4','5','6','7','8'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if(el) removeAllChildNodes(el);
    });
}

function hide_avatar_hover_divs() {
    const divs = ['clothing_choose_div', 'accessories_choose_div', 'body_choose_div'];
    divs.forEach(id => {
        const el = document.getElementById(id);
        if(el) el.style.display = 'none';
    });
}

function remove_active_avatar_divs() {
    const classes = [
        'clothing_hover_div', 'accessories_hover_div', 'body_hover_div',
        'cat1', 'cat2', 'cat3', 'cat4', 'cat5', 'cat6', 'cat7', 'cat8'
    ];
    classes.forEach(id => {
        const el = document.getElementById(id);
        if(el) el.classList.remove("is-active");
    });
}

function get_inventory(category, page) {
    if(!document.getElementById(category)) return;

    hide_avatar_divs();
    last_category = category;
    last_page = page;
    
    // Visual loading state could go here
    
    $.post('/api/rendering/get-inventory.php', {
        category: category,
        page: page
    }).done(function(data) {
        $("#" + category).html(data);
    });
}

function get_wearing() {
    $.post('/api/rendering/get-wearing.php', {}).done(function(data) {
        $("#wearing").html(data);
    });
}

// ACTION: Wear Item
function wear(item) {
    $.post('/api/rendering/wear.php', { item: item })
    .done(function(data) {
        get_wearing();
        // Refresh 3D Render
        if(window.renderer) window.renderer.reloadAvatar();
    });
}

// ACTION: Remove Item
function remove(item) {
    $.post('/api/rendering/remove.php', { item: item })
    .done(function(data) {
        get_wearing();
        // Refresh 3D Render
        if(window.renderer) window.renderer.reloadAvatar();
    });
}

// ACTION: Update Color (Called from color picker)
// Expected format: color('head', '0.5, 0.2, 0.2')
function updateColor(part, colorStr) {
    $.post('/api/rendering/color.php', { part: part, color: colorStr })
    .done(function(data) {
        if(window.renderer) window.renderer.updatePartColor(part, colorStr);
    });
}

// 2D/3D Toggle Helper
function toggleViewMode(mode) {
    const canvas = document.getElementById('avatarCanvas');
    const img = document.getElementById('avatarImage2D');
    
    if(mode === '3d') {
        if(canvas) canvas.style.display = 'block';
        if(img) img.style.display = 'none';
    } else {
        if(canvas) canvas.style.display = 'none';
        if(img) img.style.display = 'block';
    }
}


/**
 * ============================================================================
 * PART 2: MODERN THREE.JS RENDERER CLASS
 * Handles the heavy lifting of 3D rendering
 * ============================================================================
 */

class AvatarRenderer {
    constructor(canvasId, userId) {
        this.canvas = document.getElementById(canvasId);
        this.userId = userId;
        if (!this.canvas) return;

        // Configuration
        this.config = {
            paths: {
                baseModel: '/JGFJFSHFSAH/core/rendering/avatar/avatar.gltf', // Update this path
                baseWithGear: '/JGFJFSHFSAH/core/rendering/avatar/avatar10.gltf', // Update this path
                assets: '/assets/items/', // Path to hats/gear
                textures: '/assets/textures/' // Path to faces/shirts
            }
        };

        // Scene Setup
        this.scene = new THREE.Scene();
        // this.scene.background = new THREE.Color(0xf0f2f5); // Uncomment for gray background
        
        // Camera
        const aspect = this.canvas.clientWidth / this.canvas.clientHeight;
        this.camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 100);
        this.camera.position.set(0, 1.4, 3.5);

        // Renderer
        this.renderer = new THREE.WebGLRenderer({ 
            canvas: this.canvas, 
            antialias: true, 
            alpha: true, // Transparent background
            preserveDrawingBuffer: true 
        });
        this.renderer.setSize(this.canvas.clientWidth, this.canvas.clientHeight);
        this.renderer.outputEncoding = THREE.sRGBEncoding;
        this.renderer.shadowMap.enabled = true;

        // Lighting
        this.setupLights();

        // Controls
        this.controls = new THREE.OrbitControls(this.camera, this.canvas);
        this.controls.target.set(0, 1, 0);
        this.controls.enablePan = false;
        this.controls.minDistance = 2;
        this.controls.maxDistance = 6;

        // Loaders
        this.gltfLoader = new THREE.GLTFLoader();
        this.texLoader = new THREE.TextureLoader();

        // State
        this.currentAvatarMesh = null;
        this.accessories = [];

        // Initial Load
        this.reloadAvatar();
        this.animate();

        // Handle Resize
        window.addEventListener('resize', () => this.onResize());
    }

    setupLights() {
        const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 0.8);
        hemiLight.position.set(0, 20, 0);
        this.scene.add(hemiLight);

        const dirLight = new THREE.DirectionalLight(0xffffff, 0.5);
        dirLight.position.set(3, 10, 10);
        dirLight.castShadow = true;
        this.scene.add(dirLight);
    }

    async reloadAvatar() {
        // 1. Fetch latest data from API
        try {
            const response = await fetch(`/api/rendering/get-avatar-data.php?id=${this.userId}`);
            const data = await response.json();
            this.buildAvatar(data);
        } catch (error) {
            console.error("Failed to fetch avatar data", error);
        }
    }

    async buildAvatar(data) {
        // Clear Scene (except lights)
        this.scene.children = this.scene.children.filter(c => c.isLight);
        
        // Determine Base Model (Gear vs No Gear pose)
        const modelPath = data.assets.gear ? this.config.paths.baseWithGear : this.config.paths.baseModel;

        // Load Base
        this.gltfLoader.load(modelPath, (gltf) => {
            const model = gltf.scene;
            this.currentAvatarMesh = model;
            
            // 1. Apply Body Colors
            this.applyColors(model, data.colors);

            // 2. Apply Textures (Face, Shirt, Pants)
            this.applyTextures(model, data.assets);

            this.scene.add(model);

            // 3. Load Accessories (Hats)
            if (data.assets.hats && Array.isArray(data.assets.hats)) {
                data.assets.hats.forEach(hatId => this.loadAccessory(hatId));
            }
            
            // 4. Load Gear
            if (data.assets.gear) {
                this.loadAccessory(data.assets.gear);
            }
        });
    }

    applyColors(model, colors) {
        if(!colors) return;
        
        // Normalize color helper (converts "255, 0, 0" to THREE.Color)
        const getColor = (str) => {
            if(!str) return new THREE.Color(1,1,1);
            const [r,g,b] = str.split(',').map(v => parseFloat(v.trim()));
            // Check if values are 0-1 or 0-255
            if(r > 1 || g > 1 || b > 1) return new THREE.Color(r/255, g/255, b/255);
            return new THREE.Color(r,g,b);
        };

        model.traverse((o) => {
            if (o.isMesh) {
                // Map mesh names to color keys. Adjust these names based on your actual GLTF file node names
                if (o.name.includes('Head')) o.material.color = getColor(colors.head);
                else if (o.name.includes('Torso')) o.material.color = getColor(colors.torso);
                else if (o.name.includes('LeftArm')) o.material.color = getColor(colors.left_arm);
                else if (o.name.includes('RightArm')) o.material.color = getColor(colors.right_arm);
                else if (o.name.includes('LeftLeg')) o.material.color = getColor(colors.left_leg);
                else if (o.name.includes('RightLeg')) o.material.color = getColor(colors.right_leg);
            }
        });
    }

    applyTextures(model, assets) {
        const loadTex = (url, materialNameKeywords) => {
            if (!url) return;
            this.texLoader.load(url, (tex) => {
                tex.flipY = false; // GLTF standard
                tex.encoding = THREE.sRGBEncoding;
                
                model.traverse((o) => {
                    if (o.isMesh) {
                        // Logic to find correct mesh for texture. 
                        // e.g. Shirt goes on Torso and Arms usually
                        const name = o.name.toLowerCase();
                        materialNameKeywords.forEach(keyword => {
                            if(name.includes(keyword)) {
                                o.material.map = tex;
                                o.material.needsUpdate = true;
                            }
                        });
                    }
                });
            });
        };

        // Face
        if(assets.face) loadTex(assets.face, ['head']);
        // Shirt (Applied to Torso and Arms)
        if(assets.shirt) loadTex(assets.shirt, ['torso', 'arm']);
        // Pants (Applied to Legs and Torso usually)
        if(assets.pants) loadTex(assets.pants, ['leg', 'torso']);
        // T-Shirt (Applied to Torso front plane - requires specific mesh usually)
        if(assets.tshirt) loadTex(assets.tshirt, ['tshirt']);
    }

    loadAccessory(id) {
        if(!id || id == 0) return;
        
        // Assuming API returns path or constructing it here
        // You might need an endpoint that resolves Item ID -> .obj/.gltf path
        // For now, assuming direct mapping or simple path
        const path = `/assets/hats/${id}.obj`; // Or .gltf

        // Check extension to decide loader
        if(path.endsWith('.gltf') || path.endsWith('.glb')) {
            this.gltfLoader.load(path, (gltf) => {
                this.scene.add(gltf.scene);
            });
        } 
        // Fallback for OBJ (Common in older engines)
        else {
            // You would need OBJLoader included in HTML for this
            if(typeof THREE.OBJLoader !== 'undefined') {
                new THREE.OBJLoader().load(path, (obj) => {
                    // Apply default material
                    obj.traverse(c => { if(c.isMesh) c.material = new THREE.MeshStandardMaterial({color: 0xffffff}); });
                    this.scene.add(obj);
                });
            }
        }
    }

    // Helper for immediate color updates from Color Picker without reload
    updatePartColor(part, colorStr) {
        if(!this.currentAvatarMesh) return;
        
        const [r,g,b] = colorStr.split(',').map(v => parseFloat(v));
        const color = (r > 1 ? new THREE.Color(r/255, g/255, b/255) : new THREE.Color(r,g,b));

        const map = {
            'head': 'Head', 'torso': 'Torso',
            'left_arm': 'LeftArm', 'right_arm': 'RightArm',
            'left_leg': 'LeftLeg', 'right_leg': 'RightLeg'
        };

        const targetName = map[part];
        if(targetName) {
            this.currentAvatarMesh.traverse((o) => {
                if(o.isMesh && o.name.includes(targetName)) {
                    o.material.color = color;
                }
            });
        }
    }

    onResize() {
        if (!this.canvas) return;
        const width = this.canvas.parentElement.clientWidth; // Use parent container width
        const height = this.canvas.clientHeight; // Keep defined height
        
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    animate() {
        requestAnimationFrame(() => this.animate());
        this.controls.update();
        this.renderer.render(this.scene, this.camera);
    }
}