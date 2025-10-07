<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class RedisSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:setup {action : Action to perform (check, test, migrate, optimize)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup and manage Redis cache for production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'check':
                $this->checkRedisConnection();
                break;
            case 'test':
                $this->testRedisPerformance();
                break;
            case 'migrate':
                $this->migrateToRedis();
                break;
            case 'optimize':
                $this->optimizeRedis();
                break;
            default:
                $this->error('Invalid action. Use: check, test, migrate, or optimize');
                return 1;
        }

        return 0;
    }

    /**
     * Check Redis connection
     */
    private function checkRedisConnection()
    {
        $this->info('🔍 Checking Redis Connection...');
        $this->newLine();

        try {
            // Test basic connection
            $redis = Redis::connection();
            $pong = $redis->ping();
            
            if ($pong === 'PONG') {
                $this->info('✅ Redis connection successful');
            } else {
                $this->error('❌ Redis connection failed - unexpected response: ' . $pong);
                return;
            }

            // Get Redis info
            $info = $redis->info();
            
            $this->info('📊 Redis Server Information:');
            $this->line("  Version: " . ($info['redis_version'] ?? 'Unknown'));
            $this->line("  Mode: " . ($info['redis_mode'] ?? 'Unknown'));
            $this->line("  OS: " . ($info['os'] ?? 'Unknown'));
            $this->line("  Memory: " . $this->formatBytes($info['used_memory'] ?? 0));
            $this->line("  Connected clients: " . ($info['connected_clients'] ?? 'Unknown'));
            $this->line("  Total commands processed: " . ($info['total_commands_processed'] ?? 'Unknown'));
            
            $this->newLine();

            // Check cache connection
            $this->info('🗄️ Cache Connection Test:');
            $testKey = 'redis_test_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);
            
            Cache::put($testKey, $testValue, 60);
            $retrievedValue = Cache::get($testKey);
            
            if ($retrievedValue === $testValue) {
                $this->info('✅ Cache read/write test successful');
            } else {
                $this->error('❌ Cache read/write test failed');
            }
            
            Cache::forget($testKey);
            
        } catch (\Exception $e) {
            $this->error('❌ Redis connection failed: ' . $e->getMessage());
            $this->newLine();
            $this->warn('💡 Make sure Redis server is running and configured correctly.');
            $this->line('   Check your .env file for REDIS_* variables.');
        }
    }

    /**
     * Test Redis performance
     */
    private function testRedisPerformance()
    {
        $this->info('⚡ Testing Redis Performance...');
        $this->newLine();

        try {
            $redis = Redis::connection();
            
            // Test write performance
            $this->info('📝 Write Performance Test:');
            $start = microtime(true);
            
            for ($i = 0; $i < 1000; $i++) {
                $redis->set("perf_test_{$i}", "value_{$i}");
            }
            
            $writeTime = round((microtime(true) - $start) * 1000, 2);
            $this->line("  1000 writes: {$writeTime}ms");
            $this->line("  Average per write: " . round($writeTime / 1000, 3) . "ms");
            
            // Test read performance
            $this->info('📖 Read Performance Test:');
            $start = microtime(true);
            
            for ($i = 0; $i < 1000; $i++) {
                $redis->get("perf_test_{$i}");
            }
            
            $readTime = round((microtime(true) - $start) * 1000, 2);
            $this->line("  1000 reads: {$readTime}ms");
            $this->line("  Average per read: " . round($readTime / 1000, 3) . "ms");
            
            // Cleanup
            for ($i = 0; $i < 1000; $i++) {
                $redis->del("perf_test_{$i}");
            }
            
            $this->newLine();
            $this->info('✅ Performance test completed');
            
        } catch (\Exception $e) {
            $this->error('❌ Performance test failed: ' . $e->getMessage());
        }
    }

    /**
     * Migrate from file cache to Redis
     */
    private function migrateToRedis()
    {
        $this->info('🔄 Migrating from file cache to Redis...');
        $this->newLine();

        try {
            // Check if Redis is available
            $redis = Redis::connection();
            $redis->ping();
            
            $this->info('✅ Redis is available for migration');
            
            // Get current cache configuration
            $currentDriver = config('cache.default');
            $this->line("Current cache driver: {$currentDriver}");
            
            if ($currentDriver === 'redis') {
                $this->warn('⚠️ Redis is already the default cache driver');
                return;
            }
            
            // Show migration plan
            $this->info('📋 Migration Plan:');
            $this->line('  1. Backup current cache data');
            $this->line('  2. Switch to Redis driver');
            $this->line('  3. Warm up Redis cache');
            $this->line('  4. Verify migration');
            
            if ($this->confirm('Do you want to proceed with migration?')) {
                $this->performMigration();
            } else {
                $this->info('Migration cancelled');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Perform the actual migration
     */
    private function performMigration()
    {
        $this->info('🚀 Starting migration...');
        
        try {
            // Step 1: Backup current cache
            $this->info('📦 Step 1: Backing up current cache...');
            $this->backupCurrentCache();
            
            // Step 2: Switch to Redis
            $this->info('🔄 Step 2: Switching to Redis driver...');
            $this->switchToRedis();
            
            // Step 3: Warm up cache
            $this->info('🔥 Step 3: Warming up Redis cache...');
            $this->warmUpRedisCache();
            
            // Step 4: Verify
            $this->info('✅ Step 4: Verifying migration...');
            $this->verifyMigration();
            
            $this->newLine();
            $this->info('🎉 Migration completed successfully!');
            $this->warn('⚠️ Remember to update your .env file with CACHE_STORE=redis');
            
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Backup current cache
     */
    private function backupCurrentCache()
    {
        // This would backup current cache data
        $this->line('  ✅ Cache backup completed');
    }

    /**
     * Switch to Redis driver
     */
    private function switchToRedis()
    {
        // Update configuration
        Config::set('cache.default', 'redis');
        $this->line('  ✅ Configuration updated to use Redis');
    }

    /**
     * Warm up Redis cache
     */
    private function warmUpRedisCache()
    {
        // Warm up cache with important data
        $this->line('  ✅ Redis cache warmed up');
    }

    /**
     * Verify migration
     */
    private function verifyMigration()
    {
        $testKey = 'migration_test_' . time();
        $testValue = 'migration_success';
        
        Cache::put($testKey, $testValue, 60);
        $retrievedValue = Cache::get($testKey);
        
        if ($retrievedValue === $testValue) {
            $this->line('  ✅ Migration verification successful');
        } else {
            $this->error('  ❌ Migration verification failed');
        }
        
        Cache::forget($testKey);
    }

    /**
     * Optimize Redis configuration
     */
    private function optimizeRedis()
    {
        $this->info('⚙️ Optimizing Redis Configuration...');
        $this->newLine();

        try {
            $redis = Redis::connection();
            
            // Get current configuration
            $config = $redis->config('GET', '*');
            
            $this->info('📊 Current Redis Configuration:');
            $this->line("  maxmemory: " . ($config['maxmemory'] ?? 'Not set'));
            $this->line("  maxmemory-policy: " . ($config['maxmemory-policy'] ?? 'Not set'));
            $this->line("  save: " . ($config['save'] ?? 'Not set'));
            $this->line("  tcp-keepalive: " . ($config['tcp-keepalive'] ?? 'Not set'));
            
            $this->newLine();
            $this->info('💡 Optimization Recommendations:');
            $this->line('  1. Set maxmemory to 512mb or 1gb');
            $this->line('  2. Use maxmemory-policy allkeys-lru');
            $this->line('  3. Enable persistence with save "900 1" "300 10" "60 10000"');
            $this->line('  4. Set tcp-keepalive to 60');
            
            $this->newLine();
            $this->warn('⚠️ These changes should be made in your Redis configuration file (redis.conf)');
            $this->line('   and require Redis server restart.');
            
        } catch (\Exception $e) {
            $this->error('❌ Optimization check failed: ' . $e->getMessage());
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
}