<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get total number of users (accounts)
     */
    public function getTotalUsers()
    {
        try {
            return DB::connection('mysql_auth')
                ->table('account')
                ->count();
        } catch (\Exception $e) {
            \Log::error("Error getting total users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get last registered user
     */
    public function getLastRegisteredUser()
    {
        try {
            $user = DB::connection('mysql_auth')
                ->table('account')
                ->orderBy('joindate', 'desc')
                ->first();

            if ($user) {
                return $user->username ?? 'Unknown';
            }

            return 'No users found';
        } catch (\Exception $e) {
            \Log::error("Error getting last registered user: " . $e->getMessage());
            return 'Error retrieving data';
        }
    }

    /**
     * Get GM level for user
     */
    public function getGmLevel($accountId)
    {
        try {
            $gmLevel = DB::connection('mysql_auth')
                ->table('account_access')
                ->where('id', $accountId)
                ->value('gmlevel');

            return $gmLevel ?? 0;
        } catch (\Exception $e) {
            \Log::error("Error getting GM level for account {$accountId}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get user email from auth database
     */
    public function getAuthEmail($accountId)
    {
        try {
            $email = DB::connection('mysql_auth')
                ->table('account')
                ->where('id', $accountId)
                ->value('email');

            return $email ?? 'user@example.com';
        } catch (\Exception $e) {
            \Log::error("Error getting email for account {$accountId}: " . $e->getMessage());
            return 'user@example.com';
        }
    }

    /**
     * Get user points
     */
    public function getPoints($accountId)
    {
        return \App\Models\UserCurrency::getPoints($accountId);
    }

    /**
     * Get user tokens
     */
    public function getTokens($accountId)
    {
        return \App\Models\UserCurrency::getTokens($accountId);
    }

    /**
     * Get user avatar
     */
    public function getAvatar($accountId)
    {
        return \App\Models\UserCurrency::getAvatar($accountId);
    }

    /**
     * Get user role
     */
    public function getRole($accountId)
    {
        return \App\Models\UserCurrency::getRole($accountId);
    }

    /**
     * Check if user has admin access
     */
    public function isAdmin($accountId)
    {
        $gmLevel = $this->getGmLevel($accountId);
        $role = $this->getRole($accountId);
        
        return $gmLevel > 0 || in_array($role, ['admin', 'moderator']);
    }
}
