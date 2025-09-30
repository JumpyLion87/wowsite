<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FailedLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $rules = [
            'username' => 'required|string',
            'password' => 'required|string',
        ];

        $recaptchaSiteKey = config('app.recaptcha_site_key');
        $recaptchaSecret = config('app.recaptcha_secret_key');

        if (!empty($recaptchaSiteKey) && !empty($recaptchaSecret)) {
            $rules['g-recaptcha-response'] = 'required|string';
        }

        $request->validate($rules, [
            'username.required' => __('Username is required'),
            'password.required' => __('Password is required'),
            'g-recaptcha-response.required' => __('Please complete the reCAPTCHA'),
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
                    return back()->withErrors(['error' => __('reCAPTCHA verification failed')])->withInput();
                }
            } catch (\Throwable $e) {
                // If Google is unreachable, fail gracefully but allow retry
                return back()->withErrors(['error' => __('Unable to verify reCAPTCHA. Please try again.')])->withInput();
            }
        }
    

        $ip = $request->ip();
        $username = $request->input('username');

        //Проверка брутфорса
        if (!$this->checkBruteForce($ip, $username)) {
        return back()->withErrors(['error' => 'Too many attempts. Please try again later.']);
        }

        $user = User::where('username', $username)->first();

        if (!$user || !$user->authenticateWithSRP6($request->password, $username)) {
            // Record failed attempt using legacy schema
            $record = FailedLogin::where(function ($q) use ($ip, $username) {
                $q->where('ip_address', $ip)->orWhere('username', $username);
            })->first();

            $nowTs = now()->timestamp;
            if ($record) {
                $newAttempts = (int) ($record->attempts ?? 0) + 1;
                $blockUntil = null;
                if ($newAttempts >= (int) config('app.max_login_attempts', 5)) {
                    $blockUntil = $nowTs + ((int) config('app.lockout_duration', 15) * 60);
                }
                $record->update([
                    'attempts' => $newAttempts,
                    'last_attempt' => $nowTs,
                    'username' => $username,
                    'block_until' => $blockUntil,
                ]);
            } else {
                FailedLogin::create([
                    'ip_address' => $ip,
                    'username' => $username,
                    'attempts' => 1,
                    'last_attempt' => $nowTs,
                    'block_until' => null,
                ]);
            }
            return back()->withErrors(['error' => 'Invalid username or password.'])->withInput();
        }

        Auth::login($user);
        // On success, clear failed attempts for this IP/username (best-effort)
        try {
            FailedLogin::where('ip_address', $ip)->orWhere('username', $username)->delete();
        } catch (\Throwable $e) {}
        return redirect('/account');

    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    protected function checkBruteForce($ip, $username)
    {
        $record = FailedLogin::where(function ($q) use ($ip, $username) {
            $q->where('ip_address', $ip)->orWhere('username', $username);
        })->first();

        if (!$record) {
            return true;
        }

        $nowTs = now()->timestamp;

        // If blocked and block_until in future => locked
        if (!empty($record->block_until) && (int) $record->block_until > $nowTs) {
            return false;
        }

        // If last attempt older than 1 hour, ignore counter
        $windowStart = $nowTs - 3600;
        if ((int) $record->last_attempt < $windowStart) {
            return true;
        }

        return ((int) ($record->attempts ?? 0)) < (int) config('app.max_login_attempts', 5);
    }
}