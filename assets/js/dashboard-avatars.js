/**
 * 3D Avatar Renderer with 2D Still Image Capture - COMBINED & FIXED
 * Combines both scripts with best practices for face rendering
 */

// ============================================================================
// GLOBAL CACHE MANAGEMENT
// ============================================================================

window.THREE_LOADED = window.THREE_LOADED || false;

const globalModelCache = new Map();
const globalTextureCache = new Map();
const globalAvatarCache = new Map();
const loadingPromises = new Map();

// ============================================================================
// MAIN RENDERER CLASS - OPTIMIZED FACE RENDERING
// ============================================================================

class DashboardAvatarRenderer3DTo2D {
    constructor(canvas) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.userId = parseInt(canvas.dataset.userId) || 0;
        this.renderType = canvas.dataset.renderType || 'avatar';
        this.itemTypeFocus = canvas.dataset.itemTypeFocus || '';

        if (!this.ctx) {
            console.error('Could not get 2D context for canvas');
            return;
        }

        canvas.__avatarRenderer = this;

        this.userColors = this.parseColors();
        this.equippedItems = this.parseItems();

        console.log(`Avatar ${this.userId} items:`, this.equippedItems);

        this.canvas.style.backgroundColor = '#1a1a2e';
        this.canvas.style.display = 'block';

        this.config = {
            faceTexturePath: '/JGFJFSHFSAH/core/rendering/faces',
            itemTexturePath: '/JGFJFSHFSAH/core/rendering',
            avatarModelPath: '/JGFJFSHFSAH/core/rendering/avatar/avatar.glb',

            camera: {
                fov: 35,
                position: { x: 0, y: 2.0, z: 4 },
                target: { x: 0, y: 1.8, z: 0 }
            },

            renderWidth: 400,
            renderHeight: 400,

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

        this.modelCache = globalModelCache;
        this.textureCache = globalTextureCache;
        this.avatarCache = globalAvatarCache;

        this.loadedItems = new Map();
        this.appliedTextures = new Map();
        this.faceOverlayMeshes = new Map();

        this.model = null;
        this.configHash = this.generateConfigHash();

        this.renderDebounced();
    }

    // -------------------------
    // Data Parsing - Fixed face handling
    // -------------------------
    parseColors() {
        try {
            if (this.canvas.dataset.colors) {
                const colors = JSON.parse(this.canvas.dataset.colors);
                return {
                    head: this.ensureColorArray(colors.head) || [1, 0.8, 0.6],
                    torso: this.ensureColorArray(colors.torso) || [0.2, 0.2, 0.8],
                    leftArm: this.ensureColorArray(colors.leftArm) || [1, 0.8, 0.6],
                    rightArm: this.ensureColorArray(colors.rightArm) || [1, 0.8, 0.6],
                    leftLeg: this.ensureColorArray(colors.leftLeg) || [0.1, 0.1, 0.1],
                    rightLeg: this.ensureColorArray(colors.rightLeg) || [0.1, 0.1, 0.1]
                };
            }
        } catch (e) {
            console.error('Error parsing colors:', e);
        }

        return {
            head: [1, 0.8, 0.6],
            torso: [0.2, 0.2, 0.8],
            leftArm: [1, 0.8, 0.6],
            rightArm: [1, 0.8, 0.6],
            leftLeg: [0.1, 0.1, 0.1],
            rightLeg: [0.1, 0.1, 0.1]
        };
    }

    parseItems() {
        try {
            if (this.canvas.dataset.items) {
                const items = JSON.parse(this.canvas.dataset.items);
                console.log('Raw items data:', items);

                let hats = [];

                if (items.hats && Array.isArray(items.hats)) {
                    hats = items.hats.filter(id => id > 0);
                } else {
                    if ('hat1' in items && items.hat1 && items.hat1 > 0) hats.push(items.hat1);
                    if ('hat2' in items && items.hat2 && items.hat2 > 0) hats.push(items.hat2);
                    if ('hat3' in items && items.hat3 && items.hat3 > 0) hats.push(items.hat3);
                }

                let face = 'default';
                if ('face' in items) {
                    const f = items.face;
                    if (f === 'default' || f === null || f === undefined) {
                        face = 'default';
                    } else if (typeof f === 'number') {
                        face = f.toString();
                    } else if (typeof f === 'string' && f !== '') {
                        face = f;
                    } else {
                        face = 'default';
                    }
                }

                return {
                    hats: hats,
                    face: face,
                    accessory: ('accessory' in items) ? items.accessory || 0 : 0,
                    tshirt: ('tshirt' in items) ? items.tshirt || 0 : 0,
                    shirt: ('shirt' in items) ? items.shirt || 0 : 0,
                    pants: ('pants' in items) ? items.pants || 0 : 0,
                    gear: ('gear' in items) ? items.gear || 0 : 0
                };
            }
        } catch (e) {
            console.error('Error parsing items:', e);
        }

        return {
            hats: [],
            face: 'default',
            accessory: 0,
            tshirt: 0,
            shirt: 0,
            pants: 0,
            gear: 0
        };
    }

