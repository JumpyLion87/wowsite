<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AjaxController extends Controller
{
    /**
     * Поиск аккаунтов для банов
     */
    public function searchAccounts(Request $request)
    {
        // Простая проверка авторизации
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        if (!User::isAdmin(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $search = $request->get('search', '');
        
        if (strlen($search) < 3) {
            return response()->json(['accounts' => []]);
        }

        try {
            $accounts = DB::connection('mysql_auth')
                ->table('account')
                ->select('id', 'username', 'email', 'last_login')
                ->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->limit(10)
                ->get();

            return response()->json(['accounts' => $accounts]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Получить персонажей аккаунта
     */
    public function getAccountCharacters(Request $request)
    {
        
        // Простая проверка авторизации
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        if (!User::isAdmin(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $accountId = $request->get('account_id');
        
        if (!$accountId) {
            return response()->json(['characters' => []]);
        }

        try {
            // Сначала получаем информацию об аккаунте
            $account = DB::connection('mysql_auth')
                ->table('account')
                ->select('id', 'username')
                ->where('id', $accountId)
                ->first();

            if (!$account) {
                return response()->json(['characters' => []]);
            }

            // Затем получаем персонажей
            $characters = DB::connection('mysql_char')
                ->table('characters')
                ->select('name', 'level', 'race', 'class')
                ->where('account', $accountId)
                ->orderBy('level', 'desc')
                ->get()
                ->map(function($char) use ($account) {
                    // Добавляем названия рас и классов
                    $races = [
                        1 => 'Человек', 2 => 'Орк', 3 => 'Дворф', 4 => 'Ночной эльф',
                        5 => 'Нежить', 6 => 'Таурен', 7 => 'Гном', 8 => 'Тролль',
                        9 => 'Гоблин', 10 => 'Кровный эльф', 11 => 'Дреней'
                    ];
                    $classes = [
                        1 => 'Воин', 2 => 'Паладин', 3 => 'Охотник', 4 => 'Разбойник',
                        5 => 'Жрец', 6 => 'Рыцарь смерти', 7 => 'Шаман', 8 => 'Маг',
                        9 => 'Чернокнижник', 10 => 'Монах', 11 => 'Друид', 12 => 'Охотник на демонов'
                    ];
                    
                    $char->race_name = $races[$char->race] ?? 'Неизвестно';
                    $char->class_name = $classes[$char->class] ?? 'Неизвестно';
                    $char->account_name = $account->username;
                    $char->account_id = $account->id;
                    
                    return $char;
                });

            return response()->json(['characters' => $characters]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}
