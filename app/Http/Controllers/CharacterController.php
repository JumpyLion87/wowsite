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
            // Fetch character data with optimized queries
            $character = Character::where('guid', $guid)->first();
        
            if (!$character) {
                return redirect()->route('home')->with('error', __('character.error_character_not_found', ['guid' => $guid]));
            }

            // Fetch stats data
            $stats = CharacterStats::where('guid', $guid)->first();

            // Fetch arena team data with optimized query
            $pvpTeams = $this->getArenaTeamsOptimized($guid);

            // Fetch equipped items with optimized query
            $equippedItems = $this->getEquippedItemsOptimized($guid);

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

        // Get spell information for items with spells
        $spellIds = [];
        foreach ($items as $item) {
            for ($i = 1; $i <= 5; $i++) {
                $spellId = $item->{"spellid_$i"};
                if ($spellId > 0) {
                    $spellIds[] = $spellId;
                }
            }
        }

        $spells = [];
        if (!empty($spellIds)) {
            $spells = DB::connection('mysql_world')
                ->table('armory_spell')
                ->whereIn('id', array_unique($spellIds))
                ->select([
                    'id', 'Description_ru_ru', 'ToolTip_1'
                ])
                ->get()
                ->keyBy('id');
        }

        // Map items to slots and add spell information
        $equippedItems = [];
        foreach ($itemEntries as $slot => $entry) {
            if (isset($items[$entry])) {
                $item = $items[$entry];
                if (!empty($item->icon)) {
                    $item->icon = strtolower($item->icon);
                }
                
                // Add spell information to item (only for triggers 0, 1, 2, 4)
                $item->spellEffects = [];
                for ($i = 1; $i <= 5; $i++) {
                    $spellId = $item->{"spellid_$i"};
                    $spellTrigger = $item->{"spelltrigger_$i"};
                    if ($spellId > 0 && in_array($spellTrigger, [0, 1, 2, 4]) && isset($spells[$spellId])) {
                        $spell = $spells[$spellId];
                        $triggerText = $this->getSpellTriggerText($spellTrigger);
                        $description = !empty($spell->Description_ru_ru) ? $spell->Description_ru_ru : $spell->ToolTip_1;
                        if (!empty($description)) {
                            $item->spellEffects[] = "$triggerText: $description";
                        }
                    }
                }
                
                // Convert stat values to integers and filter out formulas
                for ($i = 1; $i <= 10; $i++) {
                    $statValue = $item->{"stat_value$i"};
                    if (is_string($statValue) && (strpos($statValue, '$') === 0 || !is_numeric($statValue))) {
                        $item->{"stat_value$i"} = 0;
                    } else {
                        $item->{"stat_value$i"} = (int)$statValue;
                    }
                }
                
                // Debug: Log item stats for debugging
                \Log::info("Item {$item->entry} stats:", [
                    'stat_type1' => $item->stat_type1,
                    'stat_value1' => $item->stat_value1,
                    'stat_type2' => $item->stat_type2,
                    'stat_value2' => $item->stat_value2,
                ]);
                
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

    /**
     * Get spell trigger text
     */
    private function getSpellTriggerText($trigger)
    {
        $triggers = [
            0 => 'Use',
            1 => 'Equip',
            2 => 'Chance on hit',
            4 => 'Soulstone'
        ];
        
        return $triggers[$trigger] ?? 'Unknown';
    }

    /**
     * Get arena teams for character (optimized version)
     */
    private function getArenaTeamsOptimized($guid)
    {
        // Single optimized query with all necessary data
        $teams = DB::connection('mysql_char')
            ->table('arena_team_member as atm')
            ->join('arena_team as at', 'atm.arenaTeamId', '=', 'at.arenaTeamId')
            ->where('atm.guid', $guid)
            ->select('at.arenaTeamId', 'at.name', 'at.type', 'at.rating')
            ->get();

        if ($teams->isEmpty()) {
            return collect();
        }

        // Get all team members in one query
        $teamIds = $teams->pluck('arenaTeamId')->toArray();
        $allMembers = DB::connection('mysql_char')
            ->table('arena_team_member as atm')
            ->join('characters as c', 'atm.guid', '=', 'c.guid')
            ->whereIn('atm.arenaTeamId', $teamIds)
            ->select('atm.arenaTeamId', 'c.guid', 'c.name', 'c.race', 'c.class', 'c.gender')
            ->get()
            ->groupBy('arenaTeamId');

        // Attach members to teams
        foreach ($teams as $team) {
            $team->members = $allMembers->get($team->arenaTeamId, collect())
                ->map(function ($member) {
                    $member->faction = $this->getFactionByRace($member->race);
                    $member->faction_icon = $this->getFactionIconByRace($member->race);
                    return $member;
                });
        }

        return $teams;
    }

    /**
     * Get equipped items for character (optimized version)
     */
    private function getEquippedItemsOptimized($guid)
    {
        // Single query to get all equipped items with their data
        $items = DB::connection('mysql_char')
            ->table('character_inventory as ci')
            ->join('item_instance as ii', 'ci.item', '=', 'ii.guid')
            ->join('item_template as it', 'ii.itemEntry', '=', 'it.entry')
            ->leftJoin('itemdisplayinfo_dbc as idi', 'it.displayid', '=', 'idi.ID')
            ->where('ci.guid', $guid)
            ->where('ci.bag', 0)
            ->whereIn('ci.slot', [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18])
            ->select([
                'ci.slot',
                'it.entry', 'it.name', 'it.Quality', 'it.ItemLevel', 'it.RequiredLevel', 'it.SellPrice',
                'it.MaxDurability', 'it.delay', 'it.bonding', 'it.class', 'it.subclass', 'it.InventoryType',
                'it.dmg_min1', 'it.dmg_max1', 'it.armor', 'it.holy_res', 'it.fire_res', 'it.nature_res',
                'it.frost_res', 'it.shadow_res', 'it.arcane_res', 'it.stat_type1', 'it.stat_value1',
                'it.stat_type2', 'it.stat_value2', 'it.stat_type3', 'it.stat_value3', 'it.stat_type4',
                'it.stat_value4', 'it.stat_type5', 'it.stat_value5', 'it.stat_type6', 'it.stat_value6',
                'it.stat_type7', 'it.stat_value7', 'it.stat_type8', 'it.stat_value8', 'it.stat_type9',
                'it.stat_type9', 'it.stat_value9', 'it.stat_type10', 'it.stat_value10', 'it.socketColor_1',
                'it.socketColor_2', 'it.socketColor_3', 'it.socketBonus', 'it.spellid_1',
                'it.spelltrigger_1', 'it.spellid_2', 'it.spelltrigger_2', 'it.spellid_3',
                'it.spelltrigger_3', 'it.spellid_4', 'it.spelltrigger_4', 'it.spellid_5',
                'it.spelltrigger_5', 'it.description', 'it.AllowableClass', 'it.displayid',
                'idi.InventoryIcon_1 as icon'
            ])
            ->get()
            ->keyBy('slot');

        // Process items and add tooltip data
        return $items->map(function ($item) {
            $item->tooltip = $this->itemTooltipService->generateTooltip($item);
            return $item;
        });
    }
}