    ensureColorArray(color) {
        if (!color) return null;

        if (Array.isArray(color)) {
            return color.map(val => {
                const num = parseFloat(val);
                return isNaN(num) ? 0 : Math.max(0, Math.min(1, num));
            });
        } else if (typeof color === 'string') {
            const parts = color.split(',').map(part => {
                const num = parseFloat(part.trim());
                return isNaN(num) ? 0 : Math.max(0, Math.min(1, num));
            });
            return parts.length === 3 ? parts : null;
        }
        return null;
    }

    generateConfigHash() {
        const data = {
            colors: this.userColors,
            items: this.equippedItems,
            userId: this.userId,
            renderType: this.renderType,
            itemTypeFocus: this.itemTypeFocus
        };
        return JSON.stringify(data);
    }

    // -------------------------
    // Render Pipeline
    // -------------------------
    async renderDebounced() {
        if (this.avatarCache.has(this.configHash)) {
            const cachedImage = this.avatarCache.get(this.configHash);
            this.drawImageToCanvas(cachedImage);
            return;
        }

        const loadingKey = `loading_${this.configHash}`;
        if (loadingPromises.has(loadingKey)) {
            await loadingPromises.get(loadingKey);
            if (this.avatarCache.has(this.configHash)) {
                const cachedImage = this.avatarCache.get(this.configHash);
                this.drawImageToCanvas(cachedImage);
            }
            return;
        }

        const renderPromise = this.renderWithFallback();
        loadingPromises.set(loadingKey, renderPromise);

        try {
            const imageData = await renderPromise;
            this.avatarCache.set(this.configHash, imageData);
            this.drawImageToCanvas(imageData);
        } catch (error) {
            console.error('Avatar render failed:', error);
            this.drawFallback();
        } finally {
            loadingPromises.delete(loadingKey);
        }
    }

    async renderWithFallback() {
        try {
            return await this.render3DScene();
        } catch (error) {
            console.warn('3D render failed:', error);
            return await this.renderSimple2DFallback();
        }
    }

    async render3DScene() {
        return new Promise(async (resolve, reject) => {
            try {
                if (!window.THREE) {
                    await this.loadThreeJS();
                }

                const offscreenCanvas = document.createElement('canvas');
                offscreenCanvas.width = this.config.renderWidth;
                offscreenCanvas.height = this.config.renderHeight;

                const THREE = window.THREE;

                const renderer = new THREE.WebGLRenderer({
                    canvas: offscreenCanvas,
                    alpha: true,
                    antialias: true,
                    preserveDrawingBuffer: true
                });
                renderer.setSize(this.config.renderWidth, this.config.renderHeight);
                renderer.setPixelRatio(1);
                renderer.outputEncoding = THREE.sRGBEncoding;
                renderer.shadowMap.enabled = true;
                renderer.shadowMap.type = THREE.PCFSoftShadowMap;

                const scene = new THREE.Scene();
                scene.background = this.createGradientBackground();

                const camera = new THREE.PerspectiveCamera(
                    this.config.camera.fov,
                    this.config.renderWidth / this.config.renderHeight,
                    0.1,
                    100
                );

                this.setupEnhancedLighting(scene);
                this.setupEnvironment(scene);

                // OPTIMIZED LOADING ORDER: Base model -> 3D items -> Clothing textures -> Face (LAST)
                await this.loadBaseModel(scene);
                this.applyColorsToModel();
                
                // 1. Load 3D items first
                await this.load3DItemsOnly(scene);
                
                // 2. Load clothing textures
                await this.loadClothingTextures(scene);
                
                // 3. Load face LAST to ensure it renders on top
                if (this.equippedItems.face && this.equippedItems.face !== 'default') {
                    await this.loadTextureItem(this.equippedItems.face, 'face', scene);
                }

                this.adjustCamera(scene, camera);
                renderer.render(scene, camera);
                const imageData = offscreenCanvas.toDataURL('image/png');

                this.cleanupThreeJSScene(scene, renderer);
                resolve(imageData);

            } catch (error) {
                reject(error);
            }
        });
    }

    // -------------------------
    // Scene Setup
    // -------------------------
    createGradientBackground() {
        const THREE = window.THREE;
        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 256;
        const context = canvas.getContext('2d');

        const gradient = context.createLinearGradient(0, 0, 0, 256);
        gradient.addColorStop(0, '#2a2a4e');
        gradient.addColorStop(1, '#16213e');

        context.fillStyle = gradient;
        context.fillRect(0, 0, 256, 256);

        return new THREE.CanvasTexture(canvas);
    }

    setupEnhancedLighting(scene) {
        const THREE = window.THREE;

        const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
        scene.add(ambientLight);

        const mainLight = new THREE.DirectionalLight(0xffffff, 0.8);
        mainLight.position.set(5, 10, 5);
        mainLight.castShadow = true;
        mainLight.shadow.mapSize.width = 1024;
        mainLight.shadow.mapSize.height = 1024;
        scene.add(mainLight);

        const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
        fillLight.position.set(-5, 5, -5);
        scene.add(fillLight);

        const rimLight = new THREE.DirectionalLight(0xffffff, 0.4);
        rimLight.position.set(0, 5, -10);
        scene.add(rimLight);

        const hemisphereLight = new THREE.HemisphereLight(0x87CEEB, 0x8B4513, 0.3);
        scene.add(hemisphereLight);
    }

