<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'purchases';
    protected $primaryKey = 'purchase_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'item_id',
        'point_cost',
        'token_cost',
        'purchase_date'
    ];

    protected $casts = [
        'account_id' => 'integer',
        'item_id' => 'integer',
        'point_cost' => 'integer',
        'token_cost' => 'integer',
        'purchase_date' => 'datetime'
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_id', 'id');
    }

    /**
     * Связь с товаром
     */
    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class, 'item_id', 'item_id');
    }

    /**
     * Получить покупки пользователя
     */
    public static function getUserPurchases(int $accountId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('account_id', $accountId)
            ->with('shopItem')
            ->orderBy('purchase_date', 'desc')
            ->get();
    }

    /**
     * Создать новую покупку
     */
    public static function createPurchase(int $accountId, int $itemId, int $pointCost, int $tokenCost): self
    {
        return self::create([
            'account_id' => $accountId,
            'item_id' => $itemId,
            'point_cost' => $pointCost,
            'token_cost' => $tokenCost,
            'purchase_date' => now()
        ]);
    }
}
