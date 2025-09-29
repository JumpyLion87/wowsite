<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display news listing or single news article
     */
    public function index(Request $request)
    {
        $slug = $request->get('slug');
        $items_per_page = 5;
        $default_image_url = 'img/newsimg/news.png';

        if ($slug) {
            // Single news article view
            $news = News::where('slug', $slug)->first();

            if (!$news) {
                abort(404);
            }

            return view('news.show', compact('news', 'default_image_url'));
        } else {
            // News list view
            $current_page = max(1, intval($request->get('page', 1)));
            
            $newsList = News::paginatedWithExcerpt($current_page, $items_per_page);
            $total_pages = News::totalPages($items_per_page);
            $current_page = min($current_page, $total_pages);

            return view('news.index', compact('newsList', 'current_page', 'total_pages', 'default_image_url'));
        }
    }
}