<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemTooltipService
{
    protected $qualityColors = [
        0 => '#9d9d9d', // Poor (Grey)
        1 => '#ffffff', // Common (White)
        2 => '#1eff00', // Uncommon (Green)
        3 => '#0070dd', // Rare (Blue)
        4 => '#a335ee', // Epic (Purple)
        5 => '#ff8000', // Legendary (Orange)
        6 => '#e6cc80', // Artifact (Red)
        7 => '#e6cc80'  // Bind to Account (Gold)
    ];

    protected $bondingTypes = [
        0 => null,
        1 => 'Binds when picked up',
        2 => 'Binds when equipped',
        3 => 'Binds when used',
        4 => 'Quest Item',
        5 => 'Quest Item',
        6 => 'Binds to account'
    ];

    protected $inventoryTypes = [
        0 => null,
        1 => 'Head', 2 => 'Neck', 3 => 'Shoulder', 4 => 'Shirt', 5 => 'Chest',
        6 => 'Waist', 7 => 'Legs', 8 => 'Feet', 9 => 'Wrist', 10 => 'Hands',
        11 => 'Finger', 12 => 'Trinket', 13 => 'One-Hand', 14 => 'Shield',
        15 => 'Ranged', 16 => 'Back', 17 => 'Two-Hand', 18 => 'Bag', 19 => 'Tabard',
        20 => 'Robe', 21 => 'Main Hand', 22 => 'Off Hand', 23 => 'Holdable',
        25 => 'Thrown', 26 => 'Ranged', 28 => 'Relic'
    ];

    protected $classNames = [
        0 => 'Consumable', 1 => 'Container', 2 => 'Weapon', 3 => 'Gem', 4 => 'Armor',
        5 => 'Reagent', 6 => 'Projectile', 7 => 'Trade Goods', 8 => 'Generic', 9 => 'Recipe',
        10 => 'Money', 11 => 'Quiver', 12 => 'Quest', 13 => 'Key', 14 => 'Permanent',
        15 => 'Miscellaneous', 16 => 'Glyph'
    ];

    protected $subclassNames = [
        2 => [
            0 => 'Axe', 1 => 'Axe (2H)', 2 => 'Bow', 3 => 'Gun', 4 => 'Mace', 5 => 'Mace (2H)',
            6 => 'Polearm', 7 => 'Sword', 8 => 'Sword (2H)', 10 => 'Staff', 13 => 'Fist Weapon',
            14 => 'Miscellaneous', 15 => 'Dagger', 16 => 'Thrown', 17 => 'Spear',
            18 => 'Crossbow', 19 => 'Wand', 20 => 'Fishing Pole'
        ],
        4 => [
            0 => 'Miscellaneous', 1 => 'Cloth', 2 => 'Leather', 3 => 'Mail', 4 => 'Plate',
            6 => 'Shield', 7 => 'Libram', 8 => 'Idol', 9 => 'Totem', 10 => 'Sigil'
        ]
    ];

    protected $triggerFlags = [
        0 => "Use",
        1 => "Equip",
        2 => "Chance on hit",
        4 => "Soulstone"
    ];

    protected $normalStats = [
        0 => "Mana",
        1 => "Health",
        3 => "Agility",
        4 => "Strength",
        5 => "Intellect",
        6 => "Spirit",
        7 => "Stamina"
    ];

    protected $specialStats = [
        12 => "Defense Rating",
        13 => "Dodge Rating",
        14 => "Parry Rating",
        15 => "Block Rating",
        16 => "Hit (Melee) Rating",
        17 => "Hit (Ranged) Rating",
        18 => "Hit (Spell) Rating",
        19 => "Crit (Melee) Rating",
        20 => "Crit (Ranged) Rating",
        21 => "Crit (Spell) Rating",
        22 => "Hit Taken (Melee) Rating",
        23 => "Hit Taken (Ranged) Rating",
        24 => "Hit Taken (Spell) Rating",
        25 => "Crit Taken (Melee) Rating",
        26 => "Crit Taken (Ranged) Rating",
        27 => "Crit Taken (Spell) Rating",
        28 => "Haste (Melee) Rating",
        29 => "Haste (Ranged) Rating",
        30 => "Haste (Spell) Rating",
        31 => "Hit Rating",
        32 => "Crit Rating",
        33 => "Hit Taken Rating",
        34 => "Crit Taken Rating",
        35 => "Resilience Rating",
        36 => "Haste Rating",
        37 => "Expertise Rating",
        38 => "Attack Power",
        39 => "Ranged Attack Power",
        40 => "Feral Attack Power",
        41 => "Healing Power",
        42 => "Spell Damage",
        43 => "Mana Regen",
        44 => "Armor Penetration Rating",
        45 => "Spell Power",
        46 => "Health Regen",
        47 => "Spell Penetration",
        48 => "Block Value"
    ];

    protected $socketColors = [
        1 => ['name' => 'Meta', 'icon' => '/img/shopimg/items/socketicons/socket_meta.gif'],
        2 => ['name' => 'Red', 'icon' => '/img/shopimg/items/socketicons/socket_red.gif'],
        4 => ['name' => 'Yellow', 'icon' => '/img/shopimg/items/socketicons/socket_yellow.gif'],
        8 => ['name' => 'Blue', 'icon' => '/img/shopimg/items/socketicons/socket_blue.gif']
    ];

    protected $classRestrictions = [
        1 => 'Warrior',
        2 => 'Paladin',
        4 => 'Hunter',
        8 => 'Rogue',
        16 => 'Priest',
        32 => 'Death Knight',
        64 => 'Shaman',
        128 => 'Mage',
        256 => 'Warlock',
        1024 => 'Druid'
    ];

    protected $classColors = [
        1 => '#C69B6D', // Warrior: Tan
        2 => '#F48CBA', // Paladin: Pink
        4 => '#AAD372', // Hunter: Pistachio
        8 => '#FFF468', // Rogue: Yellow
        16 => '#FFFFFF', // Priest: White
        32 => '#C41E3A', // Death Knight: Red
        64 => '#0070DD', // Shaman: Blue
        128 => '#3FC7EB', // Mage: Light Blue
        256 => '#8788EE', // Warlock: Purple
        1024 => '#FF7C0A' // Druid: Orange
    ];

    // Helpers
    protected function goldSilverCopper($amount)
    {
        $g = floor($amount / 10000);
        $s = floor(($amount % 10000) / 100);
        $c = $amount % 100;
        return "$g <span style='color:#ffd700;'>g</span> $s <span style='color:#c0c0c0;'>s</span> $c <span style='color:#b87333;'>c</span>";
    }

    protected function formatDPS($min, $max, $delay)
    {
        if ($delay <= 0) return '';
        $dps = ($min + $max) / 2 / ($delay / 1000);
        return number_format($dps, 1);
    }

    // Main tooltip generation method
    public function generateTooltip($item)
    {
        // Handle both array and object inputs
        $itemArray = is_array($item) ? $item : (array) $item;
        
        // Set item name color based on quality
        $itemColor = $this->qualityColors[$itemArray['Quality']] ?? '#ffffff';
        if ($itemArray['Quality'] == 7 && ($itemArray['flags'] & 134221824) == 134221824) {
            $itemColor = '#e6cc80';
        }

        $name = htmlspecialchars($itemArray['name']);
        $desc = htmlspecialchars($itemArray['description'] ?? '');
        $level = $itemArray['ItemLevel'];
        $reqLevel = $itemArray['RequiredLevel'];
        $sell = $itemArray['SellPrice'] ?? 0;
        $dur = $itemArray['MaxDurability'] ?? 0;
        // Only calculate speed for weapons (class = 2)
        $speed = ($itemArray['class'] == 2 && $itemArray['delay'] > 0) ? round($itemArray['delay'] / 1000, 2) : null;
        $bonding = $this->bondingTypes[$itemArray['bonding']] ?? null;
        $className = $this->classNames[$itemArray['class']] ?? 'Unknown';
        $subclassName = $this->subclassNames[$itemArray['class']][$itemArray['subclass']] ?? null;
        $invType = $this->inventoryTypes[$itemArray['InventoryType']] ?? null;

        // Class restrictions with colors
        $requiredClasses = [];
        if (isset($itemArray['AllowableClass']) && $itemArray['AllowableClass'] > 0) {
            foreach ($this->classRestrictions as $bit => $class) {
                if ($itemArray['AllowableClass'] & $bit) {
                    $color = $this->classColors[$bit] ?? '#ffffff';
                    $requiredClasses[] = "<span style='color:$color;'>$class</span>";
                }
            }
        }
        $requiredClassesText = !empty($requiredClasses) ? 'Classes: ' . implode(', ', $requiredClasses) : null;

        // Fetch spell effects for Use, Equip, Chance on Hit, and Soulstone triggers
        $spellEffects = [];
        try {
            $tableCheck = DB::connection('mysql_world')->select("SHOW TABLES LIKE 'armory_spell'");
            if (!empty($tableCheck)) {
                for ($i = 1; $i <= 5; $i++) {
                    $spellId = $itemArray["spellid_$i"] ?? 0;
                    $trigger = $itemArray["spelltrigger_$i"] ?? 0;
                    if ($spellId > 0) {
                        if (in_array($trigger, [0, 1, 2, 4])) {
                            $spell = DB::connection('mysql_world')
                                ->table('armory_spell')
                                ->where('id', $spellId)
                                ->first();

                            if ($spell) {
                                $triggerText = $this->triggerFlags[$trigger] ?? 'Unknown';
                                $description = !empty($spell->Description_ru_ru) ? htmlspecialchars($spell->Description_ru_ru) : htmlspecialchars($spell->ToolTip_1 ?? '');
                                if (!empty($description)) {
                                    $spellEffects[] = "$triggerText: $description";
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Skip spell effects if database connection fails
            error_log("ItemTooltipService: Failed to fetch spell effects: " . $e->getMessage());
        }

        ob_start();
        ?>
        <div class="item-tooltip-content">
            <div class="tooltip-header">
                <div>
                    <div class="item-name" style="color: <?= $itemColor ?> !important;"><?= $name ?></div>
                    <?php if ($level): ?><div class="item-level">Item Level <?= $level ?></div><?php endif; ?>
                </div>
                <div class="tooltip-right">
                    <div><?= $subclassName ?? '' ?></div>
                    <?php if ($speed): ?><div>Speed <?= $speed ?></div><?php endif; ?>
                </div>
            </div>

            <?php if ($bonding): ?><div><?= $bonding ?></div><?php endif; ?>
            <?php if ($invType): ?><div><?= $invType ?></div><?php endif; ?>
            <?php if ($className): ?><div><?= $className ?></div><?php endif; ?>

            <?php
            if (($itemArray['dmg_min1'] ?? 0) > 0 && ($itemArray['dmg_max1'] ?? 0) > 0):
                $min = $itemArray['dmg_min1'];
                $max = $itemArray['dmg_max1'];
            ?>
                <div><?= $min ?> - <?= $max ?> Damage</div>
                <div class="dps-text">(<?= $this->formatDPS($min, $max, $itemArray['delay'] ?? 0) ?> damage per second)</div>
            <?php endif; ?>

            <?php if (($itemArray['armor'] ?? 0) > 0): ?><div>+<?= $itemArray['armor'] ?> Armor</div><?php endif; ?>

            <?php for ($i = 1; $i <= 10; $i++):
                $type = $itemArray["stat_type$i"] ?? 0;
                $value = $itemArray["stat_value$i"] ?? 0;
                if ($type > 0 && $value != 0 && isset($this->normalStats[$type])): ?>
                    <div class="normal-stat">+<?= $value ?> <?= $this->normalStats[$type] ?></div>
            <?php endif; endfor; ?>

            <?php for ($i = 1; $i <= 10; $i++):
                $type = $itemArray["stat_type$i"] ?? 0;
                $value = $itemArray["stat_value$i"] ?? 0;
                if ($type > 0 && $value != 0 && isset($this->specialStats[$type])): ?>
                    <div class="special-stat">Equip: Increases +<?= $value ?> <?= $this->specialStats[$type] ?></div>
            <?php endif; endfor; ?>

            <?php
            $resistances = ['Holy' => $itemArray['holy_res'] ?? 0, 'Fire' => $itemArray['fire_res'] ?? 0, 'Nature' => $itemArray['nature_res'] ?? 0,
                            'Frost' => $itemArray['frost_res'] ?? 0, 'Shadow' => $itemArray['shadow_res'] ?? 0, 'Arcane' => $itemArray['arcane_res'] ?? 0];
            foreach ($resistances as $school => $val):
                if ($val > 0): ?>
                    <div class="resistance-stat">+<?= $val ?> <?= $school ?> Resistance</div>
            <?php endif; endforeach; ?>

            <!-- Sockets -->
            <div class="sockets-container">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <?php
                    $colorCode = $itemArray["socketColor_$i"] ?? null;
                    if (isset($this->socketColors[$colorCode])):
                        $colorData = $this->socketColors[$colorCode];
                    ?>
                        <div class="socket-item">
                            <img src="<?= $colorData['icon'] ?>" alt="<?= $colorData['name'] ?> socket" class="socket-icon">
                            <span class="socket-name" style="color: <?= strtolower($colorData['name']) ?>;">
                                <?= $colorData['name'] ?>
                            </span>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <?php if (!empty($itemArray['socketBonus'])): ?>
                <div class="socket-bonus">Socket Bonus: Spell ID <?= htmlspecialchars($itemArray['socketBonus']) ?></div>
            <?php endif; ?>

            <?php if ($dur > 0): ?><div>Durability <?= $dur ?>/<?= $dur ?></div><?php endif; ?>
            <?php if ($reqLevel): ?><div>Requires Level <?= $reqLevel ?></div><?php endif; ?>
            <?php if ($requiredClassesText): ?><div><?= $requiredClassesText ?></div><?php endif; ?>

            <?php for ($i = 1; $i <= 10; $i++):
                $type = $itemArray["stat_type$i"] ?? 0;
                $value = $itemArray["stat_value$i"] ?? 0;
                if ($type > 0 && $value != 0 && isset($this->specialStats[$type])): ?>
                    <div class="special-stat">Equip: Increases +<?= $value ?> <?= $this->specialStats[$type] ?></div>
            <?php endif; endfor; ?>

            <?php foreach ($spellEffects as $effect): ?>
                <div class="spell-effect"><?= $effect ?></div>
            <?php endforeach; ?>

            <?php if ($sell > 0): ?><div>Sell: <?= $this->goldSilverCopper($sell) ?></div><?php endif; ?>
            <?php if ($desc): ?><div class="item-description"><?= $desc ?></div><?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
