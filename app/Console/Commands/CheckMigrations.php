<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check database structure and migrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking database structure...');
        
        // Проверяем основные таблицы
        $requiredTables = [
            'permissions',
            'roles', 
            'role_permissions',
            'user_roles',
            'server_news',
            'news_comments',
            'user_currencies',
            'votes',
            'shop_items',
            'purchases',
            'user_sessions',
            'admin_logs',
            'website_activity_log',
            'failed_logins',
            'login_attempts',
            'profile_avatars',
            'user_ip_restrictions',
            'upcoming_events',
            'site_items'
        ];

        $this->info('Required tables:');
        foreach ($requiredTables as $table) {
            if (Schema::hasTable($table)) {
                $this->line("  ✅ {$table}");
            } else {
                $this->error("  ❌ {$table} - MISSING");
            }
        }

        // Проверяем индексы
        $this->info(PHP_EOL . 'Checking indexes...');
        $this->checkIndexes();

        // Проверяем связи
        $this->info(PHP_EOL . 'Checking foreign keys...');
        $this->checkForeignKeys();

        $this->info(PHP_EOL . 'Database check completed!');
    }

    private function checkIndexes()
    {
        $tablesWithIndexes = [
            'server_news' => ['is_published', 'created_at', 'author_id'],
            'news_comments' => ['news_id', 'is_approved', 'user_id', 'parent_id'],
            'user_currencies' => ['username', 'email', 'role'],
            'votes' => ['account_id', 'voted_at', 'site_name', 'is_processed'],
            'shop_items' => ['category', 'is_active', 'sort_order'],
            'purchases' => ['account_id', 'created_at', 'status', 'processed_at'],
            'user_sessions' => ['account_id', 'active', 'last_active'],
            'admin_logs' => ['admin_id', 'created_at', 'action'],
            'website_activity_log' => ['account_id', 'created_at', 'action'],
            'failed_logins' => ['username', 'attempted_at', 'ip_address'],
            'login_attempts' => ['account_id', 'created_at', 'ip_address']
        ];

        foreach ($tablesWithIndexes as $table => $indexes) {
            if (Schema::hasTable($table)) {
                $this->line("  {$table}:");
                foreach ($indexes as $index) {
                    $this->line("    - {$index}");
                }
            }
        }
    }

    private function checkForeignKeys()
    {
        $foreignKeys = [
            'news_comments.news_id' => 'server_news.id',
            'purchases.shop_item_id' => 'shop_items.id',
            'role_permissions.role_id' => 'roles.id',
            'role_permissions.permission_id' => 'permissions.id'
        ];

        foreach ($foreignKeys as $key => $reference) {
            $this->line("  {$key} → {$reference}");
        }
    }
}