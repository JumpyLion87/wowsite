<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Функция для безопасного создания индекса
        $createIndex = function($connection, $table, $indexName, $columns) {
            try {
                $columnsStr = is_array($columns) ? implode(', ', $columns) : $columns;
                DB::connection($connection)->statement("CREATE INDEX {$indexName} ON {$table} ({$columnsStr})");
            } catch (\Exception $e) {
                // Индекс уже существует или другая ошибка - пропускаем
            }
        };

        // Индексы для таблицы characters (mysql_char)
        $createIndex('mysql_char', 'characters', 'idx_characters_online', 'online');
        $createIndex('mysql_char', 'characters', 'idx_characters_account', 'account');
        $createIndex('mysql_char', 'characters', 'idx_characters_level', 'level');
        $createIndex('mysql_char', 'characters', 'idx_characters_created_at', 'created_at');
        $createIndex('mysql_char', 'characters', 'idx_characters_online_level', ['online', 'level']);

        // Индексы для таблицы account (mysql_auth)
        $createIndex('mysql_auth', 'account', 'idx_account_joindate', 'joindate');
        $createIndex('mysql_auth', 'account', 'idx_account_last_login', 'last_login');

        // Индексы для таблицы account_banned (mysql_auth)
        $createIndex('mysql_auth', 'account_banned', 'idx_account_banned_active', 'active');
        $createIndex('mysql_auth', 'account_banned', 'idx_account_banned_bandate', 'bandate');

        // Индексы для таблицы character_banned (mysql_char)
        $createIndex('mysql_char', 'character_banned', 'idx_character_banned_bandate', 'bandate');

        // Индексы для таблицы guild_member (mysql_char)
        $createIndex('mysql_char', 'guild_member', 'idx_guild_member_guid', 'guid');
        $createIndex('mysql_char', 'guild_member', 'idx_guild_member_guildid', 'guildid');

        // Индексы для таблицы arena_team_member (mysql_char)
        $createIndex('mysql_char', 'arena_team_member', 'idx_arena_team_member_guid', 'guid');
        $createIndex('mysql_char', 'arena_team_member', 'idx_arena_team_member_arenaTeamId', 'arenaTeamId');

        // Индексы для таблицы character_inventory (mysql_char)
        $createIndex('mysql_char', 'character_inventory', 'idx_character_inventory_guid', 'guid');
        $createIndex('mysql_char', 'character_inventory', 'idx_character_inventory_bag_slot', ['bag', 'slot']);

        // Индексы для таблицы item_instance (mysql_char)
        $createIndex('mysql_char', 'item_instance', 'idx_item_instance_itemEntry', 'itemEntry');

        // Индексы для таблицы item_template (mysql_char)
        $createIndex('mysql_char', 'item_template', 'idx_item_template_displayid', 'displayid');

        // Индексы для таблицы purchases (mysql)
        $createIndex('mysql', 'purchases', 'idx_purchases_account_id', 'account_id');
        $createIndex('mysql', 'purchases', 'idx_purchases_purchase_date', 'purchase_date');
        $createIndex('mysql', 'purchases', 'idx_purchases_item_id', 'item_id');

        // Индексы для таблицы shop_items (mysql)
        $createIndex('mysql', 'shop_items', 'idx_shop_items_category', 'category');
        $createIndex('mysql', 'shop_items', 'idx_shop_items_active', 'active');

        // Индексы для таблицы user_currencies (mysql)
        $createIndex('mysql', 'user_currencies', 'idx_user_currencies_account_id', 'account_id');
        $createIndex('mysql', 'user_currencies', 'idx_user_currencies_role', 'role');

        // Индексы для таблицы news (mysql)
        $createIndex('mysql', 'news', 'idx_news_post_date', 'post_date');
        $createIndex('mysql', 'news', 'idx_news_is_important', 'is_important');

        // Индексы для таблицы news_comments (mysql)
        $createIndex('mysql', 'news_comments', 'idx_news_comments_news_id', 'news_id');
        $createIndex('mysql', 'news_comments', 'idx_news_comments_is_approved', 'is_approved');
        $createIndex('mysql', 'news_comments', 'idx_news_comments_created_at', 'created_at');

        // Индексы для таблицы user_roles (mysql)
        $createIndex('mysql', 'user_roles', 'idx_user_roles_account_id', 'account_id');
        $createIndex('mysql', 'user_roles', 'idx_user_roles_role_id', 'role_id');

        // Индексы для таблицы role_permissions (mysql)
        $createIndex('mysql', 'role_permissions', 'idx_role_permissions_role_id', 'role_id');
        $createIndex('mysql', 'role_permissions', 'idx_role_permissions_permission_id', 'permission_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Функция для безопасного удаления индекса
        $dropIndex = function($connection, $table, $indexName) {
            try {
                DB::connection($connection)->statement("DROP INDEX {$indexName} ON {$table}");
            } catch (\Exception $e) {
                // Индекс не существует или другая ошибка - пропускаем
            }
        };

        // Удаляем индексы для таблицы characters (mysql_char)
        $dropIndex('mysql_char', 'characters', 'idx_characters_online');
        $dropIndex('mysql_char', 'characters', 'idx_characters_account');
        $dropIndex('mysql_char', 'characters', 'idx_characters_level');
        $dropIndex('mysql_char', 'characters', 'idx_characters_created_at');
        $dropIndex('mysql_char', 'characters', 'idx_characters_online_level');

        // Удаляем индексы для таблицы account (mysql_auth)
        $dropIndex('mysql_auth', 'account', 'idx_account_joindate');
        $dropIndex('mysql_auth', 'account', 'idx_account_last_login');

        // Удаляем индексы для таблицы account_banned (mysql_auth)
        $dropIndex('mysql_auth', 'account_banned', 'idx_account_banned_active');
        $dropIndex('mysql_auth', 'account_banned', 'idx_account_banned_bandate');

        // Удаляем индексы для таблицы character_banned (mysql_char)
        $dropIndex('mysql_char', 'character_banned', 'idx_character_banned_bandate');

        // Удаляем индексы для таблицы guild_member (mysql_char)
        $dropIndex('mysql_char', 'guild_member', 'idx_guild_member_guid');
        $dropIndex('mysql_char', 'guild_member', 'idx_guild_member_guildid');

        // Удаляем индексы для таблицы arena_team_member (mysql_char)
        $dropIndex('mysql_char', 'arena_team_member', 'idx_arena_team_member_guid');
        $dropIndex('mysql_char', 'arena_team_member', 'idx_arena_team_member_arenaTeamId');

        // Удаляем индексы для таблицы character_inventory (mysql_char)
        $dropIndex('mysql_char', 'character_inventory', 'idx_character_inventory_guid');
        $dropIndex('mysql_char', 'character_inventory', 'idx_character_inventory_bag_slot');

        // Удаляем индексы для таблицы item_instance (mysql_char)
        $dropIndex('mysql_char', 'item_instance', 'idx_item_instance_itemEntry');

        // Удаляем индексы для таблицы item_template (mysql_char)
        $dropIndex('mysql_char', 'item_template', 'idx_item_template_displayid');

        // Удаляем индексы для таблицы purchases (mysql)
        $dropIndex('mysql', 'purchases', 'idx_purchases_account_id');
        $dropIndex('mysql', 'purchases', 'idx_purchases_purchase_date');
        $dropIndex('mysql', 'purchases', 'idx_purchases_item_id');

        // Удаляем индексы для таблицы shop_items (mysql)
        $dropIndex('mysql', 'shop_items', 'idx_shop_items_category');
        $dropIndex('mysql', 'shop_items', 'idx_shop_items_active');

        // Удаляем индексы для таблицы user_currencies (mysql)
        $dropIndex('mysql', 'user_currencies', 'idx_user_currencies_account_id');
        $dropIndex('mysql', 'user_currencies', 'idx_user_currencies_role');

        // Удаляем индексы для таблицы news (mysql)
        $dropIndex('mysql', 'news', 'idx_news_post_date');
        $dropIndex('mysql', 'news', 'idx_news_is_important');

        // Удаляем индексы для таблицы news_comments (mysql)
        $dropIndex('mysql', 'news_comments', 'idx_news_comments_news_id');
        $dropIndex('mysql', 'news_comments', 'idx_news_comments_is_approved');
        $dropIndex('mysql', 'news_comments', 'idx_news_comments_created_at');

        // Удаляем индексы для таблицы user_roles (mysql)
        $dropIndex('mysql', 'user_roles', 'idx_user_roles_account_id');
        $dropIndex('mysql', 'user_roles', 'idx_user_roles_role_id');

        // Удаляем индексы для таблицы role_permissions (mysql)
        $dropIndex('mysql', 'role_permissions', 'idx_role_permissions_role_id');
        $dropIndex('mysql', 'role_permissions', 'idx_role_permissions_permission_id');
    }
};