<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Realm extends Model
{
    use HasFactory;

    protected $connection = 'mysql_auth';
    protected $table = 'realmlist';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'address',
        'port',
        'icon',
        'timezone',
        'allowedSecurityLevel',
        'population',
        'gamebuild'
    ];

    /**
     * Get all realms with their status
     */
    public function getRealmlist()
    {
        $realms = self::all();
        $serverStatus = [];

        foreach ($realms as $realm) {
            $status = $this->checkRealmStatus($realm);
            $serverStatus[] = array_merge( [
                'id' => $realm->id,
                'name' => $realm->name ?? 'Unknown Realm',
                'address' => $realm->address,
                'port' => $realm->port,
                'icon' => $realm->icon,               
                'timezone' => $realm->timezone,
                'population' => $realm->population,
            ], $status);
        }

        return ['realmlist' => $serverStatus];
    }

    /**
     * Get full server status for all realms
     */
    public function getFullServerStatus()
    {
        $realms = self::all();
        $serverStatus = [];

        foreach ($realms as $realm) {
            $serverStatus[] = $this->checkRealmStatus($realm);
        }

        return $serverStatus;
    }

    /**
     * Check status of a single realm
     */
    private function checkRealmStatus($realm)
    {
        // Simple socket check for realm server status
        $timeout = 2;
        $socket = @fsockopen($realm->address, $realm->port, $errno, $errstr, $timeout);

        if ($socket) {
            fclose($socket);
            $onlinePlayers = $this->getOnlinePlayersCount($realm->id);
            $uptime = $this->getServerUptime($realm->id);

            return [
                'online' => true,
                'online_players' => $onlinePlayers,
                'uptime' => $uptime
            ];
        }

        return [
            'online' => false,
            'online_players' => 0,
            'uptime' => '0'
        ];
    }

    /**
     * Get online players count for a realm
     */
    private function getOnlinePlayersCount()
    {
        try {
            $count = DB::connection('mysql_char')
                ->table('characters')
                ->where('online', '1')
                ->count();

            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get server uptime (simplified)
     */
    private function getServerUptime($realmId)
    {
        try {
            $uptime = DB::connection('mysql_auth')
                ->table('uptime')
                ->where('realmid', $realmId)
                ->orderBy('starttime', 'desc')
                ->first();

            if ($uptime && $uptime->uptime > 0) {
                // Используем значение uptime из таблицы (в секундах)
                return $this->formatUptime($uptime->uptime);
            } elseif ($uptime) {
                // Если uptime = 0, но запись существует, вычисляем разницу вручную
                $start = $uptime->starttime;
                $now = time();
                $diff = $now - $start;
                
                return $this->formatUptime($diff);
            }
        } catch (\Exception $e) {
            \Log::error("Error getting server uptime: " . $e->getMessage());
        }

        return '0';
    }

    /**
     * Format uptime seconds to human readable with localization
     */
    private function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        
        if ($days > 0) {
            $parts[] = $days . ' ' . trans_choice('home.server_status.days', $days);
        }
        if ($hours > 0) {
            $parts[] = $hours . ' ' . trans_choice('home.server_status.hours', $hours);
        }
        if ($minutes > 0 || empty($parts)) {
            $parts[] = $minutes . ' ' . trans_choice('home.server_status.minutes', $minutes);
        }

        return implode(', ', $parts);
    }

 
}