    setupEnvironment(scene) {
        const THREE = window.THREE;
        const groundGeometry = new THREE.PlaneGeometry(20, 20);
        const groundMaterial = new THREE.ShadowMaterial({ opacity: 0.2, transparent: true });
        const ground = new THREE.Mesh(groundGeometry, groundMaterial);
        ground.rotation.x = -Math.PI / 2;
        ground.position.y = -1;
        ground.receiveShadow = true;
        scene.add(ground);
    }

    // -------------------------
    // Model Loading
    // -------------------------
    async loadBaseModel(scene) {
        const cacheKey = 'base_avatar_model';

        if (this.modelCache.has(cacheKey)) {
            this.model = this.modelCache.get(cacheKey).clone();
            this.model.traverse((object) => {
                if (object.isMesh) {
                    object.castShadow = true;
                    object.receiveShadow = true;
                }
            });
            scene.add(this.model);
            return;
        }

        return new Promise((resolve, reject) => {
            const THREE = window.THREE;
            if (!THREE.GLTFLoader) {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js';
                script.onload = () => this.loadGLTFModel(scene, cacheKey, resolve, reject);
                script.onerror = reject;
                document.head.appendChild(script);
            } else {
                this.loadGLTFModel(scene, cacheKey, resolve, reject);
            }
        });
    }

    loadGLTFModel(scene, cacheKey, resolve, reject) {
        const THREE = window.THREE;
        const loader = new THREE.GLTFLoader();

        loader.load(
            this.config.avatarModelPath,
            (gltf) => {
                this.model = gltf.scene;
                this.model.traverse((object) => {
                    if (object.isMesh) {
                        object.castShadow = true;
                        object.receiveShadow = true;
                        object.material = new THREE.MeshStandardMaterial({
                            color: object.material.color,
                            map: object.material.map,
                            roughness: 0.7,
                            metalness: 0.1
                        });
                    }
                });
                this.model.scale.set(1, 1, 1);
                this.model.position.set(0, 0, 0);
                this.modelCache.set(cacheKey, this.model.clone());
                scene.add(this.model);
                resolve();
            },
            undefined,
            (error) => {
                console.error('Error loading avatar model:', error);
                reject(error);
            }
        );
    }

    applyColorsToModel() {
        if (!this.model) return;
        const colorMap = {
            'head': ['Head'],
            'torso': ['Torso'],
            'leftArm': ['LArm', 'LeftArm'],
            'rightArm': ['RArm', 'RightArm'],
            'leftLeg': ['LLeg', 'LeftLeg'],
            'rightLeg': ['RLeg', 'RightLeg']
        };
        this.model.traverse((object) => {
            if (object.isMesh) {
                for (const [colorName, meshNames] of Object.entries(colorMap)) {
                    if (meshNames.some(name => object.name.includes(name))) {
                        const color = this.userColors[colorName];
                        if (color) object.material.color.setRGB(color[0], color[1], color[2]);
                    }
                }
            }
        });
    }

    // -------------------------
    // Optimized Item Loading Order
    // -------------------------
    async load3DItemsOnly(scene) {
        const promises = [];
        const items = this.equippedItems;
        
        if (items.hats && items.hats.length > 0) {
            items.hats.forEach((hatId, idx) => {
                if (hatId && hatId > 0) promises.push(this.load3DItem(hatId, 'hat', scene, idx));
            });
        }
        if (items.gear && items.gear > 0) promises.push(this.load3DItem(items.gear, 'gear', scene));
        if (items.accessory && items.accessory > 0) promises.push(this.load3DItem(items.accessory, 'accessory', scene));
        
        await Promise.all(promises);
    }

    async loadClothingTextures(scene) {
        const promises = [];
        const items = this.equippedItems;
        
        if (items.shirt && Number(items.shirt) > 0) promises.push(this.loadTextureItem(items.shirt, 'shirt', scene));
        if (items.tshirt && Number(items.tshirt) > 0) promises.push(this.loadTextureItem(items.tshirt, 'tshirt', scene));
        if (items.pants && Number(items.pants) > 0) promises.push(this.loadTextureItem(items.pants, 'pants', scene));
        
        await Promise.all(promises);
    }

    async loadTextureItem(itemId, itemType, scene) {
        const cacheKey = `${itemType}_${itemId}`;
        if (this.textureCache.has(cacheKey)) {
            const texture = this.textureCache.get(cacheKey);
            if (itemType === 'face') {
                this.applyFaceOverlay(texture, itemId, scene);
            } else {
                this.applyTextureOverlay(texture, itemType, scene, itemId);
            }
            return;
        }

        return new Promise((resolve) => {
            const THREE = window.THREE;
            const path = `${this.config.itemPaths[itemType]}/${itemId}/${itemId}.png`;
            const loader = new THREE.TextureLoader();

            console.log(`Loading ${itemType} texture from:`, path);

            loader.load(path, (texture) => {
                texture.flipY = false;
                texture.encoding = THREE.sRGBEncoding;
                this.textureCache.set(cacheKey, texture);

                if (itemType === 'face') {
                    console.log(`Loaded face texture ${itemId} -> applying overlay`);
                    this.applyFaceOverlay(texture, itemId, scene);
                } else {
                    this.applyTextureOverlay(texture, itemType, scene, itemId);
                }

                this.appliedTextures.set(cacheKey, { texture, itemType });
                console.log(`✅ Loaded ${itemType} texture ${itemId}`);
                resolve();
            }, undefined, (error) => {
                console.warn(`Failed to load ${itemType} texture ${itemId}:`, error);
                resolve();
            });
        });
    }

