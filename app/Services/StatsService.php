<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatsService
{
    /**
     * Получить общую статистику сервера с кэшированием
     */
    public function getServerStats()
    {
        return Cache::remember('server_stats', 300, function () {
            return [
                'totalAccounts' => $this->getTotalAccounts(),
                'totalCharacters' => $this->getTotalCharacters(),
                'onlinePlayers' => $this->getOnlinePlayers(),
                'totalUsers' => $this->getTotalUsers(),
                'totalBans' => $this->getTotalBans(),
            ];
        });
    }

    /**
     * Получить статистику для дашборда админа
     */
    public function getDashboardStats()
    {
        return Cache::remember('dashboard_stats', 300, function () {
            return [
                'accounts' => $this->getTotalAccounts(),
                'characters' => $this->getTotalCharacters(),
                'online' => $this->getOnlinePlayers(),
                'users' => $this->getTotalUsers(),
                'bans' => $this->getTotalBans(),
                'recentActivity' => $this->getRecentActivity(),
            ];
        });
    }

    /**
     * Получить количество аккаунтов
     */
    public function getTotalAccounts(): int
    {
        return Cache::remember('total_accounts', 600, function () {
            return DB::connection('mysql_auth')->table('account')->count();
        });
    }

    /**
     * Получить количество персонажей
     */
    public function getTotalCharacters(): int
    {
        return Cache::remember('total_characters', 600, function () {
            return DB::connection('mysql_char')->table('characters')->count();
        });
    }

    /**
     * Получить количество онлайн игроков
     */
    public function getOnlinePlayers(): int
    {
        return Cache::remember('online_players', 60, function () {
            return DB::connection('mysql_char')->table('characters')
                ->where('online', 1)
                ->count();
        });
    }

    /**
     * Получить количество пользователей сайта
     */
    public function getTotalUsers(): int
    {
        return Cache::remember('total_users', 600, function () {
            return DB::connection('mysql')->table('user_currencies')->count();
        });
    }

    /**
     * Получить количество активных банов
     */
    public function getTotalBans(): int
    {
        return Cache::remember('total_bans', 300, function () {
            return DB::connection('mysql_auth')->table('account_banned')
                ->where('active', 1)
                ->count();
        });
    }

    /**
     * Получить недавнюю активность
     */
    public function getRecentActivity()
    {
        return Cache::remember('recent_activity', 300, function () {
            return [
                'newAccounts' => $this->getNewAccountsToday(),
                'newCharacters' => $this->getNewCharactersToday(),
                'recentPurchases' => $this->getRecentPurchases(),
            ];
        });
    }

    /**
     * Получить новых аккаунтов за сегодня
     */
    private function getNewAccountsToday(): int
    {
        return DB::connection('mysql_auth')->table('account')
            ->whereDate('joindate', today())
            ->count();
    }

    /**
     * Получить новых персонажей за сегодня
     */
    private function getNewCharactersToday(): int
    {
        return DB::connection('mysql_char')->table('characters')
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Получить недавние покупки
     */
    private function getRecentPurchases()
    {
        return DB::connection('mysql')->table('purchases')
            ->join('user_currencies', 'purchases.account_id', '=', 'user_currencies.account_id')
            ->join('shop_items', 'purchases.item_id', '=', 'shop_items.item_id')
            ->select('purchases.*', 'user_currencies.username', 'shop_items.name as item_name')
            ->orderBy('purchases.created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Очистить кэш статистики
     */
    public function clearStatsCache()
    {
        Cache::forget('server_stats');
        Cache::forget('dashboard_stats');
        Cache::forget('total_accounts');
        Cache::forget('total_characters');
        Cache::forget('online_players');
        Cache::forget('total_users');
        Cache::forget('total_bans');
        Cache::forget('recent_activity');
    }

    /**
     * Очистить кэш онлайн игроков (вызывается при изменении статуса)
     */
    public function clearOnlinePlayersCache()
    {
        Cache::forget('online_players');
    }

    /**
     * Очистить кэш банов (вызывается при изменении банов)
     */
    public function clearBansCache()
    {
        Cache::forget('total_bans');
    }
}
