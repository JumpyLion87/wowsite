<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Character;
use App\Models\Realm;

class OnlinePlayersController extends Controller
{
    /**
     * Display online players page
     */
    public function index()
    {
        $cacheKey = 'online_players_data';
        $cacheDuration = 30; // seconds

        $data = Cache::remember($cacheKey, $cacheDuration, function () {
            return $this->fetchOnlinePlayersData();
        });

        return view('online-players', $data);
    }

    /**
     * Fetch online players data from database
     */
    private function fetchOnlinePlayersData()
    {
        // Get online players using the new model method
        $onlinePlayers = (new Character())->getOnlinePlayers();
        $totalOnline = (new Character())->getOnlineCharactersCount();

        // Get realm information
        $realmInfo = Realm::first();

        // Get class statistics using the new model method
        $classStats = (new Character())->getClassDistribution()
            ->pluck('count', 'class')
            ->toArray();

        // Get race statistics using the new model method
        $raceStats = (new Character())->getRaceDistribution()
            ->pluck('count', 'race')
            ->toArray();

        return [
            'online_players' => $onlinePlayers,
            'total_online' => $totalOnline,
            'realm_info' => $realmInfo,
            'class_stats' => $classStats,
            'race_stats' => $raceStats
        ];
    }

    /**
     * Get class names mapping
     */
    public static function getClassNames()
    {
        return [
            1 => __('online_players.class_warrior'),
            2 => __('online_players.class_paladin'),
            3 => __('online_players.class_hunter'),
            4 => __('online_players.class_rogue'),
            5 => __('online_players.class_priest'),
            6 => __('online_players.class_death_knight'),
            7 => __('online_players.class_shaman'),
            8 => __('online_players.class_mage'),
            9 => __('online_players.class_warlock'),
            10 => __('online_players.class_monk'),
            11 => __('online_players.class_druid')
        ];
    }

    /**
     * Get race names mapping
     */
    public static function getRaceNames()
    {
        return [
            1 => __('online_players.race_human'),
            2 => __('online_players.race_orc'),
            3 => __('online_players.race_dwarf'),
            4 => __('online_players.race_night_elf'),
            5 => __('online_players.race_undead'),
            6 => __('online_players.race_tauren'),
            7 => __('online_players.race_gnome'),
            8 => __('online_players.race_troll'),
            9 => __('online_players.race_goblin'),
            10 => __('online_players.race_blood_elf'),
            11 => __('online_players.race_draenei'),
            22 => __('online_players.race_worgen')
        ];
    }

    /**
     * Get class icons mapping
     */
    public static function getClassIcons()
    {
        return [
            1 => 'warrior',
            2 => 'paladin',
            3 => 'hunter',
            4 => 'rogue',
            5 => 'priest',
            6 => 'death_knight',
            7 => 'shaman',
            8 => 'mage',
            9 => 'warlock',
            10 => 'monk',
            11 => 'druid'
        ];
    }

    /**
     * Get race icons mapping
     */
    public static function getRaceIcons()
    {
        return [
            1 => 'human',
            2 => 'orc',
            3 => 'dwarf',
            4 => 'nightelf',
            5 => 'undead',
            6 => 'tauren',
            7 => 'gnome',
            8 => 'troll',
            9 => 'goblin',
            10 => 'bloodelf',
            11 => 'draenei',
            22 => 'worgen'
        ];
    }

    /**
     * Get zone names mapping
     */
    public static function getZoneNames()
    {
        return [
            0 => __('online_players.zone_eastern_kingdoms'),
            1 => __('online_players.zone_kalimdor'),
            12 => __('online_players.zone_elwynn_forest'),
            14 => __('online_players.zone_durotar'),
            17 => __('online_players.zone_azshara'),
            40 => __('online_players.zone_westfall'),
            85 => __('online_players.zone_tirisfal_glades'),
            215 => __('online_players.zone_mulgore'),
            267 => __('online_players.zone_hillsbrad_foothills'),
            530 => __('online_players.zone_outland'),
            571 => __('online_players.zone_northrend'),
            796 => __('online_players.zone_scarlet_monastery'),
            1497 => __('online_players.zone_undercity'),
            1519 => __('online_players.zone_stormwind'),
            1637 => __('online_players.zone_orgrimmar'),
            1638 => __('online_players.zone_thunder_bluff'),
            1657 => __('online_players.zone_darnassus'),
            3430 => __('online_players.zone_eversong_woods'),
            3483 => __('online_players.zone_hellfire_peninsula'),
            3521 => __('online_players.zone_zangarmarsh'),
            3524 => __('online_players.zone_blades_edge_mountains'),
            3703 => __('online_players.zone_shattrath'),
            4298 => __('online_players.zone_icecrown'),
        ];
    }

    /**
     * Get CSS class names for classes by ID
     */
    public static function getClassCssClasses()
    {
        return [
            1 => 'class-warrior',
            2 => 'class-paladin',
            3 => 'class-hunter',
            4 => 'class-rogue',
            5 => 'class-priest',
            6 => 'class-death-knight',
            7 => 'class-shaman',
            8 => 'class-mage',
            9 => 'class-warlock',
            10 => 'class-monk',
            11 => 'class-druid'
        ];
    }

    /**
     * Get CSS class names for races by ID
     */
    public static function getRaceCssClasses()
    {
        return [
            1 => 'race-human',
            2 => 'race-orc',
            3 => 'race-dwarf',
            4 => 'race-night-elf',
            5 => 'race-undead',
            6 => 'race-tauren',
            7 => 'race-gnome',
            8 => 'race-troll',
            9 => 'race-goblin',
            10 => 'race-blood-elf',
            11 => 'race-draenei',
            22 => 'race-worgen'
        ];
    }
}
