<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SRP6Service;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    //Указываем таблицу для модели (по умолчанию)
    protected $table = 'account';

    //Подключение к БД 'auth' вместо default
    protected $connection = 'mysql_auth';

    /**
     * Атрибуты, разрещенные для массового заполнения
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'salt',
        'verifier',
        'joindate',
        'last_login',
        'expansion'
    ];

    /**
     * Атрибуты, которые должны быть скрыты
     *
     * @var list<string>
     */
    protected $hidden = [
        'salt',
        'verifier',
        'remember_token',
    ];

    /**
     * Получить роль пользователя
     */
    public function getRoleAttribute()
    {
        return $this->userCurrency?->role ?? 'player';
    }

    public function userCurrency()
    {
        return $this->hasOne(UserCurrency::class, 'account_id', 'id');
    }

    /**
     * Получить уровень GM
     */
    public function getGmLevelAttribute()
    {
        return $this->accountAccess?->gmlevel ?? 0;
    }

    /**
     * Отновение к таблице 'account_access'
     */
    public function accountAccess()
    {
        return $this->hasOne(AccountAccess::class, 'id', 'id');
    }

    /**
     * Аутентификация через SRP6
     */
    public function authenticateWithSRP6(string $password, ?string $username = null): bool
    {
        $srp6 = app(SRP6Service::class);
        $usernameToUse = $username ?? $this->username;
        return $srp6->verifyPassword($usernameToUse, $password, $this->salt, $this->verifier);
    }

    /**
     * Создание пользователя с SRP6
     */
    public static function createWithSRP6(array $userData)
    {
        $srp6 = app(SRP6Service::class);
        $userData['salt'] = $srp6->generateSalt();
        $userData['verifier'] = $srp6->calculateVerifier(
            $userData['username'],
            $userData['password'],
            $userData['salt']
        );

        unset($userData['password']); // Пароль больше не нужен

        return self::create($userData);
    }

    /**
     * Обновление пароля через SRP6
     */
    public function updatePasswordWithSRP6(string $newPassword): bool
    {
        $srp6 = app(SRP6Service::class);
        $this->salt = $srp6->generateSalt();
        $this->verifier = $srp6->calculateVerifier(
            $this->username,
            $newPassword,
            $this->salt
        );
        return $this->save();
    }

    /**
     * Проверка существония пользователя по имени
     */
    public static function usernameExists(string $username): bool
    {
        return self::where('username', $username)->exists();
    }

    /**
     * Проверка существования email
     */
    public static function emailExists(string $email): bool
    {
        return self::where('email', $email)->exists();
    }

    /**
     * Обвновление времени последней авторизации
     */
    public function updateLastLogin(): bool
    {
        return $this->update(['last_login' => now()]);
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime'
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
    public static function getLastRegisteredUser(): string
    {
        try {
            DB::connection('mysql_auth')->getPDO();
            // Используем Eloquent для запроса к таблице account
            $user = self::orderBy('joindate', 'desc')->first();

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
    public static function getGmLevel($accountId)
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
    public static function getAuthEmail($accountId)
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
    public static function getPoints($accountId)
    {
        return \App\Models\UserCurrency::getPoints($accountId);
    }

    /**
     * Get user tokens
     */
    public static function getTokens($accountId)
    {
        return \App\Models\UserCurrency::getTokens($accountId);
    }

    /**
     * Get user avatar
     */
    public static function getAvatar($accountId)
    {
        return \App\Models\UserCurrency::getAvatar($accountId);
    }

    /**
     * Get user role
     */
    public static function getRole($accountId)
    {
        return \App\Models\UserCurrency::getRole($accountId);
    }

    /**
     * Check if user has admin access
     */
    public static function isAdmin($accountId)
    {
        $gmLevel = self::getGmLevel($accountId);
        $role = self::getRole($accountId);
        
        return $gmLevel > 0 || in_array($role, ['admin', 'moderator']);
    }
}
