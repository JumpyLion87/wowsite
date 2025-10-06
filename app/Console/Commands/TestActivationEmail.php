<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;

class TestActivationEmail extends Command
{
    protected $signature = 'test:activation-email {email}';
    protected $description = 'Send test activation email';

    public function handle()
    {
        $email = $this->argument('email');
        $username = 'TESTUSER';
        $token = bin2hex(random_bytes(32));
        $siteName = config('app.name', 'WoW Server');
        
        $this->info("Sending test activation email to: {$email}");
        
        try {
            $result = EmailService::sendActivationEmail($username, $email, $token);
            
            if ($result) {
                $this->info("✅ Activation email sent successfully!");
                $this->info("Username: {$username}");
                $this->info("Email: {$email}");
                $this->info("Token: {$token}");
                $this->info("Activation URL: " . url("/activation?token={$token}"));
            } else {
                $this->error("❌ Failed to send activation email");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
}
