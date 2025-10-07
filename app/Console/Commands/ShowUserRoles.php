<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ShowUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:show {account_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show user roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->argument('account_id');

        // Проверяем, существует ли пользователь
        $user = User::find($accountId);
        if (!$user) {
            $this->error("User with ID {$accountId} not found.");
            return 1;
        }

        $this->info("User: {$user->username} (ID: {$user->id})");
        $this->line("");

        // Показываем роли
        $this->info("Roles:");
        $roles = $user->roles;
        if ($roles->count() > 0) {
            foreach ($roles as $role) {
                $this->line("  - {$role->display_name} ({$role->name})");
            }
        } else {
            $this->line("  No roles assigned");
        }

        $this->line("");

        // Показываем разрешения
        $this->info("Permissions:");
        $permissions = collect();
        foreach ($user->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }
        
        if ($permissions->count() > 0) {
            $groupedPermissions = $permissions->groupBy('category');
            foreach ($groupedPermissions as $category => $perms) {
                $this->line("  {$category}:");
                foreach ($perms as $permission) {
                    $this->line("    - {$permission->display_name} ({$permission->name})");
                }
            }
        } else {
            $this->line("  No permissions assigned");
        }

        return 0;
    }
}