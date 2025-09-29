<?php

namespace App\Services;

use App\Models\Realm;
use App\Models\User;
use App\Models\Character;
use Illuminate\Support\Facades\Cache;

class ServerStatusService
{
    /**
     * Get server status data with caching
     */
    public function getServerStatusData()
    {
        return Cache::remember('server_status_data', 60, function () {
            $realmModel = new Realm();
            $userModel = new User();
            $characterModel = new Character();

            return [
                'realmlist' => $realmModel->getRealmlist(),
                'serverStatus' => $realmModel->getFullServerStatus(),
                'totalAccounts' => $userModel->getTotalUsers(),
                'totalCharacters' => $characterModel->getTotalCharacters(),
                'lastRegisteredUser' => $userModel->getLastRegisteredUser()
            ];
        });
    }

    /**
     * Get server statistics for dashboard
     */
    public function getServerStatistics()
    {
        return Cache::remember('server_statistics', 300, function () {
            $characterModel = new Character();
            $userModel = new User();

            return [
                'totalAccounts' => $userModel->getTotalUsers(),
                'totalCharacters' => $characterModel->getTotalCharacters(),
                'onlineCharacters' => $characterModel->getOnlineCharactersCount(),
                'classDistribution' => $characterModel->getClassDistribution(),
                'raceDistribution' => $characterModel->getRaceDistribution(),
                'levelDistribution' => $characterModel->getLevelDistribution(),
                'recentCharacters' => $characterModel->getRecentlyCreated(5)
            ];
        });
    }

    /**
     * Clear server status cache
     */
    public function clearCache()
    {
        Cache::forget('server_status_data');
        Cache::forget('server_statistics');
    }

    /**
     * Get individual realm status
     */
    public function getRealmStatus($realmId)
    {
        $realmModel = new Realm();
        $realm = Realm::find($realmId);
        
        if (!$realm) {
            return null;
        }

        return $realmModel->checkRealmStatus($realm);
    }

    /**
     * Get all realms with detailed status
     */
    public function getAllRealmsWithStatus()
    {
        $realms = Realm::all();
        $realmModel = new Realm();
        $result = [];

        foreach ($realms as $realm) {
            $status = $realmModel->checkRealmStatus($realm);
            $result[] = [
                'realm' => $realm,
                'status' => $status,
                'logo' => $realmModel->getRealmLogo($realm->icon)
            ];
        }

        return $result;
    }
}