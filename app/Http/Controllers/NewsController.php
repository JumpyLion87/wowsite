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
        
        $news = News::where('slug', $slug)->first();

        if (!$news) {
            abort(404);
        }

        return view('news.show', compact('news', 'default_image_url'));
    }
}