    // -------------------------
    // FACE OVERLAY - FIXED VERSION
    // -------------------------
    applyFaceOverlay(texture, itemId, scene) {
        if (!texture || !this.model) {
            console.warn('applyFaceOverlay: missing texture or base model', { texture, itemId });
            return;
        }

        // Remove any existing face overlays first
        this.removeFaceOverlay();

        // Find head mesh
        let headMesh = null;
        this.model.traverse((object) => {
            if (object.isMesh && object.name && object.name.toLowerCase().includes('head')) {
                headMesh = object;
            }
        });

        if (!headMesh) {
            console.error('Could not find head mesh for face overlay');
            return;
        }

        // Clone geometry
        const overlayGeometry = headMesh.geometry.clone();

        // Create overlay material with exact same settings as avatar editor
        const overlayMaterial = new window.THREE.MeshStandardMaterial({
            map: texture,
            transparent: true,
            alphaTest: 0.1,
            roughness: 0.5,
            metalness: 0.0,
            side: window.THREE.FrontSide,
            depthWrite: false,
            depthTest: false  // Ensure face renders on top
        });

        // Create overlay mesh
        const overlayMesh = new window.THREE.Mesh(overlayGeometry, overlayMaterial);

        // Copy transforms
        overlayMesh.position.copy(headMesh.position);
        overlayMesh.rotation.copy(headMesh.rotation);
        overlayMesh.scale.copy(headMesh.scale);

        // Render order + z offset to prevent z-fighting
        overlayMesh.renderOrder = 1000;
        overlayMesh.position.z += 0.0005;

        // Add overlay to the scene root to ensure it's on top
        const parentScene = scene || (this.model.parent && this.model.parent.isScene ? this.model.parent : null);
        if (parentScene && parentScene.isScene) {
            parentScene.add(overlayMesh);
        } else {
            // fallback to model parent
            try {
                (this.model.parent && this.model.parent.add) 
                    ? this.model.parent.add(overlayMesh)
                    : this.model.add(overlayMesh);
            } catch (e) {
                console.warn('applyFaceOverlay: fallback add failed', e);
            }
        }

        // Store references
        this.faceOverlayMeshes.set(`face_${itemId}`, overlayMesh);
        this.loadedItems.set(`face_${itemId}`, overlayMesh);
        this.appliedTextures.set(`face_${itemId}`, { texture, itemType: 'face' });

        console.log(`✅ Created face overlay mesh for item ${itemId}`);
    }

    applyTextureOverlay(texture, itemType, scene, itemId) {
        if (!this.model) return;

        const bodyPartMapping = {
            'shirt': ['Torso', 'LArm', 'RArm', 'LeftArm', 'RightArm'],
            'tshirt': ['Torso'],
            'pants': ['LLeg', 'RLeg', 'LeftLeg', 'RightLeg']
        };

        const parts = bodyPartMapping[itemType] || [];

        this.model.traverse((object) => {
            if (object.isMesh && parts.some(part => object.name && object.name.includes(part))) {
                if (!object.userData.originalMaterial) {
                    object.userData.originalMaterial = object.material;
                }

                const overlayMaterial = new window.THREE.MeshStandardMaterial({
                    map: texture,
                    transparent: true,
                    alphaTest: 0.1,
                    roughness: 0.5,
                    metalness: 0.0,
                    side: window.THREE.FrontSide
                });

                object.material = overlayMaterial;
                object.userData.hasTextureOverlay = true;
            }
        });

        if (itemId) {
            this.appliedTextures.set(`${itemType}_${itemId}`, { texture, itemType });
        }
    }

    removeFaceOverlay() {
        try {
            this.faceOverlayMeshes.forEach((mesh, key) => {
                if (mesh) {
                    if (mesh.parent) mesh.parent.remove(mesh);
                    try {
                        if (mesh.geometry) mesh.geometry.dispose();
                        if (mesh.material) {
                            // Don't dispose shared textures
                            if (mesh.material.map) {
                                const isCached = Array.from(this.textureCache.values()).some(
                                    t => t && t.image && mesh.material.map && t.image === mesh.material.map.image
                                );
                                if (!isCached) mesh.material.map.dispose();
                            }
                            mesh.material.dispose();
                        }
                    } catch (e) { /* ignore */ }
                }
            });
        } catch (e) { /* ignore */ }
        
        this.faceOverlayMeshes.clear();
        
        for (const k of Array.from(this.appliedTextures.keys())) {
            if (k.startsWith('face_')) this.appliedTextures.delete(k);
        }
        
        for (const k of Array.from(this.loadedItems.keys())) {
            if (k.startsWith('face_')) this.loadedItems.delete(k);
        }
    }

