<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем разрешения
        $permissions = [
            // Управление новостями
            ['name' => 'news.create', 'display_name' => 'Создание новостей', 'description' => 'Создание новых новостей', 'category' => 'news'],
            ['name' => 'news.edit', 'display_name' => 'Редактирование новостей', 'description' => 'Редактирование существующих новостей', 'category' => 'news'],
            ['name' => 'news.delete', 'display_name' => 'Удаление новостей', 'description' => 'Удаление новостей', 'category' => 'news'],
            ['name' => 'news.publish', 'display_name' => 'Публикация новостей', 'description' => 'Публикация и снятие с публикации новостей', 'category' => 'news'],
            
            // Управление комментариями
            ['name' => 'comments.moderate', 'display_name' => 'Модерация комментариев', 'description' => 'Одобрение и отклонение комментариев', 'category' => 'comments'],
            ['name' => 'comments.delete', 'display_name' => 'Удаление комментариев', 'description' => 'Удаление комментариев', 'category' => 'comments'],
            
            // Управление банами
            ['name' => 'bans.view', 'display_name' => 'Просмотр банов', 'description' => 'Просмотр списка банов', 'category' => 'bans'],
            ['name' => 'bans.create', 'display_name' => 'Создание банов', 'description' => 'Создание новых банов', 'category' => 'bans'],
            ['name' => 'bans.edit', 'display_name' => 'Редактирование банов', 'description' => 'Редактирование существующих банов', 'category' => 'bans'],
            ['name' => 'bans.delete', 'display_name' => 'Удаление банов', 'description' => 'Удаление банов', 'category' => 'bans'],
            ['name' => 'bans.unban', 'display_name' => 'Разбан игроков', 'description' => 'Разбан игроков', 'category' => 'bans'],
            
            // Управление пользователями
            ['name' => 'users.view', 'display_name' => 'Просмотр пользователей', 'description' => 'Просмотр списка пользователей', 'category' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Редактирование пользователей', 'description' => 'Редактирование данных пользователей', 'category' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Удаление пользователей', 'description' => 'Удаление пользователей', 'category' => 'users'],
            
            // Системные настройки
            ['name' => 'settings.view', 'display_name' => 'Просмотр настроек', 'description' => 'Просмотр системных настроек', 'category' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Редактирование настроек', 'description' => 'Редактирование системных настроек', 'category' => 'settings'],
            
            // Статистика
            ['name' => 'stats.view', 'display_name' => 'Просмотр статистики', 'description' => 'Просмотр статистики сервера', 'category' => 'stats'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Создаем роли
        $adminRole = Role::createRole('admin', 'Администратор', 'Полный доступ ко всем функциям', '#dc3545');
        $moderatorRole = Role::createRole('moderator', 'Модератор', 'Ограниченный доступ к функциям модерации', '#28a745');
        $playerRole = Role::createRole('player', 'Игрок', 'Обычный игрок', '#6c757d');

        // Даем права администратору
        $adminRole->givePermission('news.create');
        $adminRole->givePermission('news.edit');
        $adminRole->givePermission('news.delete');
        $adminRole->givePermission('news.publish');
        $adminRole->givePermission('comments.moderate');
        $adminRole->givePermission('comments.delete');
        $adminRole->givePermission('bans.view');
        $adminRole->givePermission('bans.create');
        $adminRole->givePermission('bans.edit');
        $adminRole->givePermission('bans.delete');
        $adminRole->givePermission('bans.unban');
        $adminRole->givePermission('users.view');
        $adminRole->givePermission('users.edit');
        $adminRole->givePermission('users.delete');
        $adminRole->givePermission('settings.view');
        $adminRole->givePermission('settings.edit');
        $adminRole->givePermission('stats.view');

        // Даем права модератору
        $moderatorRole->givePermission('news.create');
        $moderatorRole->givePermission('news.edit');
        $moderatorRole->givePermission('comments.moderate');
        $moderatorRole->givePermission('comments.delete');
        $moderatorRole->givePermission('bans.view');
        $moderatorRole->givePermission('bans.create');
        $moderatorRole->givePermission('bans.unban');
        $moderatorRole->givePermission('users.view');
        $moderatorRole->givePermission('stats.view');

        $this->command->info('Разрешения и роли созданы успешно!');
    }
}