@extends('layouts.app')

@section('title', $news->title)

@section('content')
<div class="main-content">
    <div class="wow-news-container">
        <article class="news-single">
            <div class="news-header">
                <h1 class="news-title">{{ $news->title }}</h1>
                <div class="news-meta">
                    <span class="category {{ $news->category }}">
                        {{ trans()->has('news.category_' . $news->category) ? __('news.category_' . $news->category) : ucfirst($news->category) }}
                    </span>
                    <span class="date">{{ $news->post_date->format('F j, Y') }}</span>
                    <span class="author">{{ __('news.posted_by', ['author' => $news->posted_by]) }}</span>
                </div>
            </div>

            @if(!empty($news->image_url))
                <div class="news-image-container">
                    <img src="{{ asset($news->image_url) }}" 
                         alt="{{ $news->title }}" 
                         class="news-image-large"
                         onerror="this.src='{{ asset($default_image_url) }}'">
                </div>
            @else
                <div class="news-image-container">
                    <img src="{{ asset($default_image_url) }}" 
                         alt="{{ $news->title }}" 
                         class="news-image-large">
                </div>
            @endif

            <div class="news-content">
                {!! $news->content !!}
            </div>

            <div class="news-footer">
                <div class="news-navigation">
                    <a href="{{ route('news.index') }}" class="btn btn-primary">
                        « {{ __('news.back_to_list') }}
                    </a>
                </div>
                
                @if($news->is_important)
                    <div class="news-important-badge">
                        <span class="badge badge-warning">{{ __('news.important_news') }}</span>
                    </div>
                @endif
            </div>
        </article>

        <!-- Комментарии -->
        <div class="news-comments-section">
            <h3 class="comments-title">
                <i class="fas fa-comments me-2"></i>
                Комментарии ({{ $comments->count() }})
            </h3>

            @auth
                <!-- Форма добавления комментария -->
                <div class="comment-form-container">
                    <form action="{{ route('news.comments.store', $news->slug) }}" method="POST" class="comment-form">
                        @csrf
                        <div class="form-group">
                            <label for="comment-content" class="form-label">Добавить комментарий:</label>
                            <textarea id="comment-content" name="content" 
                                      class="form-control @error('content') is-invalid @enderror" 
                                      rows="4" 
                                      placeholder="Напишите ваш комментарий..." 
                                      required></textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>
                            Отправить комментарий
                        </button>
                    </form>
                </div>
            @else
                <div class="comment-login-prompt">
                    <p>Для добавления комментариев необходимо <a href="{{ route('login') }}">войти в систему</a>.</p>
                </div>
            @endauth

            <!-- Список комментариев -->
            <div class="comments-list">
                @forelse($comments as $comment)
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-author">
                                <i class="fas fa-user me-1"></i>
                                {{ $comment->user->username }}
                            </div>
                            <div class="comment-date">
                                {{ $comment->created_at->format('d.m.Y H:i') }}
                            </div>
                        </div>
                        <div class="comment-content">
                            {{ $comment->content }}
                        </div>
                        
                        @auth
                            <div class="comment-actions">
                                <button class="btn btn-sm btn-outline-primary reply-btn" 
                                        data-comment-id="{{ $comment->id }}">
                                    <i class="fas fa-reply me-1"></i>
                                    Ответить
                                </button>
                            </div>
                            
                            <!-- Форма ответа (скрыта по умолчанию) -->
                            <div class="reply-form" id="reply-form-{{ $comment->id }}" style="display: none;">
                                <form action="{{ route('news.comments.store', $news->slug) }}" method="POST" class="reply-comment-form">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <div class="form-group">
                                        <textarea name="content" 
                                                  class="form-control" 
                                                  rows="3" 
                                                  placeholder="Ответить на комментарий..." 
                                                  required></textarea>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-paper-plane me-1"></i>
                                            Отправить ответ
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm cancel-reply">
                                            Отмена
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endauth

                        <!-- Ответы на комментарий -->
                        @if($comment->replies->count() > 0)
                            <div class="comment-replies">
                                @foreach($comment->replies as $reply)
                                    <div class="comment-item reply-item">
                                        <div class="comment-header">
                                            <div class="comment-author">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $reply->user->username }}
                                            </div>
                                            <div class="comment-date">
                                                {{ $reply->created_at->format('d.m.Y H:i') }}
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            {{ $reply->content }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="no-comments">
                        <p>Пока нет комментариев. Будьте первым!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка кнопок "Ответить"
    const replyButtons = document.querySelectorAll('.reply-btn');
    replyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const replyForm = document.getElementById('reply-form-' + commentId);
            
            if (replyForm) {
                // Скрыть все другие формы ответов
                document.querySelectorAll('.reply-form').forEach(form => {
                    if (form.id !== 'reply-form-' + commentId) {
                        form.style.display = 'none';
                    }
                });
                
                // Показать/скрыть текущую форму
                if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                    replyForm.style.display = 'block';
                } else {
                    replyForm.style.display = 'none';
                }
            }
        });
    });
    
    // Обработка кнопок "Отмена"
    const cancelButtons = document.querySelectorAll('.cancel-reply');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const replyForm = this.closest('.reply-form');
            if (replyForm) {
                replyForm.style.display = 'none';
                // Очистить форму
                const textarea = replyForm.querySelector('textarea');
                if (textarea) {
                    textarea.value = '';
                }
            }
        });
    });
    
    // Обработка отправки форм комментариев
    const commentForms = document.querySelectorAll('.comment-form, .reply-comment-form');
    commentForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const textarea = this.querySelector('textarea[name="content"]');
            if (textarea && textarea.value.trim() === '') {
                e.preventDefault();
                alert('Пожалуйста, введите текст комментария');
                return false;
            }
        });
    });
});
</script>
@endpush