    removeTextureFromBodyParts(itemType) {
        if (!this.model) return;
        
        if (itemType === 'face') {
            this.removeFaceOverlay();
            return;
        }

        const bodyPartMapping = {
            'shirt': ['Torso', 'LArm', 'RArm', 'LeftArm', 'RightArm'],
            'tshirt': ['Torso'],
            'pants': ['LLeg', 'RLeg', 'LeftLeg', 'RightLeg']
        };

        const parts = bodyPartMapping[itemType] || [];

        this.model.traverse((object) => {
            if (object.isMesh && parts.some(part => object.name && object.name.includes(part))) {
                if (object.userData && object.userData.originalMaterial) {
                    try {
                        object.material = object.userData.originalMaterial;
                    } catch (e) { /* ignore */ }
                    delete object.userData.hasTextureOverlay;
                }
            }
        });

        for (const key of Array.from(this.appliedTextures.keys())) {
            if (key.startsWith(`${itemType}_`)) this.appliedTextures.delete(key);
        }
    }

    // -------------------------
    // 3D Item Loading
    // -------------------------
    async load3DItem(itemId, itemType, scene, layerIndex = 0) {
        if (!itemId || itemId === 0) return;
        const cacheKey = `${itemType}_${itemId}`;
        const THREE = window.THREE;

        if (this.modelCache.has(cacheKey)) {
            const cachedModel = this.modelCache.get(cacheKey).clone();
            cachedModel.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });
            if (itemType === 'hat' && layerIndex > 0) cachedModel.position.y += layerIndex * 0.05;
            scene.add(cachedModel);

