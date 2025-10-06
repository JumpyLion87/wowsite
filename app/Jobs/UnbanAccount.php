<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnbanAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $accountId;

    /**
     * Create a new job instance.
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Удаляем временный бан
            DB::connection('mysql_auth')->table('account_banned')
                ->where('id', $this->accountId)
                ->where('bannedby', 'Admin Panel')
                ->where('active', 1)
                ->delete();

            Log::info("Temporary ban removed for account {$this->accountId}");
            
        } catch (\Exception $e) {
            Log::error("Failed to unban account {$this->accountId}: " . $e->getMessage());
        }
    }
}