<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemInstance extends Model
{
    use HasFactory;

    protected $connection = 'mysql_char';
    protected $table = 'item_instance';
    protected $primaryKey = 'guid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'guid', 'itemEntry', 'owner_guid', 'creatorGuid', 'giftCreatorGuid',
        'count', 'duration', 'charges', 'flags', 'enchantments', 'randomPropertyId',
        'durability', 'playedTime', 'text'
    ];

    protected $casts = [
        'guid' => 'integer',
        'itemEntry' => 'integer',
        'owner_guid' => 'integer',
        'creatorGuid' => 'integer',
        'giftCreatorGuid' => 'integer',
        'count' => 'integer',
        'duration' => 'integer',
        'charges' => 'integer',
        'flags' => 'integer',
        'randomPropertyId' => 'integer',
        'durability' => 'integer',
        'playedTime' => 'integer'
    ];

    /**
     * Get the item template
     */
    public function itemTemplate()
    {
        return $this->belongsTo(ItemTemplate::class, 'itemEntry', 'entry');
    }
}
