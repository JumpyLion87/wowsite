<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArenaTeam extends Model
{
    use HasFactory;

    protected $connection = 'mysql_char';
    protected $table = 'arena_team';
    protected $primaryKey = 'arenaTeamId';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'arenaTeamId', 'name', 'captainGuid', 'type', 'rating', 'seasonGames',
        'seasonWins', 'weekGames', 'weekWins', 'rank', 'backgroundColor', 'emblemStyle',
        'emblemColor', 'borderStyle', 'borderColor'
    ];

    protected $casts = [
        'arenaTeamId' => 'integer',
        'captainGuid' => 'integer',
        'type' => 'integer',
        'rating' => 'integer',
        'seasonGames' => 'integer',
        'seasonWins' => 'integer',
        'weekGames' => 'integer',
        'weekWins' => 'integer',
        'rank' => 'integer',
        'backgroundColor' => 'integer',
        'emblemStyle' => 'integer',
        'emblemColor' => 'integer',
        'borderStyle' => 'integer',
        'borderColor' => 'integer'
    ];

    /**
     * Get team members
     */
    public function members()
    {
        return $this->belongsToMany(Character::class, 'arena_team_member', 'arenaTeamId', 'guid');
    }

    /**
     * Get team type name
     */
    public function getTypeNameAttribute()
    {
        $types = [
            2 => '2v2',
            3 => '3v3',
            5 => '5v5'
        ];
        
        return $types[$this->type] ?? 'Unknown';
    }
}
