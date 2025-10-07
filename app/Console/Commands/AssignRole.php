<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:assign {account_id} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->argument('account_id');
        $roleName = $this->argument('role');

        // Проверяем, существует ли пользователь
        $user = User::find($accountId);
        if (!$user) {
            $this->error("User with ID {$accountId} not found.");
            return 1;
        }

        // Проверяем, существует ли роль
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role '{$roleName}' not found.");
            return 1;
        }

        // Проверяем, есть ли уже у пользователя эта роль
        if ($user->hasRole($roleName)) {
            $this->info("User {$user->username} already has role '{$roleName}'.");
            return 0;
        }

        // Назначаем роль
        $user->roles()->attach($role->id);
        
        $this->info("Role '{$roleName}' assigned to user {$user->username} successfully.");
        return 0;
    }
}