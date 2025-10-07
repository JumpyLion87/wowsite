<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Services\EmailService;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        $accountExists = DB::connection('mysql_auth')
            ->table('account')
            ->where('email', $email)
            ->exists();

        if (!$accountExists) {
            return back()->withErrors(['email' => __('Эта почта не найдена')]);
        }

        $tokenPlain = Str::random(64);
        $tokenHash = Hash::make($tokenPlain);

        DB::connection('mysql')->table('password_reset_tokens')
            ->updateOrInsert(
                ['email' => $email],
                ['token' => $tokenHash, 'created_at' => Carbon::now()]
            );

        $resetUrl = route('password.reset', ['token' => $tokenPlain, 'email' => $email]);
        
        // Get username for email
        $user = User::on('mysql_auth')->where('email', $email)->first();
        $username = $user ? $user->username : $email;
        
        // Send email using existing EmailService
        if (EmailService::sendPasswordResetEmail($username, $email, $resetUrl)) {
            Log::info('Password reset email sent successfully', ['email' => $email, 'username' => $username]);
        } else {
            Log::error('Failed to send password reset email', ['email' => $email, 'username' => $username]);
            // Still show success to user for security
        }

        return back()->with('status', __('Ссылка для сброса пароля отправлена (проверьте почту).'));
    }

    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->input('email');
        $tokenPlain = $request->input('token');

        $record = DB::connection('mysql')->table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !Hash::check($tokenPlain, $record->token)) {
            return back()->withErrors(['token' => __('Неверный или просроченный токен')]);
        }

        if (!empty($record->created_at)) {
            $created = Carbon::parse($record->created_at);
            if ($created->lt(now()->subMinutes(60))) {
                return back()->withErrors(['token' => __('Токен просрочен')]);
            }
        }

        $user = User::on('mysql_auth')->where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => __('Аккаунт не найден')]);
        }

        $newPassword = $request->input('password');
        $updated = $user->updatePasswordWithSRP6($newPassword);
        if (!$updated) {
            return back()->withErrors(['password' => __('Не удалось обновить пароль')]);
        }

        DB::connection('mysql')->table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('login')->with('status', __('Пароль успешно изменён. Войдите с новым паролем.'));
    }
}
