<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsComment extends Model
{
    protected $fillable = [
        'news_id',
        'user_id',
        'content',
        'is_approved',
        'parent_id'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Связь с новостью
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с родительским комментарием
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(NewsComment::class, 'parent_id');
    }

    /**
     * Связь с дочерними комментариями
     */
    public function replies(): HasMany
    {
        return $this->hasMany(NewsComment::class, 'parent_id');
    }

    /**
     * Получить одобренные комментарии для новости
     */
    public static function getApprovedForNews($newsId)
    {
        return self::where('news_id', $newsId)
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
