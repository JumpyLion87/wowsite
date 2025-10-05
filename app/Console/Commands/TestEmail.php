<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;

class TestEmail extends Command
{
    protected $signature = 'test:email';
    protected $description = 'Test email sending';

    public function handle()
    {
        $this->info('Testing email configuration...');
        
        $this->info('MAIL_MAILER: ' . env('MAIL_MAILER'));
        $this->info('MAIL_HOST: ' . env('MAIL_HOST'));
        $this->info('MAIL_PORT: ' . env('MAIL_PORT'));
        $this->info('MAIL_ENCRYPTION: ' . env('MAIL_ENCRYPTION'));
        $this->info('MAIL_USERNAME: ' . env('MAIL_USERNAME'));
        
        $emailActivationEnabled = env('MAIL_MAILER') !== 'log' && !empty(env('MAIL_HOST'));
        $this->info('Email activation enabled: ' . ($emailActivationEnabled ? 'YES' : 'NO'));
        
        if ($emailActivationEnabled) {
            $this->info('Sending test email...');
            $result = EmailService::sendActivationEmail('testuser', 'test@example.com', 'testtoken123');
            $this->info('Email send result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        }
    }
}