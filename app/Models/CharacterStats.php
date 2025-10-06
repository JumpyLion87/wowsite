<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CharacterStats extends Model
{
    use HasFactory;

    protected $connection = 'mysql_char';
    protected $table = 'character_stats';
    protected $primaryKey = 'guid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'guid', 'maxhealth', 'maxpower1', 'maxpower2', 'maxpower3', 'maxpower4', 
        'maxpower5', 'maxpower6', 'maxpower7', 'strength', 'agility', 'stamina', 
        'intellect', 'spirit', 'armor', 'resHoly', 'resFire', 'resNature', 
        'resFrost', 'resShadow', 'resArcane', 'blockPct', 'dodgePct', 'parryPct', 
        'critPct', 'rangedCritPct', 'spellCritPct', 'attackPower', 'rangedAttackPower', 
        'spellPower', 'resilience'
    ];

    protected $casts = [
        'guid' => 'integer',
        'maxhealth' => 'integer',
        'maxpower1' => 'integer',
        'maxpower2' => 'integer',
        'maxpower3' => 'integer',
        'maxpower4' => 'integer',
        'maxpower5' => 'integer',
        'maxpower6' => 'integer',
        'maxpower7' => 'integer',
        'strength' => 'integer',
        'agility' => 'integer',
        'stamina' => 'integer',
        'intellect' => 'integer',
        'spirit' => 'integer',
        'armor' => 'integer',
        'resHoly' => 'integer',
        'resFire' => 'integer',
        'resNature' => 'integer',
        'resFrost' => 'integer',
        'resShadow' => 'integer',
        'resArcane' => 'integer',
        'blockPct' => 'float',
        'dodgePct' => 'float',
        'parryPct' => 'float',
        'critPct' => 'float',
        'rangedCritPct' => 'float',
        'spellCritPct' => 'float',
        'attackPower' => 'integer',
        'rangedAttackPower' => 'integer',
        'spellPower' => 'integer',
        'resilience' => 'integer'
    ];

    /**
     * Get the character that owns the stats
     */
    public function character()
    {
        return $this->belongsTo(Character::class, 'guid', 'guid');
    }
}
