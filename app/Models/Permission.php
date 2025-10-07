<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $connection = 'mysql'; // Указываем правильную базу данных

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category'
    ];

    /**
     * Роли, которые имеют это разрешение
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Получить все разрешения по категориям
     */
    public static function getByCategory()
    {
        return static::orderBy('category')->orderBy('name')->get()->groupBy('category');
    }

    /**
     * Создать разрешение
     */
    public static function createPermission($name, $displayName, $description = null, $category = 'general')
    {
        return static::create([
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
            'category' => $category
        ]);
    }
}