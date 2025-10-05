<?php

namespace App\Helpers;

class WowHelper
{
    /**
     * Получить название класса по ID
     */
    public static function getClassName(int $classId): string
    {
        $classes = [
            1 => __('wow.class_warrior'),
            2 => __('wow.class_paladin'),
            3 => __('wow.class_hunter'),
            4 => __('wow.class_rogue'),
            5 => __('wow.class_priest'),
            6 => __('wow.class_death_knight'),
            7 => __('wow.class_shaman'),
            8 => __('wow.class_mage'),
            9 => __('wow.class_warlock'),
            11 => __('wow.class_druid'),
        ];

        return $classes[$classId] ?? __('wow.unknown');
    }

    /**
     * Получить CSS класс для цвета класса
     */
    public static function getClassColor(int $classId): string
    {
        $colors = [
            1 => 'class-warrior',      // Warrior - коричневый
            2 => 'class-paladin',      // Paladin - розовый
            3 => 'class-hunter',       // Hunter - зеленый
            4 => 'class-rogue',        // Rogue - желтый
            5 => 'class-priest',       // Priest - белый
            6 => 'class-death-knight', // Death Knight - красный
            7 => 'class-shaman',       // Shaman - синий
            8 => 'class-mage',         // Mage - голубой
            9 => 'class-warlock',      // Warlock - фиолетовый
            11 => 'class-druid',       // Druid - оранжевый
        ];

        return $colors[$classId] ?? '';
    }

    /**
     * Получить hex цвет класса
     */
    public static function getClassHexColor(int $classId): string
    {
        $colors = [
            1 => '#C79C6E',  // Warrior - коричневый
            2 => '#F58CBA',  // Paladin - розовый
            3 => '#ABD473',  // Hunter - зеленый
            4 => '#FFF569',  // Rogue - желтый
            5 => '#FFFFFF',  // Priest - белый
            6 => '#C41F3B',  // Death Knight - красный
            7 => '#0070DE',  // Shaman - синий
            8 => '#69CCF0',  // Mage - голубой
            9 => '#9482C9',  // Warlock - фиолетовый
            11 => '#FF7D0A', // Druid - оранжевый
        ];

        return $colors[$classId] ?? '#FFFFFF';
    }

    /**
     * Получить название расы по ID
     */
    public static function getRaceName(int $raceId): string
    {
        $races = [
            1 => __('wow.race_human'),
            2 => __('wow.race_orc'),
            3 => __('wow.race_dwarf'),
            4 => __('wow.race_night_elf'),
            5 => __('wow.race_undead'),
            6 => __('wow.race_tauren'),
            7 => __('wow.race_gnome'),
            8 => __('wow.race_troll'),
            10 => __('wow.race_blood_elf'),
            11 => __('wow.race_draenei'),
        ];

        return $races[$raceId] ?? __('wow.unknown');
    }

    /**
     * Получить название иконки класса
     */
    public static function getClassIconName(int $classId): string
    {
        $icons = [
            1 => 'warrior',
            2 => 'paladin',
            3 => 'hunter',
            4 => 'rogue',
            5 => 'priest',
            6 => 'deathknight',
            7 => 'shaman',
            8 => 'mage',
            9 => 'warlock',
            11 => 'druid',
        ];

        return $icons[$classId] ?? 'unknown';
    }

    /**
     * Получить HTML иконки класса
     */
    public static function getClassIcon(int $classId): string
    {
        $iconName = self::getClassIconName($classId);
        $path = "/img/accountimg/class/{$iconName}.webp";
        $className = self::getClassName($classId);
        
        return '<img src="' . $path . '" alt="' . $className . '" class="class-icon" width="24" height="24">';
    }

    /**
     * Получить полный путь к иконке класса
     */
    public static function getClassIconPath(int $classId): string
    {
        $icon = self::getClassIconName($classId);
        return "/img/accountimg/class/{$icon}.webp";
    }

    /**
     * Получить название иконки расы
     */
    public static function getRaceIconName(int $raceId): string
    {
        $icons = [
            1 => 'human',
            2 => 'orc',
            3 => 'dwarf',
            4 => 'nightelf',
            5 => 'undead',
            6 => 'tauren',
            7 => 'gnome',
            8 => 'troll',
            10 => 'bloodelf',
            11 => 'draenei',
        ];

        return $icons[$raceId] ?? 'unknown';
    }

    /**
     * Получить фракцию по расе
     */
    public static function getFaction(int $raceId): string
    {
        $allianceRaces = [1, 3, 4, 7, 11];
        $hordeRaces = [2, 5, 6, 8, 10];

        if (in_array($raceId, $allianceRaces)) {
            return __('wow.alliance');
        } elseif (in_array($raceId, $hordeRaces)) {
            return __('wow.horde');
        }

        return __('wow.unknown');
    }

    /**
     * Получить путь к иконке фракции
     */
    public static function getFactionIconPath(int $raceId): string
    {
        $allianceRaces = [1, 3, 4, 7, 11];
        $faction = in_array($raceId, $allianceRaces) ? 'alliance' : 'horde';
        return "/img/accountimg/faction/{$faction}.png";
    }

    /**
     * Получить HTML иконки фракции
     */
    public static function getFactionIcon(int $raceId): string
    {
        $path = self::getFactionIconPath($raceId);
        $faction = in_array($raceId, [1, 3, 4, 7, 11]) ? 'alliance' : 'horde';
        $alt = $faction === 'alliance' ? __('wow.alliance') : __('wow.horde');
        
        return '<img src="' . $path . '" alt="' . $alt . '" class="faction-icon" width="24" height="24">';
    }

    /**
     * Получить путь к иконке расы
     */
    public static function getRaceIconPath(int $raceId, int $gender = 0): string
    {
        $genderFolder = ($gender == 0) ? 'male' : 'female';
        $icon = self::getRaceIconName($raceId);
        return "/img/accountimg/race/{$genderFolder}/{$icon}.png";
    }

    /**
     * Получить HTML иконки расы
     */
    public static function getRaceIcon(int $raceId, int $gender = 0): string
    {
        $path = self::getRaceIconPath($raceId, $gender);
        $raceName = self::getRaceName($raceId);
        
        return '<img src="' . $path . '" alt="' . $raceName . '" class="race-icon" width="24" height="24">';
    }
}
