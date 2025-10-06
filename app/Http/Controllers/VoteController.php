<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Services\VoteCheckService;

class VoteController extends Controller
{
    protected $voteCheckService;

    public function __construct(VoteCheckService $voteCheckService)
    {
        $this->voteCheckService = $voteCheckService;
    }

    /**
     * Перенаправление на страницу голосования MMOTOP
     */
    public function redirectToVote(Request $request)
    {
        $user = Auth::user();
        $mmotopUrl = env('MMOTOP_SERVER_URL', 'https://wow.mmotop.ru/servers/37477/votes/new');

        // Логируем попытку голосования
        DB::connection('mysql')->table('website_activity_log')->insert([
            'account_id' => $user->id,
            'action' => 'vote_redirect',
            'timestamp' => time(), // UNIX timestamp
            'details' => 'Переход на страницу голосования MMOTOP',
        ]);

        // Перенаправляем на MMOTOP
        return redirect($mmotopUrl);
    }

    /**
     * Проверить голос пользователя (AJAX)
     */
    public function checkVote(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = Auth::user();
        $result = $this->voteCheckService->checkUserVote($user);

        return response()->json($result);
    }

    /**
     * Получить информацию о голосовании
     */
    public function getVoteInfo(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = Auth::user();
        $voteInfo = $this->voteCheckService->getVoteInfo($user);

        return response()->json($voteInfo);
    }
}