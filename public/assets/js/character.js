document.addEventListener('DOMContentLoaded', () => {
    const tooltip = document.createElement('div');
    tooltip.className = 'item-tooltip';
    tooltip.style.display = 'none';
    tooltip.style.opacity = '0';
    tooltip.style.visibility = 'hidden';
    document.body.appendChild(tooltip);
    
    console.log('Tooltip element created:', tooltip);
    console.log('Tooltip element in DOM:', document.querySelector('.item-tooltip'));
       
    // Use event delegation for better performance and dynamic content handling
    document.addEventListener('mouseenter', (e) => {
        const slot = e.target.closest('.slot.has-item');
        if (slot) {
            console.log('Mouse enter on slot:', slot);
            showTooltip(e, slot);
        }
    }, true);

    document.addEventListener('mousemove', (e) => {
        if (tooltip.style.display === 'block') {
            updateTooltipPosition(e);
        }
    });

    document.addEventListener('mouseleave', (e) => {
        const slot = e.target.closest('.slot.has-item');
        if (slot) {
            console.log('Mouse leave from slot:', slot);
            hideTooltip();
        }
    }, true);
    
    // Prevent tooltip from hiding when hovering over it
    tooltip.addEventListener('mouseenter', () => {
        console.log('Mouse enter on tooltip');
        cancelHideTooltip();
    });
    
    tooltip.addEventListener('mouseleave', () => {
        console.log('Mouse leave from tooltip');
        hideTooltip();
    });

    // Touch events for mobile devices
    document.addEventListener('touchstart', (e) => {
        const slot = e.target.closest('.slot.has-item');
        if (slot) {
            e.preventDefault();
            showTooltip(e, slot);
            setTimeout(hideTooltip, 3000);
        }
    });

    document.addEventListener('touchmove', (e) => {
        if (tooltip.style.display === 'block') {
            updateTooltipPosition(e);
        }
    });

    function showTooltip(e, slot) {
        console.log('Show tooltip called for slot:', slot);
        cancelHideTooltip(); // Cancel any pending hide
        
        const itemData = slot.dataset.item;
        console.log('Item data:', itemData);
        
        if (itemData) {
            try {
                const item = JSON.parse(itemData);
                console.log('Parsed item:', item);
                const tooltipContent = generateTooltipHTML(item);
                console.log('Generated tooltip content:', tooltipContent);
                
                tooltip.innerHTML = tooltipContent;
                tooltip.style.display = 'block';
                tooltip.style.visibility = 'visible';
                tooltip.style.opacity = '1';
                
                console.log('Tooltip styles after setting:', {
                    display: tooltip.style.display,
                    visibility: tooltip.style.visibility,
                    opacity: tooltip.style.opacity
                });
                
                updateTooltipPosition(e);
                console.log('Tooltip should be visible now');
            } catch (error) {
                console.error('Error parsing item data:', error);
            }
        } else {
            console.log('No item data found');
            console.log('Slot dataset:', slot.dataset);
        }
    }

    let hideTimeout;
    
    function hideTooltip() {
        console.log('Hide tooltip called');
        clearTimeout(hideTimeout);
        hideTimeout = setTimeout(() => {
            tooltip.style.opacity = '0';
            tooltip.style.visibility = 'hidden';
            // Hide after transition completes
            setTimeout(() => {
                tooltip.style.display = 'none';
            }, 200);
        }, 100); // Small delay to prevent flickering
    }
    
    function cancelHideTooltip() {
        clearTimeout(hideTimeout);
    }

    function generateTooltipHTML(item) {
        console.log('generateTooltipHTML called with item:', item);
        
        const qualityColors = {
            0: '#9d9d9d', // Poor (Grey)
            1: '#ffffff', // Common (White)
            2: '#1eff00', // Uncommon (Green)
            3: '#0070dd', // Rare (Blue)
            4: '#a335ee', // Epic (Purple)
            5: '#ff8000', // Legendary (Orange)
            6: '#e6cc80'  // Artifact (Gold)
        };

        const itemColor = qualityColors[item.Quality] || '#ffffff';
        const name = item.name || 'Unknown Item';
        
        console.log('Item name:', name, 'Color:', itemColor);
        const level = item.ItemLevel || 0;
        const reqLevel = item.RequiredLevel || 0;
        const sell = item.SellPrice || 0;
        const dur = item.MaxDurability || 0;
        const speed = (item.class == 2 && item.delay > 0) ? (item.delay / 1000).toFixed(1) : null;
        const bonding = getBondingType(item.bonding);
        const className = getClassName(item.class);
        const subclassName = getSubClassName(item.class, item.subclass);
        const invType = getInventoryType(item.InventoryType);

        let html = `<div class="item-tooltip-content">
            <div class="tooltip-header">
                <div>
                    <div class="item-name" style="color: ${itemColor} !important;">${name}</div>
                    ${level ? `<div class="item-level">Item Level ${level}</div>` : ''}
                </div>
                <div class="tooltip-right">
                    <div>${subclassName || ''}</div>
                    ${speed ? `<div>Speed ${speed}</div>` : ''}
                </div>
            </div>`;

        if (bonding) html += `<div>${bonding}</div>`;
        if (invType) html += `<div>${invType}</div>`;
        if (className) html += `<div>${className}</div>`;

        // Damage for weapons
        if (item.dmg_min1 > 0 && item.dmg_max1 > 0) {
            const min = item.dmg_min1;
            const max = item.dmg_max1;
            const dps = item.delay > 0 ? ((min + max) / 2 / (item.delay / 1000)).toFixed(1) : '';
            html += `<div>${min} - ${max} Damage</div>`;
            if (dps) html += `<div class="dps-text">(${dps} damage per second)</div>`;
        }

        // Armor
        if (item.armor > 0) html += `<div>+${item.armor} Armor</div>`;

        // Stats
        for (let i = 1; i <= 10; i++) {
            const statType = item[`stat_type${i}`] || 0;
            const statValue = item[`stat_value${i}`] || 0;
            console.log(`Stat ${i}: type=${statType}, value=${statValue}`);
            if (statType > 0 && statValue != 0 && !isNaN(statValue)) {
                const statName = getStatName(statType);
                if (statName) {
                    const sign = statValue > 0 ? '+' : '';
                    html += `<div class="normal-stat">${sign}${statValue} ${statName}</div>`;
                }
            }
        }

        // Resistances
        const resistances = {
            'Holy': item.holy_res || 0,
            'Fire': item.fire_res || 0,
            'Nature': item.nature_res || 0,
            'Frost': item.frost_res || 0,
            'Shadow': item.shadow_res || 0,
            'Arcane': item.arcane_res || 0
        };
        
        for (const [school, val] of Object.entries(resistances)) {
            if (val > 0) {
                html += `<div class="resistance-stat">+${val} ${school} Resistance</div>`;
            }
        }

        // Sockets
        html += '<div class="sockets-container">';
        for (let i = 1; i <= 3; i++) {
            const colorCode = item[`socketColor_${i}`];
            if (colorCode) {
                const socketData = getSocketData(colorCode);
                if (socketData) {
                    html += `<div class="socket-item">
                        <img src="${socketData.icon}" alt="${socketData.name} socket" class="socket-icon">
                        <span class="socket-name" style="color: ${socketData.name.toLowerCase()};">${socketData.name}</span>
                    </div>`;
                }
            }
        }
        html += '</div>';

        if (item.socketBonus) {
            html += `<div class="socket-bonus">Socket Bonus: Spell ID ${item.socketBonus}</div>`;
        }

        if (dur > 0) html += `<div>Durability ${dur}/${dur}</div>`;
        if (reqLevel) html += `<div>Requires Level ${reqLevel}</div>`;

        // Class restrictions
        if (item.AllowableClass && item.AllowableClass > 0) {
            const classes = getClassRestrictions(item.AllowableClass);
            if (classes.length > 0) {
                html += `<div>Classes: ${classes.join(', ')}</div>`;
            }
        }

        // Special stats
        for (let i = 1; i <= 10; i++) {
            const statType = item[`stat_type${i}`] || 0;
            const statValue = item[`stat_value${i}`] || 0;
            if (statType > 0 && statValue != 0) {
                const statName = getSpecialStatName(statType);
                if (statName) {
                    html += `<div class="special-stat">Equip: Increases +${statValue} ${statName}</div>`;
                }
            }
        }

        // Sell price
        if (sell > 0) {
            const gold = Math.floor(sell / 10000);
            const silver = Math.floor((sell % 10000) / 100);
            const copper = sell % 100;
            html += `<div>Sell: ${gold} <span style='color:#ffd700;'>g</span> ${silver} <span style='color:#c0c0c0;'>s</span> ${copper} <span style='color:#b87333;'>c</span></div>`;
        }

        // Spell Effects
        if (item.spellEffects && item.spellEffects.length > 0) {
            html += '<div class="spells-container">';
            item.spellEffects.forEach(effect => {
                html += `<div class="spell-effect" style="color: #ffd700;">${effect}</div>`;
            });
            html += '</div>';
        }

        // Description
        if (item.description) {
            html += `<div class="item-description">${item.description}</div>`;
        }

        html += '</div>';
        console.log('Generated HTML:', html);
        return html;
    }

    function updateTooltipPosition(e) {
        const tooltip = document.querySelector('.item-tooltip');
        const x = (e.clientX || (e.touches && e.touches[0].clientX)) + 10;
        const y = (e.clientY || (e.touches && e.touches[0].clientY)) + 10;
        tooltip.style.left = `${x}px`;
        tooltip.style.top = `${y}px`;

        const rect = tooltip.getBoundingClientRect();
        if (rect.right > window.innerWidth) {
            tooltip.style.left = `${window.innerWidth - rect.width - 10}px`;
        }
        if (rect.bottom > window.innerHeight) {
            tooltip.style.top = `${window.innerHeight - rect.height - 10}px`;
        }
    }

    // Tab navigation
    const tabs = document.querySelectorAll('.tab-nav button');
    const tabContents = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });

    // Debug: Check if slots with items exist
    const slotsWithItems = document.querySelectorAll('.slot.has-item');
    console.log('Found slots with items:', slotsWithItems.length);
    slotsWithItems.forEach((slot, index) => {
        console.log(`Slot ${index}:`, slot);
        console.log(`Slot ${index} dataset:`, slot.dataset);
    });

    // Load character model if model path is available
    const modelPathElement = document.querySelector('[data-model-path]');
    if (modelPathElement) {
        const modelPath = modelPathElement.dataset.modelPath;
        if (modelPath) {
            loadCharacterModel(modelPath);
        }
    }

    // Helper functions for tooltip generation
    function getBondingType(bonding) {
        const types = {
            0: null,
            1: 'Binds when picked up',
            2: 'Binds when equipped',
            3: 'Binds when used',
            4: 'Quest Item',
            5: 'Quest Item',
            6: 'Binds to account'
        };
        return types[bonding] || null;
    }

    function getClassName(classId) {
        const classes = {
            0: 'Consumable', 1: 'Container', 2: 'Weapon', 3: 'Gem', 4: 'Armor',
            5: 'Reagent', 6: 'Projectile', 7: 'Trade Goods', 8: 'Generic', 9: 'Recipe',
            10: 'Money', 11: 'Quiver', 12: 'Quest', 13: 'Key', 14: 'Permanent',
            15: 'Miscellaneous', 16: 'Glyph'
        };
        return classes[classId] || 'Unknown';
    }

    function getSubClassName(classId, subclassId) {
        const subclasses = {
            2: {
                0: 'Axe', 1: 'Axe (2H)', 2: 'Bow', 3: 'Gun', 4: 'Mace', 5: 'Mace (2H)',
                6: 'Polearm', 7: 'Sword', 8: 'Sword (2H)', 10: 'Staff', 13: 'Fist Weapon',
                14: 'Miscellaneous', 15: 'Dagger', 16: 'Thrown', 17: 'Spear',
                18: 'Crossbow', 19: 'Wand', 20: 'Fishing Pole'
            },
            4: {
                0: 'Miscellaneous', 1: 'Cloth', 2: 'Leather', 3: 'Mail', 4: 'Plate',
                6: 'Shield', 7: 'Libram', 8: 'Idol', 9: 'Totem', 10: 'Sigil'
            }
        };
        return subclasses[classId]?.[subclassId] || null;
    }

    function getInventoryType(invType) {
        const types = {
            0: null, 1: 'Head', 2: 'Neck', 3: 'Shoulder', 4: 'Shirt', 5: 'Chest',
            6: 'Waist', 7: 'Legs', 8: 'Feet', 9: 'Wrist', 10: 'Hands',
            11: 'Finger', 12: 'Trinket', 13: 'One-Hand', 14: 'Shield',
            15: 'Ranged', 16: 'Back', 17: 'Two-Hand', 18: 'Bag', 19: 'Tabard',
            20: 'Robe', 21: 'Main Hand', 22: 'Off Hand', 23: 'Holdable',
            25: 'Thrown', 26: 'Ranged', 28: 'Relic'
        };
        return types[invType] || null;
    }

    function getStatName(statType) {
        const stats = {
            0: "Mana", 1: "Health", 3: "Agility", 4: "Strength", 5: "Intellect", 6: "Spirit", 7: "Stamina"
        };
        return stats[statType] || null;
    }

    function getSpecialStatName(statType) {
        const stats = {
            12: "Defense Rating", 13: "Dodge Rating", 14: "Parry Rating", 15: "Block Rating",
            16: "Hit (Melee) Rating", 17: "Hit (Ranged) Rating", 18: "Hit (Spell) Rating",
            19: "Crit (Melee) Rating", 20: "Crit (Ranged) Rating", 21: "Crit (Spell) Rating",
            22: "Hit Taken (Melee) Rating", 23: "Hit Taken (Ranged) Rating", 24: "Hit Taken (Spell) Rating",
            25: "Crit Taken (Melee) Rating", 26: "Crit Taken (Ranged) Rating", 27: "Crit Taken (Spell) Rating",
            28: "Haste (Melee) Rating", 29: "Haste (Ranged) Rating", 30: "Haste (Spell) Rating",
            31: "Hit Rating", 32: "Crit Rating", 33: "Hit Taken Rating", 34: "Crit Taken Rating",
            35: "Resilience Rating", 36: "Haste Rating", 37: "Expertise Rating", 38: "Attack Power",
            39: "Ranged Attack Power", 40: "Feral Attack Power", 41: "Healing Power", 42: "Spell Damage",
            43: "Mana Regen", 44: "Armor Penetration Rating", 45: "Spell Power", 46: "Health Regen",
            47: "Spell Penetration", 48: "Block Value"
        };
        return stats[statType] || null;
    }

    function getSocketData(colorCode) {
        const sockets = {
            1: { name: 'Meta', icon: '/img/shopimg/items/socketicons/socket_meta.gif' },
            2: { name: 'Red', icon: '/img/shopimg/items/socketicons/socket_red.gif' },
            4: { name: 'Yellow', icon: '/img/shopimg/items/socketicons/socket_yellow.gif' },
            8: { name: 'Blue', icon: '/img/shopimg/items/socketicons/socket_blue.gif' }
        };
        return sockets[colorCode] || null;
    }

    function getClassRestrictions(allowableClass) {
        const classes = {
            1: 'Warrior', 2: 'Paladin', 4: 'Hunter', 8: 'Rogue', 16: 'Priest',
            32: 'Death Knight', 64: 'Shaman', 128: 'Mage', 256: 'Warlock', 1024: 'Druid'
        };
        const classColors = {
            1: '#C69B6D', 2: '#F48CBA', 4: '#AAD372', 8: '#FFF468', 16: '#FFFFFF',
            32: '#C41E3A', 64: '#0070DD', 128: '#3FC7EB', 256: '#8788EE', 1024: '#FF7C0A'
        };
        
        const result = [];
        for (const [bit, className] of Object.entries(classes)) {
            if (allowableClass & parseInt(bit)) {
                const color = classColors[bit] || '#ffffff';
                result.push(`<span style='color:${color};'>${className}</span>`);
            }
        }
        return result;
    }

    function getSpellTriggerText(trigger) {
        const triggers = {
            0: 'Use:',
            1: 'Equip:',
            2: 'Chance on hit:',
            3: 'Chance on spell hit:',
            4: 'Chance on crit:',
            5: 'Chance on block:',
            6: 'Chance on parry:',
            7: 'Chance on dodge:',
            8: 'Chance on resist:',
            9: 'Chance on absorb:',
            10: 'Chance on reflect:',
            11: 'Chance on interrupt:',
            12: 'Chance on kill:',
            13: 'Chance on death:',
            14: 'Chance on miss:',
            15: 'Chance on spell miss:',
            16: 'Chance on spell crit:',
            17: 'Chance on spell block:',
            18: 'Chance on spell parry:',
            19: 'Chance on spell dodge:',
            20: 'Chance on spell resist:',
            21: 'Chance on spell absorb:',
            22: 'Chance on spell reflect:',
            23: 'Chance on spell interrupt:',
            24: 'Chance on spell kill:',
            25: 'Chance on spell death:',
            26: 'Chance on spell miss:'
        };
        return triggers[trigger] || 'Use:';
    }
});

