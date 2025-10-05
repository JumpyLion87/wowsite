<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PendingAccount extends Model
{
    protected $connection = 'mysql';
    protected $table = 'pending_accounts';
    
    public $timestamps = false;
    
    protected $fillable = [
        'username',
        'email',
        'salt',
        'verifier',
        'token',
        'activated',
        'created_at',
        'expires_at'
    ];
    
    protected $casts = [
        'activated' => 'boolean',
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
    
    /**
     * Find pending account by token
     */
    public static function findByToken($token)
    {
        return static::where('token', $token)
                    ->where('activated', false)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->first();
    }
    
    /**
     * Check if account is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
    
    /**
     * Mark as activated
     */
    public function markAsActivated()
    {
        $this->update(['activated' => true]);
    }
    
    /**
     * Create pending account with expiration
     */
    public static function createPending($username, $email, $salt, $verifier, $token, $expirationHours = 24)
    {
        return static::create([
            'username' => $username,
            'email' => $email,
            'salt' => $salt,
            'verifier' => $verifier,
            'token' => $token,
            'activated' => false,
            'created_at' => now(),
            'expires_at' => now()->addHours($expirationHours),
        ]);
    }
}