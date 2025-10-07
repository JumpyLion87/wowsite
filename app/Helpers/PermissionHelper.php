<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\UserCurrency;

class PermissionHelper
{
    /**
     * Проверить, есть ли у пользователя разрешение
     */
    public static function hasPermission($accountId, $permission)
    {
        $user = User::find($accountId);
        if (!$user) {
            return false;
        }

        // Проверяем через старую систему ролей
        $role = UserCurrency::getRole($accountId);
        if ($role === 'admin') {
            return true; // Админы имеют все права
        }

        // Проверяем через новую систему ролей
        try {
            $roles = $user->roles;
            foreach ($roles as $role) {
                if ($role->hasPermission($permission)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // Если не удается получить роли, используем старую систему
        }

        return false;
    }

    /**
     * Проверить, является ли пользователь администратором
     */
    public static function isAdmin($accountId)
    {
        $role = UserCurrency::getRole($accountId);
        return $role === 'admin';
    }

    /**
     * Проверить, является ли пользователь модератором
     */
    public static function isModerator($accountId)
    {
        $role = UserCurrency::getRole($accountId);
        return $role === 'moderator';
    }

    /**
     * Проверить, является ли пользователь администратором или модератором
     */
    public static function isAdminOrModerator($accountId)
    {
        $role = UserCurrency::getRole($accountId);
        return in_array($role, ['admin', 'moderator']);
    }
}
