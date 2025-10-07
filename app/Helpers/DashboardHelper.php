<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class DashboardHelper
{
    /**
     * Получить правильный маршрут дашборда в зависимости от роли пользователя
     */
    public static function getDashboardRoute()
    {
        if (!Auth::check()) {
            return route('home');
        }

        $user = Auth::user();

        if ($user->isAdministrator()) {
            return route('admin.dashboard');
        } elseif ($user->isModerator()) {
            return route('moderator.dashboard');
        }

        return route('home');
    }

    /**
     * Получить правильное название дашборда в зависимости от роли пользователя
     */
    public static function getDashboardTitle()
    {
        if (!Auth::check()) {
            return __('nav.home');
        }

        $user = Auth::user();

        if ($user->isAdministrator()) {
            return __('nav.admin_panel');
        } elseif ($user->isModerator()) {
            return __('nav.moderator_panel');
        }

        return __('nav.home');
    }

    /**
     * Получить правильную иконку дашборда в зависимости от роли пользователя
     */
    public static function getDashboardIcon()
    {
        if (!Auth::check()) {
            return 'fas fa-home';
        }

        $user = Auth::user();

        if ($user->isAdministrator()) {
            return 'fas fa-cogs';
        } elseif ($user->isModerator()) {
            return 'fas fa-user-shield';
        }

        return 'fas fa-home';
    }
}
