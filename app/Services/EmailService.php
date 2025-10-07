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
    
    /**
     * Send password reset email
     */
    public static function sendPasswordResetEmail($username, $email, $resetUrl)
    {
        try {
            $data = [
                'username' => $username,
                'email' => $email,
                'resetUrl' => $resetUrl,
                'siteName' => config('app.name', 'WoW Server'),
            ];
            
            Mail::send('emails.password-reset', $data, function ($message) use ($email, $username) {
                $message->to($email, $username)
                        ->subject(__('auth.password_reset_subject'));
            });
            
            Log::info('Password reset email sent', [
                'username' => $username,
                'email' => $email,
                'resetUrl' => $resetUrl
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'error' => $e->getMessage(),
                'username' => $username,
                'email' => $email
            ]);
            return false;
        }
    }
}
