<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\ItemTooltipService;
use App\Models\Character;
use App\Models\CharacterStats;
use App\Models\ArenaTeam;
use App\Models\ItemTemplate;

class CharacterController extends Controller
{
    protected $itemTooltipService;

    public function __construct(ItemTooltipService $itemTooltipService)
    {
        $this->itemTooltipService = $itemTooltipService;
    }

    /**
     * Display character page
     */
    public function show(Request $request, $guid = null)
    {
        $guid = $guid ?: $request->get('guid');
        
        if (!$guid || $guid <= 0) {
            return redirect()->route('home')->with('error', __('character.error_invalid_guid'));
        }

        // Cache configuration
        $cacheKey = "character_{$guid}";
        $cacheDuration = 300; // 5 minutes

        // Try to get data from cache
        $cachedData = Cache::get($cacheKey);
        
        if ($cachedData && (time() - $cachedData['timestamp']) < $cacheDuration) {
            $character = $cachedData['character'];
            $stats = $cachedData['stats'];
            $pvpTeams = $cachedData['pvp_teams'];
            $equippedItems = $cachedData['items'];
            $totalKills = $cachedData['total_kills'];
        } else {
            // Fetch character data
            $character = Character::where('guid', $guid)->first();
            
            if (!$character) {
                return redirect()->route('home')->with('error', __('character.error_character_not_found', ['guid' => $guid]));
            }

            // Fetch stats data
            $stats = CharacterStats::where('guid', $guid)->first();

            // Fetch arena team data
            $pvpTeams = $this->getArenaTeams($guid);

            // Fetch equipped items
            $equippedItems = $this->getEquippedItems($guid);

            // Get total kills
            $totalKills = $character->totalKills ?? 0;

            // Cache the data
            $cacheData = [
                'timestamp' => time(),
                'character' => $character,
                'stats' => $stats,
                'pvp_teams' => $pvpTeams,
                'items' => $equippedItems,
                'total_kills' => $totalKills
            ];
            Cache::put($cacheKey, $cacheData, $cacheDuration);
        }

        return view('character.show', compact(
            'character',
            'stats',
            'pvpTeams',
            'equippedItems',
            'totalKills'
        ));
    }

    /**
     * Get character by GUID
     */
    public function byGuid($guid)
    {
        return $this->show(request(), $guid);
    }

    /**
     * Get character by name
     */
    public function showByName($name)
    {
        $character = Character::where('name', $name)->first();
        
        if (!$character) {
            return redirect()->route('home')->with('error', __('character.error_character_not_found', ['name' => $name]));
        }

        return $this->show(request(), $character->guid);
    }

    /**
     * Get arena teams for character
     */
    private function getArenaTeams($guid)
    {
        $teams = DB::connection('mysql_char')
            ->table('arena_team_member as atm')
            ->join('arena_team as at', 'atm.arenaTeamId', '=', 'at.arenaTeamId')
            ->where('atm.guid', $guid)
            ->select('at.arenaTeamId', 'at.name', 'at.type', 'at.rating')
            ->get();

        foreach ($teams as $team) {
            $team->members = $this->getArenaTeamMembers($team->arenaTeamId);
        }

        return $teams;
    }

    /**
     * Get arena team members
     */
    private function getArenaTeamMembers($arenaTeamId)
    {
        return DB::connection('mysql_char')
            ->table('arena_team_member as atm')
            ->join('characters as c', 'atm.guid', '=', 'c.guid')
            ->where('atm.arenaTeamId', $arenaTeamId)
            ->select('c.guid', 'c.name', 'c.race', 'c.class', 'c.gender')
            ->get()
            ->map(function ($member) {
                $member->faction = $this->getFactionByRace($member->race);
                $member->faction_icon = $this->getFactionIconByRace($member->race);
                return $member;
            });
    }

    /**
     * Get equipped items for character
     */
    private function getEquippedItems($guid)
    {
        // Get item entries from character inventory
        $itemEntries = DB::connection('mysql_char')
            ->table('character_inventory as ci')
            ->join('item_instance as ii', 'ci.item', '=', 'ii.guid')
            ->where('ci.guid', $guid)
            ->where('ci.bag', 0)
            ->whereIn('ci.slot', [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18])
            ->pluck('ii.itemEntry', 'ci.slot')
            ->toArray();

        if (empty($itemEntries)) {
            return [];
        }

        // Get item templates from world database
        $items = DB::connection('mysql_world')
            ->table('item_template as it')
            ->leftJoin('itemdisplayinfo_dbc as idi', 'it.displayid', '=', 'idi.ID')
            ->whereIn('it.entry', array_values($itemEntries))
            ->select([
                'it.entry', 'it.name', 'it.Quality', 'it.ItemLevel', 'it.RequiredLevel', 'it.SellPrice',
                'it.MaxDurability', 'it.delay', 'it.bonding', 'it.class', 'it.subclass', 'it.InventoryType',
                'it.dmg_min1', 'it.dmg_max1', 'it.armor', 'it.holy_res', 'it.fire_res', 'it.nature_res',
                'it.frost_res', 'it.shadow_res', 'it.arcane_res', 'it.stat_type1', 'it.stat_value1',
                'it.stat_type2', 'it.stat_value2', 'it.stat_type3', 'it.stat_value3', 'it.stat_type4',
                'it.stat_value4', 'it.stat_type5', 'it.stat_value5', 'it.stat_type6', 'it.stat_value6',
                'it.stat_type7', 'it.stat_value7', 'it.stat_type8', 'it.stat_value8', 'it.stat_type9',
                'it.stat_value9', 'it.stat_type10', 'it.stat_value10', 'it.socketColor_1',
                'it.socketColor_2', 'it.socketColor_3', 'it.socketBonus', 'it.spellid_1',
                'it.spelltrigger_1', 'it.spellid_2', 'it.spelltrigger_2', 'it.spellid_3',
                'it.spelltrigger_3', 'it.spellid_4', 'it.spelltrigger_4', 'it.spellid_5',
                'it.spelltrigger_5', 'it.description', 'it.AllowableClass', 'it.displayid',
                'idi.InventoryIcon_1 as icon'
            ])
            ->get()
            ->keyBy('entry');

        // Map items to slots
        $equippedItems = [];
        foreach ($itemEntries as $slot => $entry) {
            if (isset($items[$entry])) {
                $item = $items[$entry];
                if (!empty($item->icon)) {
                    $item->icon = strtolower($item->icon);
                }
                $equippedItems[$slot] = $item;
            }
        }

        return $equippedItems;
    }

    /**
     * Get faction by race
     */
    private function getFactionByRace($race)
    {
        $allianceRaces = [1, 3, 4, 7, 11];
        $hordeRaces = [2, 5, 6, 8, 10];
        
        if (in_array($race, $allianceRaces)) {
            return 'Alliance';
        } elseif (in_array($race, $hordeRaces)) {
            return 'Horde';
        }
        
        return 'Unknown';
    }

    /**
     * Get faction icon by race
     */
    private function getFactionIconByRace($race)
    {
        $allianceRaces = [1, 3, 4, 7, 11];
        $hordeRaces = [2, 5, 6, 8, 10];
        
        if (in_array($race, $allianceRaces)) {
            return 'alliance';
        } elseif (in_array($race, $hordeRaces)) {
            return 'horde';
        }
        
        return 'unknown';
    }
}