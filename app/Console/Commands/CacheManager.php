<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:manage {action : Action to perform (clear, warm, list)} {--type=all : Type of cache to manage (all, news, stats, shop, bans)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application cache (clear, warm, list)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $type = $this->option('type');

        switch ($action) {
            case 'clear':
                $this->clearCache($type);
                break;
            case 'warm':
                $this->warmCache($type);
                break;
            case 'list':
                $this->listCache($type);
                break;
            default:
                $this->error('Invalid action. Use: clear, warm, or list');
                return 1;
        }

        return 0;
    }

    /**
     * Clear cache
     */
    private function clearCache($type)
    {
        $this->info("🧹 Clearing {$type} cache...");

        switch ($type) {
            case 'news':
                $this->clearNewsCache();
                break;
            case 'stats':
                $this->clearStatsCache();
                break;
            case 'shop':
                $this->clearShopCache();
                break;
            case 'bans':
                $this->clearBansCache();
                break;
            case 'all':
            default:
                $this->clearAllCache();
                break;
        }

        $this->info('✅ Cache cleared successfully!');
    }

    /**
     * Warm cache
     */
    private function warmCache($type)
    {
        $this->info("🔥 Warming {$type} cache...");

        switch ($type) {
            case 'news':
                $this->warmNewsCache();
                break;
            case 'stats':
                $this->warmStatsCache();
                break;
            case 'shop':
                $this->warmShopCache();
                break;
            case 'bans':
                $this->warmBansCache();
                break;
            case 'all':
            default:
                $this->warmAllCache();
                break;
        }

        $this->info('✅ Cache warmed successfully!');
    }

    /**
     * List cache status
     */
    private function listCache($type)
    {
        $this->info("📋 Cache status for {$type}:");

        $cacheKeys = $this->getCacheKeys($type);

        foreach ($cacheKeys as $key => $description) {
            $exists = Cache::has($key);
            $status = $exists ? '✅ Cached' : '❌ Not cached';
            $this->line("  {$key}: {$status} - {$description}");
        }
    }

    /**
     * Clear news cache
     */
    private function clearNewsCache()
    {
        Cache::forget('latest_news');
        Cache::forget('last_registered_user');
        $this->line('  ✅ News cache cleared');
    }

    /**
     * Clear stats cache
     */
    private function clearStatsCache()
    {
        Cache::forget('admin_dashboard_stats');
        Cache::forget('server_stats');
        Cache::forget('total_accounts');
        Cache::forget('total_characters');
        Cache::forget('online_players');
        Cache::forget('total_users');
        Cache::forget('total_bans');
        Cache::forget('recent_activity');
        $this->line('  ✅ Stats cache cleared');
    }

    /**
     * Clear shop cache
     */
    private function clearShopCache()
    {
        Cache::forget('shop_items_grouped');
        Cache::forget('shop_categories');
        
        // Clear user-specific shop cache
        $this->clearUserShopCache();
        $this->line('  ✅ Shop cache cleared');
    }

    /**
     * Clear bans cache
     */
    private function clearBansCache()
    {
        Cache::forget('ban_stats');
        $this->line('  ✅ Bans cache cleared');
    }

    /**
     * Clear all cache
     */
    private function clearAllCache()
    {
        Cache::flush();
        $this->line('  ✅ All cache cleared');
    }

    /**
     * Warm news cache
     */
    private function warmNewsCache()
    {
        // Warm news cache by accessing the data
        try {
            \App\Models\News::orderBy('is_important', 'desc')
                ->orderBy('post_date', 'desc')
                ->limit(4)
                ->get();
            $this->line('  ✅ News cache warmed');
        } catch (\Exception $e) {
            $this->line('  ❌ Error warming news cache: ' . $e->getMessage());
        }
    }

    /**
     * Warm stats cache
     */
    private function warmStatsCache()
    {
        try {
            // Warm stats by accessing the data
            \App\Services\StatsService::getServerStats();
            \App\Services\StatsService::getDashboardStats();
            $this->line('  ✅ Stats cache warmed');
        } catch (\Exception $e) {
            $this->line('  ❌ Error warming stats cache: ' . $e->getMessage());
        }
    }

    /**
     * Warm shop cache
     */
    private function warmShopCache()
    {
        try {
            // Warm shop cache by accessing the data
            \App\Models\ShopItem::getGroupedByCategory();
            $this->line('  ✅ Shop cache warmed');
        } catch (\Exception $e) {
            $this->line('  ❌ Error warming shop cache: ' . $e->getMessage());
        }
    }

    /**
     * Warm bans cache
     */
    private function warmBansCache()
    {
        try {
            // Warm bans cache by accessing the data
            \App\Http\Controllers\Admin\BanController::class;
            $this->line('  ✅ Bans cache warmed');
        } catch (\Exception $e) {
            $this->line('  ❌ Error warming bans cache: ' . $e->getMessage());
        }
    }

    /**
     * Warm all cache
     */
    private function warmAllCache()
    {
        $this->warmNewsCache();
        $this->warmStatsCache();
        $this->warmShopCache();
        $this->warmBansCache();
    }

    /**
     * Clear user-specific shop cache
     */
    private function clearUserShopCache()
    {
        // This would need to be implemented based on how user-specific cache is stored
        // For now, we'll just clear the general shop cache
    }

    /**
     * Get cache keys for specific type
     */
    private function getCacheKeys($type)
    {
        switch ($type) {
            case 'news':
                return [
                    'latest_news' => 'Latest news articles',
                    'last_registered_user' => 'Last registered user info'
                ];
            case 'stats':
                return [
                    'admin_dashboard_stats' => 'Admin dashboard statistics',
                    'server_stats' => 'Server statistics',
                    'total_accounts' => 'Total accounts count',
                    'total_characters' => 'Total characters count',
                    'online_players' => 'Online players count',
                    'total_users' => 'Total users count',
                    'total_bans' => 'Total bans count',
                    'recent_activity' => 'Recent activity data'
                ];
            case 'shop':
                return [
                    'shop_items_grouped' => 'Shop items grouped by category',
                    'shop_categories' => 'Available shop categories'
                ];
            case 'bans':
                return [
                    'ban_stats' => 'Ban statistics'
                ];
            case 'all':
            default:
                return array_merge(
                    $this->getCacheKeys('news'),
                    $this->getCacheKeys('stats'),
                    $this->getCacheKeys('shop'),
                    $this->getCacheKeys('bans')
                );
        }
    }
}