            try { this.loadedItems.set(cacheKey, cachedModel); } catch (e) { /* ignore */ }
            return;
        }

        try {
            const objPath = `${this.config.itemPaths[itemType]}/${itemId}/${itemId}.obj`;
            const texturePath = `${this.config.itemPaths[itemType]}/${itemId}/${itemId}.png`;
            const objModel = await this.loadOBJModel(objPath);

            if (objModel) {
                const texture = await this.loadTexture(texturePath);
                objModel.traverse((child) => {
                    if (child.isMesh) {
                        child.castShadow = true;
                        child.receiveShadow = true;
                        child.material = new THREE.MeshStandardMaterial({
                            map: texture,
                            transparent: true,
                            side: THREE.DoubleSide,
                            roughness: 0.7,
                            metalness: 0.3
                        });
                    }
                });
                if (itemType === 'hat' && layerIndex > 0) objModel.position.y += layerIndex * 0.05;
                this.modelCache.set(cacheKey, objModel.clone());
                scene.add(objModel);

                try { this.loadedItems.set(cacheKey, objModel); } catch (e) { /* ignore */ }
                return;
            }
            await this.loadGLTFItem(itemId, itemType, scene, layerIndex);
        } catch (error) {
            this.createFallback3DItem(itemId, itemType, scene, layerIndex);
        }
    }

    async loadOBJModel(objPath) {
        return new Promise((resolve) => {
            const THREE = window.THREE;
            if (!THREE.OBJLoader) {
                this.loadScript('https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js')
                    .then(() => this.loadOBJFile(objPath, resolve))
                    .catch(() => resolve(null));
                return;
            }
            this.loadOBJFile(objPath, resolve);
        });
    }

    loadOBJFile(objPath, resolve) {
        const THREE = window.THREE;
        const objLoader = new THREE.OBJLoader();
        objLoader.load(objPath, (obj) => resolve(obj), undefined, () => resolve(null));
    }

    async loadGLTFItem(itemId, itemType, scene, layerIndex) {
        const THREE = window.THREE;
        const gltfPath = `${this.config.itemPaths[itemType]}/${itemId}/${itemId}.gltf`;
        if (!THREE.GLTFLoader) return;
        
        return new Promise((resolve, reject) => {
            const loader = new THREE.GLTFLoader();
            loader.load(gltfPath, (gltf) => {
                const itemModel = gltf.scene;
                itemModel.traverse((child) => {
                    if (child.isMesh) {
                        child.castShadow = true;
                        child.receiveShadow = true;
                        child.material = new THREE.MeshStandardMaterial({ 
                            color: 0xFFFFFF, 
                            roughness: 0.7, 
                            metalness: 0.3 
                        });
                    }
                });
                if (itemType === 'hat' && layerIndex > 0) itemModel.position.y += layerIndex * 0.05;
                this.modelCache.set(`${itemType}_${itemId}`, itemModel.clone());
                scene.add(itemModel);

                try { this.loadedItems.set(`${itemType}_${itemId}`, itemModel); } catch (e) { /* ignore */ }
                resolve();
            }, undefined, (err) => reject(err));
        });
    }

    async loadTexture(texturePath) {
        return new Promise((resolve) => {
            const THREE = window.THREE;
            const textureLoader = new THREE.TextureLoader();
            textureLoader.load(texturePath, (texture) => {
                texture.encoding = THREE.sRGBEncoding;
                texture.flipY = false;
                resolve(texture);
            }, undefined, () => {
                const canvas = document.createElement('canvas');
                canvas.width = 64;
                canvas.height = 64;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, 64, 64);
                const texture = new THREE.CanvasTexture(canvas);
                texture.encoding = THREE.sRGBEncoding;
                resolve(texture);
            });
        });
    }

    createFallback3DItem(itemId, itemType, scene, layerIndex) {
        const THREE = window.THREE;
        if (!THREE) return;
        
        let geometry, color = 0x888888, positionY = 0, mesh;
        switch(itemType) {
            case 'hat': 
                geometry = new THREE.ConeGeometry(0.4, 0.4, 8); 
                color = 0xFF9900; 
                positionY = 1.8 + (layerIndex * 0.1); 
                break;
            case 'gear': 
                geometry = new THREE.BoxGeometry(0.3, 0.3, 0.3); 
                color = 0x00AAFF; 
                positionY = 1.5; 
                break;
            case 'accessory': 
                geometry = new THREE.SphereGeometry(0.2, 8, 8); 
                color = 0xFF00FF; 
                positionY = 1.6; 
                break;
            default: return;
        }
        
        const material = new THREE.MeshStandardMaterial({ 
            color: color, 
            roughness: 0.7, 
            metalness: 0.3 
        });
        mesh = new THREE.Mesh(geometry, material);
        mesh.position.y = positionY; 
        mesh.castShadow = true; 
        mesh.receiveShadow = true;
        scene.add(mesh);

        try { this.loadedItems.set(`${itemType}_${itemId}`, mesh); } catch (e) { /* ignore */ }
    }

    // -------------------------
    // Camera & Focus
    // -------------------------
    adjustCamera(scene, camera) {
        const THREE = window.THREE;

        let cameraHeight = 3.8;
        let cameraDistance = 4.2;
        let lookAtHeight = 4.4;
        let sideOffset = 0.6;
        let fov = 35;

        if (this.renderType === 'item') {
            const type = this.itemTypeFocus;
            if (type === 'hat' || type === 'face' || type === 'head' || type === '1' || type === '2' || type === '7') {
                cameraHeight = 4.2;
                lookAtHeight = 4.6;
                cameraDistance = 3.2;
                fov = 30;
            } else if (type === 'pants' || type === '4') {
                cameraHeight = 1.8;
                lookAtHeight = 1.4;
                cameraDistance = 5.0;
                fov = 40;
            } else if (type === 'shirt' || type === 'tshirt' || type === '3' || type === '8') {
                cameraHeight = 3.0;
                lookAtHeight = 3.0;
                cameraDistance = 4.5;
                fov = 38;
            } else if (type === 'gear' || type === '5' || type === 'accessory' || type === '6') {
                cameraHeight = 3.2;
                lookAtHeight = 2.8;
                cameraDistance = 5.2;
                sideOffset = 1.0;
                fov = 42;
            } else {
                cameraHeight = 3.5;
                lookAtHeight = 3.0;
                cameraDistance = 5.0;
                fov = 40;
            }
        }

        camera.fov = fov;
        camera.updateProjectionMatrix();

        if (this.renderType === 'item') {
            const focusObject = this.findFocusObjectInScene(scene);
            if (focusObject) {
                const offset = this.getCameraOffsetForCategory(this.itemTypeFocus);
                const preferredDir = this.getPreferredDirectionForCategory(this.itemTypeFocus);

                if (this.focusCameraOnObject(camera, focusObject, offset, preferredDir)) {
                    return;
                }
            }
        }

        camera.position.set(sideOffset, cameraHeight, cameraDistance);
        camera.lookAt(0, lookAtHeight, 0);
        camera.updateProjectionMatrix();
    }

    focusCameraOnObject(camera, object, offsetMultiplier = 1.4, preferredDirection = null) {
        try {
            const THREE = window.THREE;
            if (!THREE || !object) return false;

            const box = new THREE.Box3().setFromObject(object);
            if (!box || box.isEmpty()) return false;

            const sphere = box.getBoundingSphere(new THREE.Sphere());
            const radius = sphere.radius;
            const safeRadius = Math.max(radius, 0.05);
            const fov = (camera.fov || this.config.camera.fov) * (Math.PI / 180);
            let distance = safeRadius / Math.sin(fov / 2);
            distance *= offsetMultiplier;

            let dir = new THREE.Vector3(0.0, 0.15, 1.0).normalize();
            if (preferredDirection && preferredDirection.isVector3) {
                dir = preferredDirection.clone().normalize();
            } else if (preferredDirection && Array.isArray(preferredDirection) && preferredDirection.length === 3) {
                dir = new THREE.Vector3(preferredDirection[0], preferredDirection[1], preferredDirection[2]).normalize();
            } else {
                const pref = this.getPreferredDirectionForCategory(this.itemTypeFocus);
                if (pref) dir = pref;
            }

            const cameraPos = sphere.center.clone().add(dir.multiplyScalar(distance));
            camera.position.copy(cameraPos);
            camera.near = Math.max(0.01, distance * 0.01);
            camera.far = Math.max(100, distance * 200);
            camera.updateProjectionMatrix();
            camera.lookAt(sphere.center);

            return true;
        } catch (e) {
            console.warn('focusCameraOnObject error:', e);
            return false;
        }
    }

    findFocusObjectInScene(scene) {
        const prefer = (this.itemTypeFocus || '').toString().toLowerCase();

        if (prefer) {
            for (const [key, obj] of this.loadedItems) {
                if (key.toLowerCase().includes(prefer)) return obj;
            }
            if (this.loadedItems.has(prefer)) return this.loadedItems.get(prefer);
        }

        const priority = [
            `gear_${this.equippedItems.gear || ''}`,
            `accessory_${this.equippedItems.accessory || ''}`
        ];

        if (Array.isArray(this.equippedItems.hats) && this.equippedItems.hats.length > 0) {
            priority.push(`hat_${this.equippedItems.hats[0]}`);
        }

        if (this.equippedItems.face && this.equippedItems.face !== 'default') {
            priority.push(`face_${this.equippedItems.face}`);
        }

        for (const key of priority) {
            if (this.loadedItems.has(key)) {
                return this.loadedItems.get(key);
            }
        }

        if (this.model) {
            let headMesh = null;
            this.model.traverse((child) => {
                if (!headMesh && child.isMesh && child.name.toLowerCase().includes('head')) {
                    headMesh = child;
                }
            });
            if (headMesh) return headMesh;
        }

        return null;
    }

    getCameraOffsetForCategory(category) {
        switch ((category || '').toString().toLowerCase()) {
            case 'gear':
            case '5': return 1.9;
            case 'hat':
            case '1': return 1.4;
            case 'accessory':
            case '6': return 1.8;
            case 'head':
            case '7': return 1.3;
            case 'pants':
            case '4': return 1.7;
            case 'shirt':
            case '3': return 1.6;
            default: return 1.4;
        }
    }

    getPreferredDirectionForCategory(category) {
        const THREE = window.THREE;
        switch ((category || '').toString().toLowerCase()) {
            case 'hat':
            case '1':
            case 'head':
            case '7':
            case 'face':
                return new THREE.Vector3(0, 0.15, 1).normalize();
            case 'gear':
            case '5':
                return new THREE.Vector3(1, 0.2, 0.8).normalize();
            case 'accessory':
            case '6':
                return new THREE.Vector3(0.6, 0.1, 1).normalize();
            case 'pants':
            case '4':
                return new THREE.Vector3(0, 0.2, 1).normalize();
            case 'shirt':
            case 'tshirt':
            case '3':
            case '8':
                return new THREE.Vector3(0.2, 0.2, 1).normalize();
            default:
                return new THREE.Vector3(0, 0.15, 1).normalize();
        }
    }

    // -------------------------
    // Utility Methods
    // -------------------------
    async loadThreeJS() {
        if (window.THREE) return;
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
            script.onload = () => { window.THREE_LOADED = true; resolve(); };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async loadScript(src) {
        return new Promise((resolve) => {
            if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = resolve;
            document.head.appendChild(script);
        });
    }

    cleanupThreeJSScene(scene, renderer) {
        try {
            if (renderer) renderer.dispose();
        } catch (e) { /* ignore */ }
        
        scene.traverse((object) => {
            try {
                if (object.geometry) object.geometry.dispose();
                if (object.material) {
                    if (object.material.map) {
                        const isCached = Array.from(this.textureCache.values()).some(
                            t => t && t.image && object.material.map && t.image === object.material.map.image
                        );
                        if (!isCached) object.material.map.dispose();
                    }
                    if (Array.isArray(object.material)) {
                        object.material.forEach(m => m.dispose());
                    } else {
                        object.material.dispose();
                    }
                }
            } catch (e) { /* ignore */ }
        });
    }

    // -------------------------
    // 2D Canvas Rendering
    // -------------------------
    drawImageToCanvas(imageData) {
        const img = new Image();
        img.onload = () => {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.drawBackground();
            this.ctx.save();
            const scale = Math.min(this.canvas.width / img.width, this.canvas.height / img.height) * 0.9;
            const x = (this.canvas.width - img.width * scale) / 2;
            const y = (this.canvas.height - img.height * scale) / 2;
            this.ctx.imageSmoothingEnabled = true;
            this.ctx.imageSmoothingQuality = 'high';
            this.ctx.drawImage(img, x, y, img.width * scale, img.height * scale);
            this.ctx.restore();
            this.applyCanvasStyling();
        };
        img.src = imageData;
    }

    drawBackground() {
        const width = this.canvas.width;
        const height = this.canvas.height;
        const gradient = this.ctx.createRadialGradient(
            width / 2, height / 2, 0,
            width / 2, height / 2, Math.max(width, height) / 2
        );
        gradient.addColorStop(0, '#2a2a4e');
        gradient.addColorStop(1, '#16213e');
        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(0, 0, width, height);
    }

    applyCanvasStyling() {
        if (this.canvas.classList.contains('avatar-user') || this.canvas.classList.contains('avatar-friend')) {
            this.applyCircularMask();
        }
        this.drawOutline();
    }

    applyCircularMask() {
        const width = this.canvas.width;
        const height = this.canvas.height;
        const radius = Math.min(width, height) / 2 - 2;
        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.arc(width / 2, height / 2, radius, 0, Math.PI * 2);
        this.ctx.clip();
        const imgData = this.ctx.getImageData(0, 0, width, height);
        this.ctx.clearRect(0, 0, width, height);
        this.ctx.putImageData(imgData, 0, 0);
        this.ctx.restore();
    }

    drawOutline() {
        const width = this.canvas.width;
        const height = this.canvas.height;
        this.ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
        this.ctx.lineWidth = 2;
        if (this.canvas.classList.contains('avatar-user') || this.canvas.classList.contains('avatar-friend')) {
            this.ctx.beginPath();
            this.ctx.arc(width / 2, height / 2, Math.min(width, height) / 2 - 1, 0, Math.PI * 2);
            this.ctx.stroke();
        } else {
            this.ctx.strokeRect(1, 1, width - 2, height - 2);
        }
    }

    async renderSimple2DFallback() {
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            canvas.width = 400;
            canvas.height = 400;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#16213e';
            ctx.fillRect(0, 0, 400, 400);
            ctx.fillStyle = '#6c5ce7';
            ctx.beginPath();
            ctx.arc(200, 150, 80, 0, Math.PI * 2);
            ctx.fill();
            ctx.fillRect(140, 240, 120, 160);
            resolve(canvas.toDataURL('image/png'));
        });
    }

    drawFallback() {
        this.drawBackground();
        this.ctx.fillStyle = '#6c5ce7';
        this.ctx.beginPath();
        this.ctx.arc(this.canvas.width / 2, this.canvas.height / 2 - 40, 30, 0, Math.PI * 2);
        this.ctx.fill();
        this.ctx.fillRect(this.canvas.width / 2 - 20, this.canvas.height / 2, 40, 60);
    }

    removeItem(itemType, itemId) {
        try {
            const key = `${itemType}_${itemId}`;

            if (this.loadedItems.has(key)) {
                const obj = this.loadedItems.get(key);
                if (obj && obj.parent) obj.parent.remove(obj);
                if (obj) {
                    obj.traverse((child) => {
                        if (child.isMesh) {
                            try {
                                if (child.geometry) child.geometry.dispose();
                                if (child.material) {
                                    if (child.material.map) {
                                        const isCached = Array.from(this.textureCache.values()).some(
                                            t => t && t.image && child.material.map && t.image === child.material.map.image
                                        );
                                        if (!isCached) child.material.map.dispose();
                                    }
                                    child.material.dispose();
                                }
                            } catch (e) { /* ignore */ }
                        }
                    });
                }
                this.loadedItems.delete(key);
                console.log(`✅ Removed 3D ${itemType} item ${itemId}`);
            }

            if (this.appliedTextures.has(key)) {
                this.removeTextureFromBodyParts(itemType);
                this.appliedTextures.delete(key);
                console.log(`✅ Removed ${itemType} texture ${itemId}`);
            }

            if (itemType === 'face' && this.faceOverlayMeshes.has(`face_${itemId}`)) {
                const overlayMesh = this.faceOverlayMeshes.get(`face_${itemId}`);
                if (overlayMesh && overlayMesh.parent) overlayMesh.parent.remove(overlayMesh);
                try {
                    if (overlayMesh.geometry) overlayMesh.geometry.dispose();
                    if (overlayMesh.material) {
                        if (overlayMesh.material.map) {
                            const isCached = Array.from(this.textureCache.values()).some(
                                t => t && t.image && overlayMesh.material.map && t.image === overlayMesh.material.map.image
                            );
                            if (!isCached) overlayMesh.material.map.dispose();
                        }
                        overlayMesh.material.dispose();
                    }
                } catch (e) { /* ignore */ }
                
                this.faceOverlayMeshes.delete(`face_${itemId}`);
                this.appliedTextures.delete(`face_${itemId}`);
                this.loadedItems.delete(`face_${itemId}`);
                console.log(`✅ Removed face overlay mesh ${itemId}`);
            }
        } catch (e) {
            console.warn('removeItem error:', e);
        }
    }
}

