<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PendingAccount;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivationController extends Controller
{
    /**
     * Show activation page and activate account immediately
     */
    public function showActivationForm(Request $request)
    {
        $token = $request->get('token');
        
        if (!$token) {
            return view('auth.activation', [
                'error' => __('auth.activation_no_token')
            ]);
        }
        
        // Try to activate account immediately
        return $this->activate($request);
    }
    
    /**
     * Activate account
     */
    public function activate(Request $request)
    {
        $token = $request->get('token');
        
        if (!$token) {
            return back()->withErrors(['error' => __('auth.activation_no_token')]);
        }
        
        try {
            DB::beginTransaction();
            
            $pendingAccount = PendingAccount::findByToken($token);
            
            if (!$pendingAccount) {
                return view('auth.activation', [
                    'error' => __('auth.activation_invalid_token')
                ]);
            }
            
            if ($pendingAccount->isExpired()) {
                return view('auth.activation', [
                    'error' => __('auth.activation_expired')
                ]);
            }
            
            // Check if account already exists
            if (User::where('username', $pendingAccount->username)->exists()) {
                return view('auth.activation', [
                    'error' => __('auth.activation_account_exists')
                ]);
            }
            
            // Create user account
            $user = User::create([
                'username' => $pendingAccount->username,
                'email' => $pendingAccount->email,
                'salt' => $pendingAccount->salt,
                'verifier' => $pendingAccount->verifier,
                'joindate' => now(),
                'expansion' => 2, // WotLK expansion
            ]);
            
            // Mark as activated and delete pending account
            $pendingAccount->markAsActivated();
            $pendingAccount->delete();
            
            // Send confirmation email
            EmailService::sendActivationConfirmation($pendingAccount->username, $pendingAccount->email);
            
            DB::commit();
            
            Log::info('Account activated successfully', [
                'username' => $pendingAccount->username,
                'email' => $pendingAccount->email,
                'user_id' => $user->id
            ]);
            
            return view('auth.activation', [
                'success' => __('auth.activation_success'),
                'username' => $pendingAccount->username
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Account activation failed', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);
            
            return view('auth.activation', [
                'error' => __('auth.activation_failed')
            ]);
        }
    }
}