<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\UserRole;

class SyncUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize user roles between user_currencies and user_roles tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting role synchronization...');

        // Clear existing user_roles
        UserRole::truncate();
        $this->info('Cleared existing user_roles table');

        // Get all users with roles from user_currencies
        $usersWithRoles = DB::connection('mysql')->table('user_currencies')
            ->whereIn('role', ['admin', 'moderator'])
            ->get(['account_id', 'role']);

        $synced = 0;
        foreach ($usersWithRoles as $user) {
            $roleId = $user->role === 'admin' ? 1 : 2; // admin = 1, moderator = 2
            
            UserRole::create([
                'account_id' => $user->account_id,
                'role_id' => $roleId
            ]);
            
            $synced++;
            $this->line("Synced user {$user->account_id} with role {$user->role}");
        }

        $this->info("Synchronization completed! Synced {$synced} users.");
        
        return 0;
    }
}
