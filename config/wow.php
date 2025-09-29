<?php

return [
    'slots' => [
        'definitions' => [
            0 => 'head',
            1 => 'neck',
            2 => 'shoulders',
            3 => 'body',
            4 => 'chest',
            5 => 'waist',
            6 => 'legs',
            7 => 'feet',
            8 => 'wrists',
            9 => 'hands',
            10 => 'finger',
            11 => 'finger',
            12 => 'trinket',
            13 => 'trinket',
            14 => 'back',
            15 => 'main_hand',
            16 => 'off_hand',
            17 => 'ranged',
            18 => 'tabard'
        ],
        'labels' => [
            0 => 'Head',
            1 => 'Neck',
            2 => 'Shoulders',
            3 => 'Body',
            4 => 'Chest',
            5 => 'Waist',
            6 => 'Legs',
            7 => 'Feet',
            8 => 'Wrists',
            9 => 'Hands',
            10 => 'Finger',
            11 => 'Finger',
            12 => 'Trinket',
            13 => 'Trinket',
            14 => 'Back',
            15 => 'Main Hand',
            16 => 'Off Hand',
            17 => 'Ranged',
            18 => 'Tabard'
        ],
        'default_icons' => [
            0 => 'head.gif',
            1 => 'neck.gif',
            2 => 'shoulders.gif',
            3 => 'body.gif',
            4 => 'chest.gif',
            5 => 'waist.gif',
            6 => 'legs.gif',
            7 => 'feet.gif',
            8 => 'wrists.gif',
            9 => 'hands.gif',
            10 => 'finger.gif',
            11 => 'finger.gif',
            12 => 'trinket.gif',
            13 => 'trinket.gif',
            14 => 'back.gif',
            15 => 'mainhand.gif',
            16 => 'offhand.gif',
            17 => 'ranged.gif',
            18 => 'tabard.gif'
        ]
    ],
    'races' => [
        1 => ['name' => 'Human', 'icon' => 'human'],
        2 => ['name' => 'Orc', 'icon' => 'orc'],
        3 => ['name' => 'Dwarf', 'icon' => 'dwarf'],
        4 => ['name' => 'Night Elf', 'icon' => 'nightelf'],
        5 => ['name' => 'Undead', 'icon' => 'undead'],
        6 => ['name' => 'Tauren', 'icon' => 'tauren'],
        7 => ['name' => 'Gnome', 'icon' => 'gnome'],
        8 => ['name' => 'Troll', 'icon' => 'troll'],
        10 => ['name' => 'Blood Elf', 'icon' => 'bloodelf'],
        11 => ['name' => 'Draenei', 'icon' => 'draenei']
    ],
    'classes' => [
        1 => ['name' => 'Warrior', 'icon' => 'warrior'],
        2 => ['name' => 'Paladin', 'icon' => 'paladin'],
        3 => ['name' => 'Hunter', 'icon' => 'hunter'],
        4 => ['name' => 'Rogue', 'icon' => 'rogue'],
        5 => ['name' => 'Priest', 'icon' => 'priest'],
        6 => ['name' => 'Death Knight', 'icon' => 'deathknight'],
        7 => ['name' => 'Shaman', 'icon' => 'shaman'],
        8 => ['name' => 'Mage', 'icon' => 'mage'],
        9 => ['name' => 'Warlock', 'icon' => 'warlock'],
        11 => ['name' => 'Druid', 'icon' => 'druid']
    ],
    'power_types' => [
        0 => 'Mana',
        1 => 'Rage',
        2 => 'Focus',
        3 => 'Energy',
        4 => 'Happiness',
        5 => 'Runes',
        6 => 'Runic Power'
    ],
    'factions' => [
        1 => ['name' => 'Alliance', 'icon' => 'alliance'],
        3 => ['name' => 'Alliance', 'icon' => 'alliance'],
        4 => ['name' => 'Alliance', 'icon' => 'alliance'],
        7 => ['name' => 'Alliance', 'icon' => 'alliance'],
        11 => ['name' => 'Alliance', 'icon' => 'alliance'],
        2 => ['name' => 'Horde', 'icon' => 'horde'],
        5 => ['name' => 'Horde', 'icon' => 'horde'],
        6 => ['name' => 'Horde', 'icon' => 'horde'],
        8 => ['name' => 'Horde', 'icon' => 'horde'],
        10 => ['name' => 'Horde', 'icon' => 'horde']
    ],
    'class_abbreviations' => [
        'Warrior' => 'War',
        'Paladin' => 'Pal',
        'Hunter' => 'Hunt',
        'Rogue' => 'Rog',
        'Priest' => 'Pri',
        'Death Knight' => 'DK',
        'Shaman' => 'Sham',
        'Mage' => 'Mag',
        'Warlock' => 'Lock',
        'Druid' => 'Dru'
    ],
    'quality_colors' => [
        0 => '#9d9d9d', // Poor (Grey)
        1 => '#ffffff', // Common (White)
        2 => '#1eff00', // Uncommon (Green)
        3 => '#0070dd', // Rare (Blue)
        4 => '#a335ee', // Epic (Purple)
        5 => '#ff8000', // Legendary (Orange)
        6 => '#e6cc80'  // Artifact (Gold)
    ]
];
