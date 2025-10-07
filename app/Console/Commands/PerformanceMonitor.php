<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:monitor {--action=check : Action to perform (check, clear-cache, optimize)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor and optimize application performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->option('action');

        switch ($action) {
            case 'check':
                $this->checkPerformance();
                break;
            case 'clear-cache':
                $this->clearCache();
                break;
            case 'optimize':
                $this->optimize();
                break;
            default:
                $this->error('Invalid action. Use: check, clear-cache, or optimize');
                return 1;
        }

        return 0;
    }

    /**
     * Check performance metrics
     */
    private function checkPerformance()
    {
        $this->info('🔍 Checking Performance Metrics...');
        $this->newLine();

        // Check cache status
        $this->checkCacheStatus();

        // Check database performance
        $this->checkDatabasePerformance();

        // Check slow queries
        $this->checkSlowQueries();

        // Check memory usage
        $this->checkMemoryUsage();
    }

    /**
     * Check cache status
     */
    private function checkCacheStatus()
    {
        $this->info('📊 Cache Status:');
        
        $cacheKeys = [
            'latest_news',
            'last_registered_user',
            'admin_dashboard_stats',
            'shop_items_grouped',
            'ban_stats',
            'server_stats'
        ];

        foreach ($cacheKeys as $key) {
            $exists = Cache::has($key);
            $status = $exists ? '✅ Cached' : '❌ Not cached';
            $this->line("  {$key}: {$status}");
        }
        
        $this->newLine();
    }

    /**
     * Check database performance
     */
    private function checkDatabasePerformance()
    {
        $this->info('🗄️ Database Performance:');
        
        // Test query performance
        $start = microtime(true);
        
        // Test characters query
        $charactersCount = DB::connection('mysql_char')->table('characters')->count();
        $charactersTime = round((microtime(true) - $start) * 1000, 2);
        
        $start = microtime(true);
        
        // Test online players query
        $onlineCount = DB::connection('mysql_char')->table('characters')
            ->where('online', 1)
            ->count();
        $onlineTime = round((microtime(true) - $start) * 1000, 2);
        
        $start = microtime(true);
        
        // Test account query
        $accountsCount = DB::connection('mysql_auth')->table('account')->count();
        $accountsTime = round((microtime(true) - $start) * 1000, 2);

        $this->line("  Characters count ({$charactersCount}): {$charactersTime}ms");
        $this->line("  Online players ({$onlineCount}): {$onlineTime}ms");
        $this->line("  Accounts count ({$accountsCount}): {$accountsTime}ms");
        
        $this->newLine();
    }

    /**
     * Check for slow queries
     */
    private function checkSlowQueries()
    {
        $this->info('🐌 Slow Query Analysis:');
        
        try {
            // Check if slow query log is enabled
            $slowLogStatus = DB::select("SHOW VARIABLES LIKE 'slow_query_log'")[0]->Value ?? 'OFF';
            $slowLogFile = DB::select("SHOW VARIABLES LIKE 'slow_query_log_file'")[0]->Value ?? 'Not set';
            $longQueryTime = DB::select("SHOW VARIABLES LIKE 'long_query_time'")[0]->Value ?? '10';
            
            $this->line("  Slow query log: {$slowLogStatus}");
            $this->line("  Log file: {$slowLogFile}");
            $this->line("  Long query time: {$longQueryTime}s");
            
            if ($slowLogStatus === 'ON') {
                $this->line("  ✅ Slow query logging is enabled");
            } else {
                $this->line("  ⚠️ Slow query logging is disabled");
            }
            
        } catch (\Exception $e) {
            $this->line("  ❌ Could not check slow query settings: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Check memory usage
     */
    private function checkMemoryUsage()
    {
        $this->info('💾 Memory Usage:');
        
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        $this->line("  Current usage: " . $this->formatBytes($memoryUsage));
        $this->line("  Peak usage: " . $this->formatBytes($memoryPeak));
        $this->line("  Memory limit: {$memoryLimit}");
        
        $usagePercent = ($memoryUsage / $this->parseMemoryLimit($memoryLimit)) * 100;
        $status = $usagePercent > 80 ? '⚠️ High' : ($usagePercent > 60 ? '⚡ Medium' : '✅ Low');
        $this->line("  Usage status: {$status} ({$usagePercent}%)");
        
        $this->newLine();
    }

    /**
     * Clear all caches
     */
    private function clearCache()
    {
        $this->info('🧹 Clearing all caches...');
        
        try {
            Cache::flush();
            $this->info('✅ Application cache cleared');
            
            \Artisan::call('config:clear');
            $this->info('✅ Configuration cache cleared');
            
            \Artisan::call('route:clear');
            $this->info('✅ Route cache cleared');
            
            \Artisan::call('view:clear');
            $this->info('✅ View cache cleared');
            
            $this->info('🎉 All caches cleared successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error clearing caches: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application
     */
    private function optimize()
    {
        $this->info('⚡ Optimizing application...');
        
        try {
            \Artisan::call('config:cache');
            $this->info('✅ Configuration cached');
            
            \Artisan::call('route:cache');
            $this->info('✅ Routes cached');
            
            \Artisan::call('view:cache');
            $this->info('✅ Views cached');
            
            \Artisan::call('optimize');
            $this->info('✅ Application optimized');
            
            $this->info('🎉 Application optimization completed!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error optimizing application: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit($limit)
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $limit = (int) $limit;
        
        switch ($last) {
            case 'g':
                $limit *= 1024;
            case 'm':
                $limit *= 1024;
            case 'k':
                $limit *= 1024;
        }
        
        return $limit;
    }
}