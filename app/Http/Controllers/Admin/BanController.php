<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Helpers\PermissionHelper;

class BanController extends Controller
{
    /**
     * Показать список всех банов
     */
    public function index(Request $request)
    {
        if (!Auth::check() || !PermissionHelper::hasPermission(Auth::id(), 'bans.view')) {
            return redirect('/login');
        }

        // Получаем все баны (аккаунты + персонажи + IP)
        $accountBans = DB::connection('mysql_auth')
            ->table('account_banned as ab')
            ->join('account as a', 'ab.id', '=', 'a.id')
            ->select(
                'ab.id',
                'ab.bandate',
                'ab.unbandate',
                'ab.bannedby',
                'ab.banreason',
                'ab.active',
                'a.username',
                'a.email',
                DB::raw("'account' as ban_type"),
                DB::raw("NULL as character_name")
            );

        // Получаем баны персонажей отдельно
        $characterBansData = DB::connection('mysql_char')
            ->table('character_banned as cb')
            ->join('characters as c', 'cb.guid', '=', 'c.guid')
            ->select(
                'cb.guid as id',
                'cb.bandate',
                'cb.unbandate',
                'cb.bannedby',
                'cb.banreason',
                'cb.active',
                'c.account as account_id',
                'c.name as character_name'
            )
            ->get();

        // Получаем информацию об аккаунтах для банов персонажей
        $characterBans = collect();
        foreach ($characterBansData as $ban) {
            $account = DB::connection('mysql_auth')
                ->table('account')
                ->select('username', 'email')
                ->where('id', $ban->account_id)
                ->first();
            
            if ($account) {
                $characterBans->push((object)[
                    'id' => $ban->id,
                    'bandate' => $ban->bandate,
                    'unbandate' => $ban->unbandate,
                    'bannedby' => $ban->bannedby,
                    'banreason' => $ban->banreason,
                    'active' => $ban->active,
                    'username' => $account->username,
                    'email' => $account->email,
                    'ban_type' => 'character',
                    'character_name' => $ban->character_name
                ]);
            }
        }

        // Получаем IP баны
        $ipBansData = DB::connection('mysql_auth')
            ->table('ip_banned')
            ->select('*')
            ->get();

        $ipBans = collect();
        foreach ($ipBansData as $ban) {
            $ipBans->push((object)[
                'id' => $ban->ip,
                'bandate' => $ban->bandate,
                'unbandate' => $ban->unbandate,
                'bannedby' => $ban->bannedby,
                'banreason' => $ban->banreason,
                'active' => $ban->unbandate === 0 || $ban->unbandate > time(),
                'username' => $ban->ip,
                'email' => null,
                'ban_type' => 'ip',
                'character_name' => null
            ]);
        }

        // Объединяем результаты
        $allBans = $accountBans->get()->concat($characterBans)->concat($ipBans);

        // Фильтры
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');

        // Применяем фильтры
        $filteredBans = $allBans->filter(function($ban) use ($status, $search) {
            // Фильтр по статусу
            if ($status === 'active') {
                if ($ban->active != 1) return false;
                if ($ban->unbandate && $ban->unbandate <= time()) return false;
            } elseif ($status === 'expired') {
                if ($ban->active != 1 || !$ban->unbandate || $ban->unbandate > time()) return false;
            } elseif ($status === 'inactive') {
                if ($ban->active != 0) return false;
            }

            // Фильтр по поиску
            if ($search) {
                $searchLower = strtolower($search);
                return str_contains(strtolower($ban->username), $searchLower) ||
                       str_contains(strtolower($ban->email), $searchLower) ||
                       str_contains(strtolower($ban->banreason), $searchLower) ||
                       str_contains(strtolower($ban->character_name), $searchLower);
            }

            return true;
        });

        // Сортируем и пагинируем
        $sortedBans = $filteredBans->sortByDesc('bandate');
        $page = $request->get('page', 1);
        $perPage = 20;
        
        $bans = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedBans->forPage($page, $perPage)->values(),
            $sortedBans->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
                'query' => request()->query()
            ]
        );

        // Статистика с кэшированием (5 минут)
        $stats = Cache::remember('ban_stats', 300, function () {
            $accountBansCount = DB::connection('mysql_auth')->table('account_banned')->count();
            $characterBansCount = DB::connection('mysql_char')->table('character_banned')->count();
            $ipBansCount = DB::connection('mysql_auth')->table('ip_banned')->count();
            
            return [
                'total' => $accountBansCount + $characterBansCount + $ipBansCount,
                'active' => DB::connection('mysql_auth')->table('account_banned')
                    ->where('active', 1)
                    ->where(function($q) {
                        $q->whereNull('unbandate')
                          ->orWhere('unbandate', '>', time());
                    })->count() + 
                    DB::connection('mysql_char')->table('character_banned')
                    ->where('active', 1)
                    ->where(function($q) {
                        $q->whereNull('unbandate')
                          ->orWhere('unbandate', '>', time());
                    })->count() +
                    DB::connection('mysql_auth')->table('ip_banned')
                    ->where(function($q) {
                        $q->where('unbandate', 0)
                          ->orWhere('unbandate', '>', time());
                    })->count(),
                'expired' => DB::connection('mysql_auth')->table('account_banned')
                    ->where('active', 1)
                    ->where('unbandate', '<=', time())
                    ->count() +
                    DB::connection('mysql_char')->table('character_banned')
                    ->where('active', 1)
                    ->where('unbandate', '<=', time())
                    ->count() +
                    DB::connection('mysql_auth')->table('ip_banned')
                    ->where('unbandate', '<=', time())
                    ->count(),
                'permanent' => DB::connection('mysql_auth')->table('account_banned')
                    ->where('active', 1)
                    ->whereNull('unbandate')
                    ->count() +
                    DB::connection('mysql_char')->table('character_banned')
                    ->where('active', 1)
                    ->whereNull('unbandate')
                    ->count() +
                    DB::connection('mysql_auth')->table('ip_banned')
                    ->where('unbandate', 0)
                    ->count()
            ];
        });

        return view('admin.bans.index', compact('bans', 'stats', 'status', 'search'));
    }

    /**
     * Показать детали бана
     */
    public function show($id)
    {
        if (!Auth::check() || !PermissionHelper::hasPermission(Auth::id(), 'bans.view')) {
            return redirect('/login');
        }

        // Сначала пробуем найти бан аккаунта
        $ban = DB::connection('mysql_auth')
            ->table('account_banned as ab')
            ->join('account as a', 'ab.id', '=', 'a.id')
            ->select(
                'ab.*',
                'a.username',
                'a.email',
                'a.last_login',
                DB::raw("'account' as ban_type"),
                DB::raw("NULL as character_name")
            )
            ->where('ab.id', $id)
            ->first();

        // Если не найден бан аккаунта, ищем бан персонажа
        if (!$ban) {
            $characterBan = DB::connection('mysql_char')
                ->table('character_banned as cb')
                ->join('characters as c', 'cb.guid', '=', 'c.guid')
                ->select(
                    'cb.guid as id',
                    'cb.bandate',
                    'cb.unbandate',
                    'cb.bannedby',
                    'cb.banreason',
                    'cb.active',
                    'c.account as account_id',
                    'c.name as character_name'
                )
                ->where('cb.guid', $id)
                ->first();

            if ($characterBan) {
                // Получаем информацию об аккаунте
                $account = DB::connection('mysql_auth')
                    ->table('account')
                    ->select('username', 'email', 'last_login')
                    ->where('id', $characterBan->account_id)
                    ->first();

                if ($account) {
                    $ban = (object)[
                        'id' => $characterBan->id,
                        'bandate' => $characterBan->bandate,
                        'unbandate' => $characterBan->unbandate,
                        'bannedby' => $characterBan->bannedby,
                        'banreason' => $characterBan->banreason,
                        'active' => $characterBan->active,
                        'username' => $account->username,
                        'email' => $account->email,
                        'last_login' => $account->last_login,
                        'ban_type' => 'character',
                        'character_name' => $characterBan->character_name
                    ];
                }
            }
        }

        // Если не найден бан персонажа, ищем IP бан
        if (!$ban) {
            $ipBan = DB::connection('mysql_auth')
                ->table('ip_banned')
                ->select('*')
                ->where('ip', $id)
                ->first();

            if ($ipBan) {
                $ban = (object)[
                    'id' => $ipBan->ip,
                    'bandate' => $ipBan->bandate,
                    'unbandate' => $ipBan->unbandate,
                    'bannedby' => $ipBan->bannedby,
                    'banreason' => $ipBan->banreason,
                    'active' => $ipBan->unbandate === 0 || $ipBan->unbandate > time(),
                    'username' => $ipBan->ip,
                    'email' => null,
                    'last_login' => null,
                    'ban_type' => 'ip',
                    'character_name' => null
                ];
            }
        }

        if (!$ban) {
            return redirect()->route('admin.bans.index')
                ->with('error', __('admin_bans.ban_not_found'));
        }

        // История банов для этого аккаунта/персонажа/IP
        if ($ban->ban_type === 'account') {
            $banHistory = DB::connection('mysql_auth')
                ->table('account_banned')
                ->where('id', $id)
                ->orderBy('bandate', 'desc')
                ->get();
        } elseif ($ban->ban_type === 'character') {
            // Для банов персонажей получаем историю банов этого персонажа
            $banHistory = DB::connection('mysql_char')
                ->table('character_banned')
                ->where('guid', $id)
                ->orderBy('bandate', 'desc')
                ->get();
        } else {
            // Для IP банов получаем историю банов этого IP
            $banHistory = DB::connection('mysql_auth')
                ->table('ip_banned')
                ->where('ip', $id)
                ->orderBy('bandate', 'desc')
                ->get();
        }

        return view('admin.bans.show', compact('ban', 'banHistory'));
    }

    /**
     * Создать новый бан
     */
    public function store(Request $request)
    {
        if (!Auth::check() || !PermissionHelper::hasPermission(Auth::id(), 'bans.create')) {
            return redirect('/login');
        }

        $banType = $request->ban_type;
        
        // Валидация в зависимости от типа бана
        if ($banType === 'account') {
            $request->validate([
                'account_id' => 'required|integer|exists:mysql_auth.account,id',
                'ban_reason' => 'required|string|max:500',
                'ban_duration' => 'nullable|integer|min:1',
                'ban_type' => 'required|in:account,ip,character'
            ]);
        } elseif ($banType === 'ip') {
            $request->validate([
                'ip_address' => 'required|ip',
                'ban_reason' => 'required|string|max:500',
                'ban_duration' => 'nullable|integer|min:1',
                'ban_type' => 'required|in:account,ip,character'
            ]);
        } elseif ($banType === 'character') {
            $request->validate([
                'character_name' => 'required|string|max:12',
                'ban_reason' => 'required|string|max:500',
                'ban_duration' => 'nullable|integer|min:1',
                'ban_type' => 'required|in:account,ip,character'
            ]);
        }

        try {
            $banDate = now();
            $unbanDate = $request->ban_duration ? 
                $banDate->copy()->addDays((int)$request->ban_duration) : null;

            if ($banType === 'account') {
                $accountId = $request->account_id;
                
                // Проверяем, не забанен ли уже аккаунт
                $existingBan = DB::connection('mysql_auth')
                    ->table('account_banned')
                    ->where('id', $accountId)
                    ->where('active', 1)
                    ->where(function($q) {
                        $q->whereNull('unbandate')
                          ->orWhere('unbandate', '>', time());
                    })
                    ->first();

                if ($existingBan) {
                    return redirect()->back()
                        ->with('error', __('admin_bans.account_already_banned'));
                }

                // Создаем бан аккаунта
                DB::connection('mysql_auth')->table('account_banned')->insert([
                    'id' => $accountId,
                    'bandate' => $banDate->timestamp,
                    'unbandate' => $unbanDate ? $unbanDate->timestamp : null,
                    'bannedby' => Auth::user()->username ?? 'Admin Panel',
                    'banreason' => $request->ban_reason,
                    'active' => 1,
                ]);

            } elseif ($banType === 'ip') {
                $ipAddress = $request->ip_address;
                
                // Проверяем, не забанен ли уже IP
                $existingBan = DB::connection('mysql_auth')
                    ->table('ip_banned')
                    ->where('ip', $ipAddress)
                    ->where(function($q) {
                        $q->where('unbandate', 0)
                          ->orWhere('unbandate', '>', time());
                    })
                    ->first();

                if ($existingBan) {
                    return redirect()->back()
                        ->with('error', __('admin_bans.ip_already_banned'));
                }

                // Создаем бан IP
                DB::connection('mysql_auth')->table('ip_banned')->insert([
                    'ip' => $ipAddress,
                    'bandate' => $banDate->timestamp,
                    'unbandate' => $unbanDate ? $unbanDate->timestamp : 0,
                    'bannedby' => Auth::user()->username ?? 'Admin Panel',
                    'banreason' => $request->ban_reason,
                ]);

            } elseif ($banType === 'character') {
                $characterName = $request->character_name;
                
                // Получаем ID персонажа
                $character = DB::connection('mysql_char')
                    ->table('characters')
                    ->where('name', $characterName)
                    ->first();

                if (!$character) {
                    return redirect()->back()
                        ->with('error', __('admin_bans.character_not_found'));
                }

                // Проверяем, не забанен ли уже персонаж
                $existingBan = DB::connection('mysql_char')
                    ->table('character_banned')
                    ->where('guid', $character->guid)
                    ->where('active', 1)
                    ->where(function($q) {
                        $q->whereNull('unbandate')
                          ->orWhere('unbandate', '>', time());
                    })
                    ->first();

                if ($existingBan) {
                    return redirect()->back()
                        ->with('error', __('admin_bans.character_already_banned'));
                }

                // Создаем бан персонажа
                DB::connection('mysql_char')->table('character_banned')->insert([
                    'guid' => $character->guid,
                    'bandate' => $banDate->timestamp,
                    'unbandate' => $unbanDate ? $unbanDate->timestamp : null,
                    'bannedby' => Auth::user()->username ?? 'Admin Panel',
                    'banreason' => $request->ban_reason,
                    'active' => 1,
                ]);
            }


            // Очистить кэш статистики
            $this->clearBanCache();

            return redirect()->route('admin.bans.index')
                ->with('success', __('admin_bans.ban_created_successfully'));

        } catch (\Exception $e) {
            Log::error('Ban creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('admin_bans.ban_creation_failed'));
        }
    }

    /**
     * Разбанить аккаунт
     */
    public function unban($id)
    {
        if (!Auth::check() || !PermissionHelper::hasPermission(Auth::id(), 'bans.unban')) {
            return redirect('/login');
        }

        try {
            // Пробуем разбанить аккаунт
            $updated = DB::connection('mysql_auth')
                ->table('account_banned')
                ->where('id', $id)
                ->where('active', 1)
                ->update(['active' => 0]);

            // Если не найден бан аккаунта, пробуем разбанить персонажа
            if (!$updated) {
                $updated = DB::connection('mysql_char')
                    ->table('character_banned')
                    ->where('guid', $id)
                    ->where('active', 1)
                    ->update(['active' => 0]);
            }

            // IP баны не поддерживают разбан через active, только удаление

            if (!$updated) {
                return redirect()->route('admin.bans.index')
                    ->with('error', __('admin_bans.ban_not_found'));
            }


            // Очистить кэш статистики
            $this->clearBanCache();

            return redirect()->route('admin.bans.index')
                ->with('success', __('admin_bans.unban_successful'));

        } catch (\Exception $e) {
            Log::error('Unban error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('admin_bans.unban_failed'));
        }
    }

    /**
     * Удалить бан (из истории)
     */
    public function destroy($id)
    {
        if (!Auth::check() || !PermissionHelper::hasPermission(Auth::id(), 'bans.delete')) {
            return redirect('/login');
        }

        try {
            $deleted = false;
            
            // Простая проверка: если $id содержит точки, это IP адрес
            if (strpos($id, '.') !== false) {
                Log::info('Attempting to delete IP ban', [
                    'id' => $id,
                    'contains_dot' => strpos($id, '.') !== false
                ]);
                
                $deleted = DB::connection('mysql_auth')
                    ->table('ip_banned')
                    ->where('ip', $id)
                    ->delete();
                
                Log::info('IP ban deletion result', [
                    'deleted' => $deleted,
                    'ip' => $id
                ]);
            } else {
                // Пробуем удалить бан аккаунта
                $deleted = DB::connection('mysql_auth')
                    ->table('account_banned')
                    ->where('id', $id)
                    ->delete();

                // Если не найден бан аккаунта, пробуем удалить бан персонажа
                if (!$deleted) {
                    $deleted = DB::connection('mysql_char')
                        ->table('character_banned')
                        ->where('guid', $id)
                        ->delete();
                }
            }

            if (!$deleted) {
                return redirect()->route('admin.bans.index')
                    ->with('error', __('admin_bans.ban_not_found'));
            }


            // Очистить кэш статистики
            $this->clearBanCache();

            return redirect()->route('admin.bans.index')
                ->with('success', __('admin_bans.ban_deleted_successfully'));

        } catch (\Exception $e) {
            Log::error('Ban deletion error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('admin_bans.ban_deletion_failed'));
        }
    }

    /**
     * Массовые операции с банами
     */
    public function bulkAction(Request $request)
    {
        if (!Auth::check() || !PermissionHelper::hasPermission(Auth::id(), 'bans.delete')) {
            return redirect('/login');
        }

        $request->validate([
            'action' => 'required|in:unban,delete',
            'ban_ids' => 'required|array|min:1',
            'ban_ids.*' => 'integer'
        ]);

        try {
            $action = $request->action;
            $banIds = $request->ban_ids;

            if ($action === 'unban') {
                DB::connection('mysql_auth')
                    ->table('account_banned')
                    ->whereIn('id', $banIds)
                    ->update(['active' => 0]);

                $message = __('admin_bans.bulk_unban_successful', ['count' => count($banIds)]);
            } else {
                DB::connection('mysql_auth')
                    ->table('account_banned')
                    ->whereIn('id', $banIds)
                    ->delete();

                $message = __('admin_bans.bulk_delete_successful', ['count' => count($banIds)]);
            }

            Log::info('Bulk ban action', [
                'action' => $action,
                'ban_ids' => $banIds,
                'performed_by' => Auth::id()
            ]);

            // Очистить кэш статистики
            $this->clearBanCache();

            return redirect()->route('admin.bans.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Bulk ban action error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('admin_bans.bulk_action_failed'));
        }
    }

    /**
     * Получить информацию об аккаунте для бана
     */
    public function getAccountInfo(Request $request)
    {
        // Простая проверка авторизации
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        if (!\App\Models\User::isAdmin(Auth::id())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $search = $request->get('search', '');
        $character = $request->get('character', '');
        $accountCharacters = $request->get('account_characters', '');
        
        // Поиск аккаунтов (для обычного поиска и для поиска аккаунтов для персонажей)
        if ($search) {
            \Log::info('Account search request', [
                'search' => $search,
                'search_length' => strlen($search)
            ]);
            
            if (strlen($search) < 3) {
                $response = response()->json(['accounts' => []]);
                $response->header('Content-Type', 'application/json');
                return $response;
            }

            try {
                // Проверяем подключение к базе данных
                DB::connection('mysql_auth')->getPdo();
                
                $accounts = DB::connection('mysql_auth')
                    ->table('account')
                    ->select('id', 'username', 'email', 'last_login')
                    ->where(function($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->limit(10)
                    ->get();

                \Log::info('Account search results', [
                    'search' => $search,
                    'results_count' => $accounts->count(),
                    'accounts' => $accounts->toArray()
                ]);

                $response = response()->json(['accounts' => $accounts]);
                $response->header('Content-Type', 'application/json');
                $response->header('X-Content-Type-Options', 'nosniff');
                return $response;
            } catch (\Exception $e) {
                \Log::error('Account search error', [
                    'search' => $search,
                    'error' => $e->getMessage()
                ]);
                
                $response = response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
                $response->header('Content-Type', 'application/json');
                $response->header('X-Content-Type-Options', 'nosniff');
                return $response;
            }
        }
        
        // Поиск персонажей по аккаунту
        if ($accountCharacters) {
            $characters = DB::connection('mysql_char')
                ->table('characters as c')
                ->join('account as a', 'c.account', '=', 'a.id')
                ->select(
                    'c.name',
                    'c.level',
                    'c.race',
                    'c.class',
                    'a.username as account_name',
                    'a.id as account_id'
                )
                ->where('c.account', $accountCharacters)
                ->orderBy('c.level', 'desc')
                ->get()
                ->map(function($char) {
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
                    
                    return $char;
                });

            $response = response()->json(['characters' => $characters]);
            $response->header('Content-Type', 'application/json');
            $response->header('X-Content-Type-Options', 'nosniff');
            return $response;
        }
        
        // Поиск персонажей по имени (старая логика)
        if ($character) {
            if (strlen($character) < 2) {
                $response = response()->json(['characters' => []]);
                $response->header('Content-Type', 'application/json');
                return $response;
            }

            $characters = DB::connection('mysql_char')
                ->table('characters as c')
                ->join('account as a', 'c.account', '=', 'a.id')
                ->select(
                    'c.name',
                    'c.level',
                    'c.race',
                    'c.class',
                    'a.username as account_name',
                    'a.id as account_id'
                )
                ->where('c.name', 'like', "%{$character}%")
                ->limit(10)
                ->get()
                ->map(function($char) {
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
                    
                    return $char;
                });

            $response = response()->json(['characters' => $characters]);
            $response->header('Content-Type', 'application/json');
            $response->header('X-Content-Type-Options', 'nosniff');
            return $response;
        }
        
        // Если нет параметров поиска, возвращаем пустой результат
        \Log::info('No search parameters provided', [
            'search' => $search,
            'character' => $character,
            'accountCharacters' => $accountCharacters
        ]);
        $response = response()->json(['accounts' => []]);
        $response->header('Content-Type', 'application/json');
        $response->header('X-Content-Type-Options', 'nosniff');
        return $response;
    }

    /**
     * Очистить кэш статистики банов
     */
    private function clearBanStatsCache()
    {
        Cache::forget('ban_stats');
    }

    /**
     * Очистить кэш при создании/удалении бана
     */
    private function clearBanCache()
    {
        $this->clearBanStatsCache();
        // Можно добавить очистку других связанных кэшей
    }
}
