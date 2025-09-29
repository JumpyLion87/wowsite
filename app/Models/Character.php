<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'zone', 'death_expire_time', 'taxi_path', 'totalKills', 'todayKills',
        'yesterdayKills', 'chosenTitle', 'watchedFaction', 'drunk', 'health',
        'power1', 'power2', 'power3', 'power4', 'power5', 'power6', 'power7',
        'latency', 'talentGroupsCount', 'activeTalentGroup', 'lootSpecId',
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
}