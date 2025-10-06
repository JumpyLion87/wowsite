<?php

if (!function_exists('getOnlineStatus')) {
    function getOnlineStatus($isOnline)
    {
        return $isOnline ? '<span class="status-online">Online</span>' : '<span class="status-offline">Offline</span>';
    }
}

if (!function_exists('getAccountStatus')) {
    function getAccountStatus($isLocked, $banInfo)
    {
        if ($isLocked) {
            return '<span class="status-locked">Locked</span>';
        }

        if (!empty($banInfo)) {
            return '<span class="status-banned">Banned</span>';
        }

        return '<span class="status-normal">Normal</span>';
    }
}