// Three.js 3D character model loader
function loadCharacterModel(modelPath) {
    const container = document.querySelector('.character-image');
    const defaultImage = container.querySelector('.default-image');
    
    import('https://esm.sh/three@0.167.1')
        .then(THREE => {
            return Promise.all([
                import('https://esm.sh/three@0.167.1/examples/jsm/controls/OrbitControls.js'),
                import('https://esm.sh/three@0.167.1/examples/jsm/loaders/GLTFLoader.js'),
                THREE
            ]);
        })
        .then(([OrbitControlsModule, GLTFLoaderModule, THREE]) => {
            const OrbitControls = OrbitControlsModule.OrbitControls;
            const GLTFLoader = GLTFLoaderModule.GLTFLoader;

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
            scene.add(ambientLight);
            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.position.set(5, 5, 5);
            scene.add(directionalLight);

            const controls = new OrbitControls(camera, renderer.domElement);

            const loader = new GLTFLoader();
            loader.load(modelPath, (gltf) => {
                console.log('Model loaded successfully:', gltf);
                const model = gltf.scene;
                scene.add(model);

                // Hide default image on successful model load
                defaultImage.style.display = 'none';

                model.traverse((child) => {
                    if (child.isMesh && child.material && child.material.map) {
                        console.log('Mesh texture:', child.material.map.name || 'Unnamed texture');
                    } else if (child.isMesh) {
                        console.log('Mesh missing texture:', child.name);
                    }
                });

                const box = new THREE.Box3().setFromObject(model);
                const center = box.getCenter(new THREE.Vector3());
                const size = box.getSize(new THREE.Vector3());
                const initialDistance = size.z * 0.8;
                camera.position.set(center.x + size.x, center.y + size.y / 2, center.z + size.z * 2);
                camera.lookAt(center);
                controls.target = center;
                controls.minDistance = initialDistance * 0.5;
                controls.maxDistance = initialDistance * 2.0;

                if (gltf.animations && gltf.animations.length > 0) {
                    const mixer = new THREE.AnimationMixer(model);
                    const action = mixer.clipAction(gltf.animations[0]);
                    action.play();
                    console.log('Available animations:', gltf.animations);
                    const clock = new THREE.Clock();
                    function updateAnimations() {
                        const delta = clock.getDelta();
                        mixer.update(delta);
                    }
                    scene.userData.mixer = mixer;
                    scene.userData.updateAnimations = updateAnimations;
                }
            }, (progress) => {
                console.log(`Loading: ${progress.loaded / progress.total * 100}%`);
            }, (error) => {
                console.error('Error loading model:', error);
                // Default image remains visible if model fails to load
            });

            function animate() {
                requestAnimationFrame(animate);
                controls.update();
                if (scene.userData.mixer) {
                    scene.userData.updateAnimations();
                }
                renderer.render(scene, camera);
            }
            animate();

            window.addEventListener('resize', () => {
                const width = container.clientWidth;
                const height = container.clientHeight;
                camera.aspect = width / height;
                camera.updateProjectionMatrix();
                renderer.setSize(width, height);
            });
        })
        .catch(error => {
            console.error('Error loading Three.js modules:', error);
        });
}
