<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsCommentController extends Controller
{
    /**
     * Сохранить новый комментарий
     */
    public function store(Request $request, $newsSlug)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:news_comments,id'
        ]);

        $news = News::where('slug', $newsSlug)->firstOrFail();

        $comment = NewsComment::create([
            'news_id' => $news->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
            'is_approved' => false // Комментарии требуют модерации
        ]);

        return back()->with('success', __('news.comment_sent_for_moderation'));
    }

    /**
     * Получить комментарии для новости (AJAX)
     */
    public function getComments($newsSlug)
    {
        $news = News::where('slug', $newsSlug)->firstOrFail();
        $comments = $news->approvedComments()->get();

        return response()->json($comments);
    }
}
