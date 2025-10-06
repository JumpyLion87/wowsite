<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use Illuminate\Http\Request;

class NewsCommentController extends Controller
{
    /**
     * Список комментариев для модерации
     */
    public function index()
    {
        $comments = NewsComment::with(['news', 'user', 'parent'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.news-comments.index', compact('comments'));
    }

    /**
     * Одобрить комментарий
     */
    public function approve(NewsComment $comment)
    {
        $comment->update(['is_approved' => true]);
        
        return back()->with('success', 'Комментарий одобрен');
    }

    /**
     * Отклонить комментарий
     */
    public function reject(NewsComment $comment)
    {
        $comment->update(['is_approved' => false]);
        
        return back()->with('success', 'Комментарий отклонен');
    }

    /**
     * Удалить комментарий
     */
    public function destroy(NewsComment $comment)
    {
        $comment->delete();
        
        return back()->with('success', 'Комментарий удален');
    }

    /**
     * Массовое одобрение комментариев
     */
    public function bulkApprove(Request $request)
    {
        $commentIds = $request->input('comment_ids', []);
        
        if (!empty($commentIds)) {
            NewsComment::whereIn('id', $commentIds)
                ->update(['is_approved' => true]);
        }
        
        return back()->with('success', 'Выбранные комментарии одобрены');
    }

    /**
     * Массовое отклонение комментариев
     */
    public function bulkReject(Request $request)
    {
        $commentIds = $request->input('comment_ids', []);
        
        if (!empty($commentIds)) {
            NewsComment::whereIn('id', $commentIds)
                ->update(['is_approved' => false]);
        }
        
        return back()->with('success', 'Выбранные комментарии отклонены');
    }

    /**
     * Массовое удаление комментариев
     */
    public function bulkDelete(Request $request)
    {
        $commentIds = $request->input('comment_ids', []);
        
        if (!empty($commentIds)) {
            NewsComment::whereIn('id', $commentIds)->delete();
        }
        
        return back()->with('success', 'Выбранные комментарии удалены');
    }
}
