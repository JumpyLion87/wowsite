<?php

namespace App\Services;

class BladeDirectivesService
{
    /**
     * Отображает статус онлайн/оффлайн
     */
    public static function onlineStatus($isOnline)
    {
        $isOnline = (bool) $isOnline;
        
        if ($isOnline) {
            return '<span class="status-online" style="color: green;">● ' . __('admin_dashboard.online') . '</span>';
        } else {
            return '<span class="status-offline" style="color: red;">● ' . __('admin_dashboard.offline') . '</span>';
        }
    }

    /**
     * Отображает статус аккаунта (бан/разбан)
     */
    public static function accountStatus($params)
    {
        $isLocked = $params['isLocked'] ?? false;
        $banInfo = $params['banInfo'] ?? [];

        if ($isLocked) {
            $reason = $banInfo['reason'] ?? __('admin_dashboard.no_reason_provided');
            $until = isset($banInfo['until']) ? \Carbon\Carbon::parse($banInfo['until'])->format('Y-m-d H:i') : __('admin_dashboard.permanent');
            
            return '<span class="status-banned" style="color: red;">● ' . __('admin_dashboard.banned') . '</span>' .
                   '<br><small>' . __('admin_dashboard.reason') . ': ' . $reason . '</small>' .
                   '<br><small>' . __('admin_dashboard.until') . ': ' . $until . '</small>';
        } else {
            return '<span class="status-active" style="color: green;">● ' . __('admin_dashboard.active') . '</span>';
        }
    }
}
