<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class CacheOptimizer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:optimize {action : Action to perform (check, analyze, recommend, setup)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize cache configuration for production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'check':
                $this->checkCacheStatus();
                break;
            case 'analyze':
                $this->analyzeCacheUsage();
                break;
            case 'recommend':
                $this->recommendOptimizations();
                break;
            case 'setup':
                $this->setupOptimalCache();
                break;
            default:
                $this->error('Invalid action. Use: check, analyze, recommend, or setup');
                return 1;
        }

        return 0;
    }

    /**
     * Check current cache status
     */
    private function checkCacheStatus()
    {
        $this->info('🔍 Checking Cache Status...');
        $this->newLine();

        // Current configuration
        $currentDriver = config('cache.default');
        $this->info('📊 Current Configuration:');
        $this->line("  Driver: {$currentDriver}");
        $this->line("  Prefix: " . config('cache.prefix'));

        // Check available drivers
        $this->info('🔧 Available Drivers:');
        $availableDrivers = $this->getAvailableDrivers();
        foreach ($availableDrivers as $driver => $status) {
            $icon = $status ? '✅' : '❌';
            $this->line("  {$icon} {$driver}");
        }

        // Test current cache
        $this->info('🧪 Cache Test:');
        $this->testCurrentCache();

        // Check cache directory
        if ($currentDriver === 'file') {
            $this->checkFileCacheDirectory();
        }
    }

    /**
     * Analyze cache usage
     */
    private function analyzeCacheUsage()
    {
        $this->info('📊 Analyzing Cache Usage...');
        $this->newLine();

        // Analyze cache keys
        $this->analyzeCacheKeys();

        // Analyze cache performance
        $this->analyzeCachePerformance();

        // Analyze cache size
        $this->analyzeCacheSize();
    }

    /**
     * Recommend optimizations
     */
    private function recommendOptimizations()
    {
        $this->info('💡 Cache Optimization Recommendations...');
        $this->newLine();

        $currentDriver = config('cache.default');
        $availableDrivers = $this->getAvailableDrivers();

        $this->info('🎯 Driver Recommendations:');
        
        if ($currentDriver === 'file' && $availableDrivers['redis']) {
            $this->line('  ✅ Redis is available - recommended for production');
            $this->line('     Benefits: Better performance, shared across processes');
        } elseif ($currentDriver === 'file' && $availableDrivers['memcached']) {
            $this->line('  ✅ Memcached is available - good for production');
            $this->line('     Benefits: Better performance than file cache');
        } elseif ($currentDriver === 'file') {
            $this->line('  ⚠️ File cache is acceptable for development');
            $this->line('     Consider Redis or Memcached for production');
        } else {
            $this->line('  ✅ Current driver is optimal');
        }

        $this->newLine();
        $this->info('⚙️ Configuration Recommendations:');
        
        // Check cache prefix
        $prefix = config('cache.prefix');
        if (empty($prefix)) {
            $this->line('  ⚠️ Consider setting a cache prefix for multi-tenant environments');
        } else {
            $this->line('  ✅ Cache prefix is configured');
        }

        // Check cache TTL
        $this->line('  💡 Consider appropriate TTL values:');
        $this->line('     - News: 30 minutes (1800s)');
        $this->line('     - Stats: 5 minutes (300s)');
        $this->line('     - Shop items: 1 hour (3600s)');
        $this->line('     - User data: 10 minutes (600s)');

        $this->newLine();
        $this->info('🚀 Performance Recommendations:');
        $this->line('  1. Use Redis for production environments');
        $this->line('  2. Set appropriate cache TTL values');
        $this->line('  3. Implement cache warming strategies');
        $this->line('  4. Monitor cache hit rates');
        $this->line('  5. Use cache tags for grouped invalidation');
    }

    /**
     * Setup optimal cache configuration
     */
    private function setupOptimalCache()
    {
        $this->info('🚀 Setting up Optimal Cache Configuration...');
        $this->newLine();

        $availableDrivers = $this->getAvailableDrivers();
        $currentDriver = config('cache.default');

        // Determine best driver
        $recommendedDriver = $this->getRecommendedDriver($availableDrivers);
        
        $this->info("📊 Current driver: {$currentDriver}");
        $this->info("🎯 Recommended driver: {$recommendedDriver}");

        if ($currentDriver === $recommendedDriver) {
            $this->info('✅ Current configuration is already optimal');
            return;
        }

        if ($this->confirm("Switch from {$currentDriver} to {$recommendedDriver}?")) {
            $this->switchCacheDriver($recommendedDriver);
        } else {
            $this->info('Configuration unchanged');
        }
    }

    /**
     * Get available cache drivers
     */
    private function getAvailableDrivers()
    {
        $drivers = [];

        // Check file driver
        $drivers['file'] = true;

        // Check database driver
        try {
            \DB::connection()->getPdo();
            $drivers['database'] = true;
        } catch (\Exception $e) {
            $drivers['database'] = false;
        }

        // Check Redis driver
        try {
            if (extension_loaded('redis')) {
                $drivers['redis'] = true;
            } else {
                $drivers['redis'] = false;
            }
        } catch (\Exception $e) {
            $drivers['redis'] = false;
        }

        // Check Memcached driver
        try {
            if (extension_loaded('memcached')) {
                $drivers['memcached'] = true;
            } else {
                $drivers['memcached'] = false;
            }
        } catch (\Exception $e) {
            $drivers['memcached'] = false;
        }

        return $drivers;
    }

    /**
     * Test current cache
     */
    private function testCurrentCache()
    {
        try {
            $testKey = 'cache_test_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);
            
            // Test write
            $start = microtime(true);
            Cache::put($testKey, $testValue, 60);
            $writeTime = round((microtime(true) - $start) * 1000, 2);
            
            // Test read
            $start = microtime(true);
            $retrievedValue = Cache::get($testKey);
            $readTime = round((microtime(true) - $start) * 1000, 2);
            
            if ($retrievedValue === $testValue) {
                $this->line("  ✅ Read/Write test successful");
                $this->line("  📝 Write time: {$writeTime}ms");
                $this->line("  📖 Read time: {$readTime}ms");
            } else {
                $this->line("  ❌ Read/Write test failed");
            }
            
            Cache::forget($testKey);
            
        } catch (\Exception $e) {
            $this->line("  ❌ Cache test failed: " . $e->getMessage());
        }
    }

    /**
     * Check file cache directory
     */
    private function checkFileCacheDirectory()
    {
        $cachePath = storage_path('framework/cache/data');
        
        if (File::exists($cachePath)) {
            $files = File::files($cachePath);
            $size = 0;
            
            foreach ($files as $file) {
                $size += $file->getSize();
            }
            
            $this->line("  📁 Cache directory: {$cachePath}");
            $this->line("  📊 Cache files: " . count($files));
            $this->line("  💾 Cache size: " . $this->formatBytes($size));
        } else {
            $this->line("  ❌ Cache directory not found: {$cachePath}");
        }
    }

    /**
     * Analyze cache keys
     */
    private function analyzeCacheKeys()
    {
        $this->info('🔑 Cache Keys Analysis:');
        
        $cacheKeys = [
            'latest_news' => 'News articles',
            'last_registered_user' => 'User registration data',
            'admin_dashboard_stats' => 'Admin statistics',
            'shop_items_grouped' => 'Shop items',
            'ban_stats' => 'Ban statistics',
            'server_stats' => 'Server statistics'
        ];

        foreach ($cacheKeys as $key => $description) {
            $exists = Cache::has($key);
            $status = $exists ? '✅ Cached' : '❌ Not cached';
            $this->line("  {$key}: {$status} - {$description}");
        }
    }

    /**
     * Analyze cache performance
     */
    private function analyzeCachePerformance()
    {
        $this->info('⚡ Cache Performance Analysis:');
        
        // Test multiple operations
        $operations = 100;
        $start = microtime(true);
        
        for ($i = 0; $i < $operations; $i++) {
            $key = "perf_test_{$i}";
            $value = "value_{$i}";
            
            Cache::put($key, $value, 60);
            Cache::get($key);
        }
        
        $totalTime = round((microtime(true) - $start) * 1000, 2);
        $avgTime = round($totalTime / ($operations * 2), 3);
        
        $this->line("  📊 {$operations} write/read operations: {$totalTime}ms");
        $this->line("  📈 Average per operation: {$avgTime}ms");
        
        // Cleanup
        for ($i = 0; $i < $operations; $i++) {
            Cache::forget("perf_test_{$i}");
        }
    }

    /**
     * Analyze cache size
     */
    private function analyzeCacheSize()
    {
        $this->info('💾 Cache Size Analysis:');
        
        $currentDriver = config('cache.default');
        
        if ($currentDriver === 'file') {
            $this->analyzeFileCacheSize();
        } else {
            $this->line("  📊 Cache size analysis not available for {$currentDriver} driver");
        }
    }

    /**
     * Analyze file cache size
     */
    private function analyzeFileCacheSize()
    {
        $cachePath = storage_path('framework/cache/data');
        
        if (File::exists($cachePath)) {
            $files = File::allFiles($cachePath);
            $totalSize = 0;
            $fileCount = count($files);
            
            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }
            
            $this->line("  📁 Total files: {$fileCount}");
            $this->line("  💾 Total size: " . $this->formatBytes($totalSize));
            $this->line("  📊 Average file size: " . $this->formatBytes($totalSize / max($fileCount, 1)));
        }
    }

    /**
     * Get recommended driver
     */
    private function getRecommendedDriver($availableDrivers)
    {
        if ($availableDrivers['redis']) {
            return 'redis';
        } elseif ($availableDrivers['memcached']) {
            return 'memcached';
        } elseif ($availableDrivers['database']) {
            return 'database';
        } else {
            return 'file';
        }
    }

    /**
     * Switch cache driver
     */
    private function switchCacheDriver($driver)
    {
        $this->info("🔄 Switching to {$driver} driver...");
        
        // Update configuration
        Config::set('cache.default', $driver);
        
        // Test new configuration
        $this->testCurrentCache();
        
        $this->info("✅ Switched to {$driver} driver");
        $this->warn("⚠️ Remember to update your .env file with CACHE_STORE={$driver}");
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
}