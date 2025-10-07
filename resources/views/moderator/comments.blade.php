@extends('layouts.app')

@section('title', 'Модерация комментариев')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Модерация комментариев</h1>
                <a href="{{ route('moderator.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Назад к панели
                </a>
            </div>

            <!-- Фильтры -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Фильтры</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('moderator.comments') }}" class="btn btn-outline-primary btn-sm">
                                Все комментарии
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('moderator.comments', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-sm">
                                Ожидающие модерации
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('moderator.comments', ['status' => 'approved']) }}" class="btn btn-outline-success btn-sm">
                                Одобренные
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('moderator.comments', ['status' => 'rejected']) }}" class="btn btn-outline-danger btn-sm">
                                Отклоненные
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Список комментариев -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Комментарии</h6>
                </div>
                <div class="card-body">
                    @if($comments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Пользователь</th>
                                        <th>Новость</th>
                                        <th>Комментарий</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comments as $comment)
                                        <tr>
                                            <td>{{ $comment->id }}</td>
                                            <td>
                                                <strong>{{ $comment->user->username ?? 'Неизвестно' }}</strong>
                                                @if($comment->user)
                                                    <br><small class="text-muted">{{ $comment->user->email }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('news.show', $comment->news->id) }}" target="_blank">
                                                    {{ Str::limit($comment->news->title ?? 'Удалена', 30) }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="comment-content">
                                                    {{ Str::limit($comment->content, 100) }}
                                                </div>
                                                @if(strlen($comment->content) > 100)
                                                    <button class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#commentModal{{ $comment->id }}">
                                                        Показать полностью
                                                    </button>
                                                @endif
                                            </td>
                                            <td>
                                                @if($comment->is_approved)
                                                    <span class="badge bg-success">Одобрен</span>
                                                @else
                                                    <span class="badge bg-warning">Ожидает</span>
                                                @endif
                                            </td>
                                            <td>{{ $comment->created_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if(!$comment->is_approved)
                                                        <form method="POST" action="{{ route('moderator.comments.approve', $comment->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm" title="Одобрить">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($comment->is_approved)
                                                        <form method="POST" action="{{ route('moderator.comments.reject', $comment->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning btn-sm" title="Отклонить">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    <form method="POST" action="{{ route('moderator.comments.delete', $comment->id) }}" class="d-inline" onsubmit="return confirm('Удалить комментарий?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Удалить">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Пагинация -->
                        <div class="d-flex justify-content-center">
                            {{ $comments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Нет комментариев для отображения</h5>
                            <p class="text-muted">Попробуйте изменить фильтры</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна для полного текста комментариев -->
@foreach($comments as $comment)
    @if(strlen($comment->content) > 100)
        <div class="modal fade" id="commentModal{{ $comment->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Комментарий #{{ $comment->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Пользователь:</strong> {{ $comment->user->username ?? 'Неизвестно' }}</p>
                        <p><strong>Новость:</strong> {{ $comment->news->title ?? 'Удалена' }}</p>
                        <p><strong>Комментарий:</strong></p>
                        <div class="border p-3 bg-light">
                            {{ $comment->content }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
