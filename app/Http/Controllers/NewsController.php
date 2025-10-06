<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display news listing
     */
    public function index(Request $request)
    {
        $items_per_page = 5;
        $default_image_url = 'img/newsimg/news.png';
        
        // News list view
        $current_page = max(1, intval($request->get('page', 1)));
        
        $newsList = News::paginatedWithExcerpt($current_page, $items_per_page);
        $total_pages = News::totalPages($items_per_page);
        $current_page = min($current_page, $total_pages);

        return view('news.index', compact('newsList', 'current_page', 'total_pages', 'default_image_url'));
    }

    /**
     * Display single news article
     */
    public function show($slug)
    {
        $default_image_url = 'img/newsimg/news.png';
        
        \Log::info('News show attempt', [
            'slug' => $slug,
            'request_url' => request()->url()
        ]);
        
        $news = News::where('slug', $slug)->first();

        if (!$news) {
            \Log::warning('News not found', ['slug' => $slug]);
            abort(404);
        }

        \Log::info('News found', [
            'news_id' => $news->id,
            'news_title' => $news->title
        ]);

        // Декодируем HTML контент и очищаем от лишних тегов
        $news->content = html_entity_decode($news->content, ENT_QUOTES, 'UTF-8');
        
        // Очищаем от лишних <br> тегов и исправляем структуру
        $news->content = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/i', '<br>', $news->content); // Убираем двойные <br>
        $news->content = preg_replace('/<p>\s*<br\s*\/?>\s*<\/p>/i', '', $news->content); // Убираем пустые параграфы с <br>
        $news->content = preg_replace('/<br\s*\/?>\s*<\/p>/i', '</p>', $news->content); // Убираем <br> перед закрытием </p>
        $news->content = preg_replace('/<p>\s*<br\s*\/?>/i', '<p>', $news->content); // Убираем <br> после открытия <p>
        $news->content = preg_replace('/<p>\s*<h([1-6])>/i', '<h$1>', $news->content); // Убираем <p> перед заголовками
        $news->content = preg_replace('/<\/h([1-6])>\s*<\/p>/i', '</h$1>', $news->content); // Убираем </p> после заголовков

        // Загружаем комментарии
        $comments = $news->approvedComments()->get();

        return view('news.show', compact('news', 'default_image_url', 'comments'));
    }
}