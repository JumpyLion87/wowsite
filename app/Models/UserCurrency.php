<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserCurrency extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'user_currencies';
    protected $primaryKey = 'account_id';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'username',
        'email',
        'points',
        'tokens',
        'avatar',
        'role',
        'last_password_change',
    ];

    /**
     * Get user currency by account ID
     */
    public static function getByAccountId($accountId)
    {
        return static::where('account_id', $accountId)->first();
    }

    /**
     * Get user points
     */
    public static function getPoints($accountId)
    {
        $currency = static::getByAccountId($accountId);
        return $currency ? $currency->points : 0;
    }

    /**
     * Get user tokens
     */
    public static function getTokens($accountId)
    {
        $currency = static::getByAccountId($accountId);
        return $currency ? $currency->tokens : 0;
    }

    /**
     * Get user avatar
     */
    public static function getAvatar($accountId)
    {
        $currency = static::getByAccountId($accountId);
        if ($currency && $currency->avatar) {
            // Check if avatar exists in profile_avatars table
            $avatarExists = DB::connection('mysql')
                ->table('profile_avatars')
                ->where('filename', $currency->avatar)
                ->where('active', 1)
                ->exists();
            
            if ($avatarExists) {
                return $currency->avatar;
            }
        }
        return 'user.jpg'; // Default avatar
    }

    /**
     * Get user role
     */
    public static function getRole($accountId)
    {
        $currency = static::getByAccountId($accountId);
        return $currency ? $currency->role : 'player';
    }

    /**
     * Check if user has enough currency for purchase
     */
    public function hasEnoughCurrency(int $pointCost, int $tokenCost): bool
    {
        return $this->points >= $pointCost && $this->tokens >= $tokenCost;
    }

    /**
     * Deduct currency after purchase
     */
    public function deductCurrency(int $pointCost, int $tokenCost): bool
    {
        if (!$this->hasEnoughCurrency($pointCost, $tokenCost)) {
            return false;
        }

        $this->points -= $pointCost;
        $this->tokens -= $tokenCost;
        
        return $this->save();
    }

    /**
     * Add currency to user
     */
    public function addCurrency(int $points = 0, int $tokens = 0): bool
    {
        $this->points += $points;
        $this->tokens += $tokens;
        
        return $this->save();
    }

    /**
     * Get user balance as array
     */
    public function getBalance(): array
    {
        return [
            'points' => $this->points,
            'tokens' => $this->tokens
        ];
    }
}