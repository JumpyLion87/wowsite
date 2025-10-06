<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Services\SRP6Service;
use App\Services\EmailService;
use App\Models\PendingAccount;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $rules = [
            'username' => 'required|string|min:3|max:16|regex:/^[a-zA-Z0-9]+$/',
            'email' => 'required|email|max:64',
            'password' => 'required|string|min:6|max:32',
            'confirm_password' => 'required|string|same:password',
        ];

        $recaptchaSiteKey = config('app.recaptcha_site_key');
        $recaptchaSecret = config('app.recaptcha_secret_key');

        if (!empty($recaptchaSiteKey) && !empty($recaptchaSecret)) {
            $rules['g-recaptcha-response'] = 'required|string';
        }

        $request->validate($rules, [
            'username.required' => __('auth.username_required'),
            'username.min' => __('auth.username_too_short'),
            'username.max' => __('auth.username_too_long'),
            'username.regex' => __('auth.username_invalid_chars'),
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.email_invalid'),
            'email.max' => __('auth.email_too_long'),
            'password.required' => __('auth.password_required'),
            'password.min' => __('auth.password_too_short'),
            'password.max' => __('auth.password_too_long'),
            'confirm_password.required' => __('auth.confirm_password_required'),
            'confirm_password.same' => __('auth.password_mismatch'),
            'g-recaptcha-response.required' => __('auth.recaptcha_required'),
        ]);

        // Optional: server-side reCAPTCHA verification when configured
        if (!empty($recaptchaSiteKey) && !empty($recaptchaSecret)) {
            $token = (string) $request->input('g-recaptcha-response');
            try {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $recaptchaSecret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
                $ok = $response->ok() ? (bool) data_get($response->json(), 'success', false) : false;
                if (!$ok) {
                    return back()->withErrors(['error' => __('auth.recaptcha_failed')])->withInput();
                }
            } catch (\Throwable $e) {
                return back()->withErrors(['error' => __('auth.recaptcha_unavailable')])->withInput();
            }
        }

        // Check for existing accounts
        $username = strtoupper($request->username);
        $email = $request->email;

        // Check if username already exists
        if (User::where('username', $username)->exists()) {
            return back()->withErrors(['error' => __('auth.username_exists')])->withInput();
        }

        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            return back()->withErrors(['error' => __('auth.email_exists')])->withInput();
        }

        // Check pending accounts (if table exists)
        try {
            $pendingExists = DB::connection('mysql')->table('pending_accounts')
                ->where('username', $username)
                ->orWhere('email', $email)
                ->exists();
            
            if ($pendingExists) {
                return back()->withErrors(['error' => __('auth.account_pending')])->withInput();
            }
        } catch (\Exception $e) {
            // Table doesn't exist, continue
        }

        // Create account using SRP6
        try {
            DB::beginTransaction();

            // Generate SRP6 salt and verifier
            $srp6Service = new SRP6Service();
            $salt = $srp6Service->generateSalt();
            $verifier = $srp6Service->calculateVerifier($username, $request->password, $salt);
            
            \Log::info('Registration attempt', [
                'username' => $username,
                'email' => $email,
                'salt_length' => strlen($salt),
                'verifier_length' => strlen($verifier)
            ]);

            // Check if email activation is enabled
            $emailActivationEnabled = env('MAIL_MAILER') !== 'log' && !empty(env('MAIL_HOST'));
            
            \Log::info('Email activation check', [
                'MAIL_MAILER' => env('MAIL_MAILER'),
                'MAIL_HOST' => env('MAIL_HOST'),
                'emailActivationEnabled' => $emailActivationEnabled
            ]);
            
            if ($emailActivationEnabled) {
                // Create pending account with email activation
                $token = bin2hex(random_bytes(32));
                
                PendingAccount::createPending($username, $email, $salt, $verifier, $token, 24);
                
                // Send activation email
                if (EmailService::sendActivationEmail($username, $email, $token)) {
                    DB::commit();
                    return redirect('/login')->with('success', __('auth.registration_success_email'))->with('show_message', true);
                } else {
                    DB::rollback();
                    return back()->withErrors(['error' => __('auth.email_send_failed')])->withInput();
                }
            } else {
                // Create account directly (no email activation)
                $user = User::create([
                    'username' => $username,
                    'email' => $email,
                    'salt' => $salt,
                    'verifier' => $verifier,
                    'joindate' => now(),
                    'expansion' => 2, // WotLK expansion
                ]);

                DB::commit();
                return redirect('/login')->with('success', __('auth.registration_success'));
            }

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'username' => $username,
                'email' => $email
            ]);
            return back()->withErrors(['error' => __('auth.registration_failed') . ': ' . $e->getMessage()])->withInput();
        }
    }
}
