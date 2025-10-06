<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send activation email
     */
    public static function sendActivationEmail($username, $email, $token)
    {
        try {
            $activationUrl = url('/activate?token=' . $token);
            
            $data = [
                'username' => $username,
                'email' => $email,
                'activationUrl' => $activationUrl,
                'siteName' => config('app.name', 'WoW Server'),
            ];
            
            Mail::send('emails.activation', $data, function ($message) use ($email, $username) {
                $message->to($email, $username)
                        ->subject(__('auth.email_activation_subject'));
            });
            
            Log::info('Activation email sent', [
                'username' => $username,
                'email' => $email,
                'token' => $token
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send activation email', [
                'error' => $e->getMessage(),
                'username' => $username,
                'email' => $email
            ]);
            return false;
        }
    }
    
    /**
     * Send activation confirmation email
     */
    public static function sendActivationConfirmation($username, $email)
    {
        try {
            $data = [
                'username' => $username,
                'email' => $email,
                'siteName' => config('app.name', 'WoW Server'),
            ];
            
            Mail::send('emails.activation-confirmation', $data, function ($message) use ($email, $username) {
                $message->to($email, $username)
                        ->subject(__('auth.email_activation_confirmation_subject'));
            });
            
            Log::info('Activation confirmation email sent', [
                'username' => $username,
                'email' => $email
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send activation confirmation email', [
                'error' => $e->getMessage(),
                'username' => $username,
                'email' => $email
            ]);
            return false;
        }
    }
}
