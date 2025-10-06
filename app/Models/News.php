<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class News extends Model
{
    protected $table = 'server_news';
    
    // Отключаем автоматические timestamps
    public $timestamps = false;
    
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
                        // Декодируем HTML контент перед созданием excerpt
                        $decodedContent = html_entity_decode($news->content, ENT_QUOTES, 'UTF-8');
                        $news->excerpt = substr(strip_tags($decodedContent), 0, $excerptLength);
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

    /**
     * Связь с комментариями
     */
    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class, 'news_id');
    }

    /**
     * Получить одобренные комментарии
     */
    public function approvedComments()
    {
        return $this->comments()
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc');
    }
}
