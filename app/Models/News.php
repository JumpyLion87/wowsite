<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'server_news';
    
    protected $fillable = [
        'title',
        'slug',
        'content',
        'image_url',
        'posted_by',
        'post_date',
        'is_important',
        'category'
    ];
    
    protected $casts = [
        'post_date' => 'datetime',
        'is_important' => 'boolean'
    ];

    /**
     * Get paginated news with excerpts
     */
    public static function paginatedWithExcerpt($page = 1, $perPage = 5, $excerptLength = 200)
    {
        $offset = ($page - 1) * $perPage;
        
        return self::orderBy('post_date', 'desc')
                    ->offset($offset)
                    ->limit($perPage)
                    ->get()
                    ->map(function ($news) use ($excerptLength) {
                        $news->excerpt = substr(strip_tags($news->content), 0, $excerptLength);
                        return $news;
                    });
    }

    /**
     * Get news by slug
     */
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Get total pages count for pagination
     */
    public static function totalPages($perPage = 5)
    {
        $total = self::count();
        return ceil($total / $perPage);
    }
}