// ============================================================================
// INITIALIZATION
// ============================================================================

function initializeDashboardAvatars3D() {
    console.log('🚀 Initializing 3D avatar renderer...');
    const selector = 'canvas[data-user-id]';

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const canvas = entry.target;
                    if (!canvas.dataset.rendered) {
                        canvas.dataset.rendered = 'true';
                        new DashboardAvatarRenderer3DTo2D(canvas);
                    }
                }
            });
        }, { rootMargin: '100px', threshold: 0.01 });

        document.querySelectorAll(selector).forEach(canvas => observer.observe(canvas));
    } else {
        document.querySelectorAll(selector).forEach(canvas => {
            if (!canvas.dataset.rendered) {
                canvas.dataset.rendered = 'true';
                new DashboardAvatarRenderer3DTo2D(canvas);
            }
        });
    }
}

function loadThreeJSOnce() {
    return new Promise((resolve) => {
        if (window.THREE) {
            resolve();
            return;
        }
        
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
        script.onload = () => {
            Promise.all([
                loadScriptOnce('https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js'),
                loadScriptOnce('https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js')
            ]).then(resolve).catch(resolve);
        };
        script.onerror = resolve;
        document.head.appendChild(script);
    });
}

function loadScriptOnce(src) {
    return new Promise((resolve) => {
        if (document.querySelector(`script[src="${src}"]`)) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = resolve;
        document.head.appendChild(script);
    });
}

// Initialize when ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        loadThreeJSOnce().then(() => setTimeout(initializeDashboardAvatars3D, 500));
    });
} else {
    loadThreeJSOnce().then(() => setTimeout(initializeDashboardAvatars3D, 500));
}

// Fallback initialization after 2 seconds
setTimeout(initializeDashboardAvatars3D, 2000);