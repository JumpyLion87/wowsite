<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Character extends Model
{
    use HasFactory;

    protected $connection = 'mysql_char';
    protected $table = 'characters';
    protected $primaryKey = 'guid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'guid', 'account', 'name', 'race', 'class', 'gender', 'level', 'xp', 'money',
        'playerBytes', 'playerBytes2', 'playerFlags', 'position_x', 'position_y', 
        'position_z', 'map', 'instance_id', 'orientation', 'taximask', 'online',
        'totaltime', 'leveltime', 'rest_bonus', 'logout_time', 'is_logout_resting',
        'rest_bonus', 'resettalents_cost', 'resettalents_time', 'trans_x', 'trans_y',
        'trans_z', 'trans_o', 'transguid', 'extra_flags', 'stable_slots', 'at_login',
        'zone', 'death_expire_time', 'taxi_path', 'arena_points', 'totalHonorPoints',
        'totalKills', 'todayKills','yesterdayKills', 'chosenTitle', 'watchedFaction', 
        'drunk', 'health', 'power1', 'power2', 'power3', 'power4', 'power5', 'power6',
        'power7', 'latency', 'talentGroupsCount', 'activeTalentGroup', 'lootSpecId',
        'exploredZones', 'equipmentCache', 'ammoId', 'knownTitles', 'actionBars',
        'grantableLevels', 'deleteInfos_Account', 'deleteInfos_Name', 'deleteDate'
    ];

    protected $casts = [
        'guid' => 'integer',
        'account' => 'integer',
        'race' => 'integer',
        'class' => 'integer',
        'gender' => 'integer',
        'level' => 'integer',
        'xp' => 'integer',
        'money' => 'integer',
        'playerBytes' => 'integer',
        'playerBytes2' => 'integer',
        'playerFlags' => 'integer',
        'position_x' => 'float',
        'position_y' => 'float',
        'position_z' => 'float',
        'map' => 'integer',
        'instance_id' => 'integer',
        'orientation' => 'float',
        'online' => 'boolean',
        'totaltime' => 'integer',
        'leveltime' => 'integer',
        'rest_bonus' => 'float',
        'logout_time' => 'integer',
        'is_logout_resting' => 'boolean',
        'resettalents_cost' => 'integer',
        'resettalents_time' => 'integer',
        'trans_x' => 'float',
        'trans_y' => 'float',
        'trans_z' => 'float',
        'trans_o' => 'float',
        'transguid' => 'integer',
        'extra_flags' => 'integer',
        'stable_slots' => 'integer',
        'at_login' => 'integer',
        'zone' => 'integer',
        'death_expire_time' => 'integer',
        'totalKills' => 'integer',
        'todayKills' => 'integer',
        'yesterdayKills' => 'integer',
        'chosenTitle' => 'integer',
        'watchedFaction' => 'integer',
        'drunk' => 'integer',
        'health' => 'integer',
        'power1' => 'integer',
        'power2' => 'integer',
        'power3' => 'integer',
        'power4' => 'integer',
        'power5' => 'integer',
        'power6' => 'integer',
        'power7' => 'integer',
        'latency' => 'integer',
        'talentGroupsCount' => 'integer',
        'activeTalentGroup' => 'integer',
        'lootSpecId' => 'integer',
        'ammoId' => 'integer',
        'deleteDate' => 'integer'
    ];

    /**
     * Get character stats
     */
    public function stats()
    {
        return $this->hasOne(CharacterStats::class, 'guid', 'guid');
    }

    /**
     * Get character inventory
     */
    public function inventory()
    {
        return $this->hasMany(CharacterInventory::class, 'guid', 'guid');
    }

    /**
     * Функция для PvP teams
     */
    public function getFormattedType(): string
    {
        return match ($this->type) {
            2 => '2v2',
            3 => '3v3',
            default => '5v5',
        };
    }

    /**
     * Get arena teams
     */
    public function arenaTeams()
    {
        return $this->belongsToMany(ArenaTeam::class, 'arena_team_member', 'guid', 'arenaTeamId');
    }

    /**
     * Get race name
     */
    public function getRaceNameAttribute()
    {
        $races = config('wow.races');
        return $races[$this->race]['name'] ?? 'Unknown';
    }

    /**
     * Get class name
     */
    public function getClassNameAttribute()
    {
        $classes = config('wow.classes');
        return $classes[$this->class]['name'] ?? 'Unknown';
    }

    /**
     * Get faction
     */
    public function getFactionAttribute()
    {
        $allianceRaces = [1, 3, 4, 7, 11];
        $hordeRaces = [2, 5, 6, 8, 10];
        
        if (in_array($this->race, $allianceRaces)) {
            return 'Alliance';
        } elseif (in_array($this->race, $hordeRaces)) {
            return 'Horde';
        }
        
        return 'Unknown';
    }

    /**
     * Get faction icon
     */
    public function getFactionIconAttribute()
    {
        $allianceRaces = [1, 3, 4, 7, 11];
        $hordeRaces = [2, 5, 6, 8, 10];
        
        if (in_array($this->race, $allianceRaces)) {
            return 'alliance';
        } elseif (in_array($this->race, $hordeRaces)) {
            return 'horde';
        }
        
        return 'unknown';
    }

    // Get total number of online characters
    public function getOnlineCharactersCount()
    {
        try {
            return self::where('online', 1)->count();
        }
        catch (Exception $e) {
            Log::error('Error getting online characters count: ' . $e->getMessage());
            return 0;
        }
    }

    // Get total number of characters
    public function getTotalCharacters()
    {
        try {
            return self::count();
        }
        catch (\Exception $e) {
            Log::error('Error getting total characters: ' . $e->getMessage());
            return 0;
        }
    }


    // Get characters by level distribution

    public function getLevelDistribution()
    {
        try {
            return self::select('level', DB::raw('COUNT(*) as count'))
               ->where('online', 1)
               ->groupBy('level')
               ->orderBy('level', 'desc')
                ->get();
        } catch (\Exception $e) {
           Log::error("Error getting level distribution: " . $e->getMessage());
           return collect();
        }
    }

    /**
     * Get characters by class distribution
     */
    public function getClassDistribution()
    {
        try {
            return self::select('class', DB::raw('COUNT(*) as count'))
                ->where('online', 1)
                ->groupBy('class')
                ->orderBy('count', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error("Error getting class distribution: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get characters by race distribution
     */
    public function getRaceDistribution()
    {
        try {
            return self::select('race', DB::raw('COUNT(*) as count'))
                ->where('online', 1)
                ->groupBy('race')
                ->orderBy('count', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error("Error getting race distribution: " . $e->getMessage());
            return collect();
        }
    }

     /**
    * Get recently created characters
     */
    public function getRecentlyCreated($limit = 10)
    {
        try {
            return self::orderBy('created_at', 'desc')
                ->limit($limit)
                ->get(['name', 'race', 'class', 'level', 'created_at']);
        } catch (\Exception $e) {
            Log::error("Error getting recently created characters: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get online characters with details
     */
    public function getOnlinePlayers()
    {
        try {
            return self::where('online', 1)
                ->orderBy('name')
                ->get(['guid', 'name', 'race', 'class', 'level', 'zone']);
        } catch (\Exception $e) {
            Log::error("Error getting online players: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get characters by account ID
     */
    public function getCharactersByAccount($accountId)
    {
        try {
            return self::where('account', $accountId)
                ->orderBy('name')
                ->get(['guid', 'name', 'race', 'class', 'gender', 'level', 'online']);
        } catch (\Exception $e) {
            Log::error("Error getting characters by account: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get character by GUID
     */
    public function getCharacterById($characterId)
    {
        try {
            return self::find($characterId);
        } catch (\Exception $e) {
            Log::error("Error getting character by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get character by name
     */
    public function getCharacterByName($characterName)
    {
        try {
            return self::where('name', $characterName)->first();
        } catch (\Exception $e) {
            Log::error("Error getting character by name: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get top characters by level
     */
    public function getTopCharactersByLevel($limit = 10)
    {
        try {
            return self::orderBy('level', 'desc')
                ->orderBy('name', 'asc')
                ->limit($limit)
                ->get(['name', 'race', 'class', 'level']);
        } catch (\Exception $e) {
            Log::error("Error getting top characters by level: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get faction distribution (Alliance/Horde)
     */
    public function getFactionDistribution()
    {
        try {
            return self::selectRaw("
                CASE 
                    WHEN race IN (1, 3, 4, 7, 11, 22, 25, 29) THEN 'Alliance'
                    WHEN race IN (2, 5, 6, 8, 9, 10, 26) THEN 'Horde'
                    ELSE 'Neutral'
                END as faction,
                COUNT(*) as count
            ")
            ->groupBy('faction')
            ->orderBy('count', 'desc')
            ->get();
        } catch (\Exception $e) {
            Log::error("Error getting faction distribution: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get level statistics by ranges
     */
    public function getLevelStatistics()
    {
        try {
            return self::selectRaw("
                CASE 
                    WHEN level BETWEEN 1 AND 10 THEN '1-10'
                    WHEN level BETWEEN 11 AND 20 THEN '11-20'
                    WHEN level BETWEEN 21 AND 30 THEN '21-30'
                    WHEN level BETWEEN 31 AND 40 THEN '31-40'
                    WHEN level BETWEEN 41 AND 50 THEN '41-50'
                    WHEN level BETWEEN 51 AND 60 THEN '51-60'
                    WHEN level BETWEEN 61 AND 70 THEN '61-70'
                    WHEN level BETWEEN 71 AND 80 THEN '71-80'
                    ELSE '80+'
                END as level_range,
                COUNT(*) as count
            ")
            ->groupBy('level_range')
            ->orderBy('level_range')
            ->get();
        } catch (\Exception $e) {
            Log::error("Error getting level statistics: " . $e->getMessage());
            return collect();
        }
    }
}