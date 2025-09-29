<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CharacterInventory extends Model
{
    use HasFactory;

    protected $connection = 'mysql_char';
    protected $table = 'character_inventory';
    public $timestamps = false;

    protected $fillable = [
        'guid', 'bag', 'slot', 'item'
    ];

    protected $casts = [
        'guid' => 'integer',
        'bag' => 'integer',
        'slot' => 'integer',
        'item' => 'integer'
    ];

    /**
     * Get the character that owns the inventory
     */
    public function character()
    {
        return $this->belongsTo(Character::class, 'guid', 'guid');
    }

    /**
     * Get the item instance
     */
    public function itemInstance()
    {
        return $this->belongsTo(ItemInstance::class, 'item', 'guid');
    }
}
