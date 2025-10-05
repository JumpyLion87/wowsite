<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopItem extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'shop_items';
    protected $primaryKey = 'item_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'category',
        'name',
        'description',
        'image',
        'point_cost',
        'token_cost',
        'stock',
        'entry',
        'gold_amount',
        'level_boost',
        'at_login_flags',
        'is_item'
    ];

    protected $casts = [
        'point_cost' => 'integer',
        'token_cost' => 'integer',
        'stock' => 'integer',
        'entry' => 'integer',
        'gold_amount' => 'integer',
        'level_boost' => 'integer',
        'at_login_flags' => 'integer',
        'is_item' => 'boolean'
    ];

    /**
     * Связь с ItemTemplate (если это предмет)
     */
    public function itemTemplate(): BelongsTo
    {
        return $this->belongsTo(ItemTemplate::class, 'entry', 'entry');
    }

    /**
     * Связь с покупками
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'item_id', 'item_id');
    }

    /**
     * Проверить, есть ли товар в наличии
     */
    public function isInStock(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }

    /**
     * Получить все товары по категории
     */
    public static function getByCategory(string $category = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::query();
        
        if ($category && $category !== 'All') {
            $query->where('category', $category);
        }
        
        return $query->orderBy('category')->orderBy('name')->get();
    }

    /**
     * Получить все товары сгруппированные по категориям
     */
    public static function getGroupedByCategory(): array
    {
        $items = self::orderBy('category')->orderBy('name')->get();
        
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item->category][] = $item;
        }
        
        return $grouped;
    }

    /**
     * Получить доступные категории
     */
    public static function getCategories(): array
    {
        return self::distinct()->pluck('category')->toArray();
    }
}
