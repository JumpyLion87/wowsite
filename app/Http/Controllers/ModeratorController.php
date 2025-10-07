<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NewsComment;
use App\Models\News;

class ModeratorController extends Controller
{
    /**
     * Dashboard модератора
     */
    public function dashboard()
    {
        // Проверяем права модератора
        if (!Auth::check() || (!Auth::user()->isAdministrator() && !Auth::user()->isModerator())) {
            abort(403, 'Доступ запрещен. Требуются права модератора или администратора.');
        }

        // Статистика для модератора
        $pendingComments = NewsComment::where('is_approved', false)->count();
        $approvedComments = NewsComment::where('is_approved', true)->count();
        $totalComments = NewsComment::count();
        $recentComments = NewsComment::with(['user', 'news'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'pending_comments' => $pendingComments,
            'approved_comments' => $approvedComments,
            'total_comments' => $totalComments,
            'recent_comments' => $recentComments
        ];

        return view('moderator.dashboard', compact('stats'));
    }

    /**
     * Список комментариев для модерации
     */
    public function comments()
    {
        // Проверяем права модератора
        if (!Auth::check() || (!Auth::user()->isAdministrator() && !Auth::user()->isModerator())) {
            abort(403, 'Доступ запрещен. Требуются права модератора или администратора.');
        }

        $comments = NewsComment::with(['user', 'news'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('moderator.comments', compact('comments'));
    }

    /**
     * Одобрить комментарий
     */
    public function approveComment($id)
    {
        // Проверяем права модератора
        if (!Auth::check() || (!Auth::user()->isAdministrator() && !Auth::user()->isModerator())) {
            abort(403, 'Доступ запрещен. Требуются права модератора или администратора.');
        }

        $comment = NewsComment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Комментарий одобрен');
    }

    /**
     * Отклонить комментарий
     */
    public function rejectComment($id)
    {
        // Проверяем права модератора
        if (!Auth::check() || (!Auth::user()->isAdministrator() && !Auth::user()->isModerator())) {
            abort(403, 'Доступ запрещен. Требуются права модератора или администратора.');
        }

        $comment = NewsComment::findOrFail($id);
        $comment->update(['is_approved' => false]);

        return redirect()->back()->with('success', 'Комментарий отклонен');
    }

    /**
     * Удалить комментарий
     */
    public function deleteComment($id)
    {
        // Проверяем права модератора
        if (!Auth::check() || (!Auth::user()->isAdministrator() && !Auth::user()->isModerator())) {
            abort(403, 'Доступ запрещен. Требуются права модератора или администратора.');
        }

        $comment = NewsComment::findOrFail($id);
        $comment->delete();

        return redirect()->back()->with('success', 'Комментарий удален');
    }
}
