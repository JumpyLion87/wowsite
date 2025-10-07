<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color'
    ];

    /**
     * Разрешения роли
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Пользователи с этой ролью
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'account_id');
    }

    /**
     * Проверить, есть ли у роли разрешение
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('permissions.name', $permission)->exists();
        }
        
        return $this->permissions()->where('permissions.id', $permission->id)->exists();
    }

    /**
     * Дать разрешение роли
     */
    public function givePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }
        
        if ($permission && !$this->hasPermission($permission)) {
            $this->permissions()->attach($permission);
        }
    }

    /**
     * Отозвать разрешение у роли
     */
    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }
        
        if ($permission) {
            $this->permissions()->detach($permission);
        }
    }

    /**
     * Создать роль
     */
    public static function createRole($name, $displayName, $description = null, $color = '#6c757d')
    {
        return static::create([
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
            'color' => $color
        ]);
    }
}