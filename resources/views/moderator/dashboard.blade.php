@extends('layouts.app')

@section('title', 'Панель модератора')

@section('content')
<div class="dashboard-container">
    <div class="row">
        <div class="col-12">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Панель модератора</h1>
                <div class="text-muted">
                    <i class="fas fa-user-shield me-2"></i>
                    Добро пожаловать, {{ Auth::user()->username }}
                </div>
            </div>

            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Ожидают модерации
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $stats['pending_comments'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Одобрено
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $stats['approved_comments'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Всего комментариев
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $stats['total_comments'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-comments fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Статус
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Модератор
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Управление</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('admin.news-comments.index') }}" class="btn btn-warning btn-block">
                                        <i class="fas fa-comments me-2"></i>
                                        Модерация комментариев
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('admin.news.index') }}" class="btn btn-info btn-block">
                                        <i class="fas fa-newspaper me-2"></i>
                                        Управление новостями
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('admin.bans.index') }}" class="btn btn-danger btn-block">
                                        <i class="fas fa-ban me-2"></i>
                                        Управление банами
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('home') }}" class="btn btn-secondary btn-block">
                                        <i class="fas fa-home me-2"></i>
                                        На главную
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Последние комментарии -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Последние комментарии</h6>
                        </div>
                        <div class="card-body">
                            @if($stats['recent_comments']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Пользователь</th>
                                                <th>Новость</th>
                                                <th>Комментарий</th>
                                                <th>Статус</th>
                                                <th>Дата</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stats['recent_comments'] as $comment)
                                                <tr>
                                                    <td>{{ $comment->user->username ?? 'Неизвестно' }}</td>
                                                    <td>{{ Str::limit($comment->news->title ?? 'Удалена', 30) }}</td>
                                                    <td>{{ Str::limit($comment->content, 50) }}</td>
                                                    <td>
                                                        @if($comment->is_approved)
                                                            <span class="badge bg-success">Одобрен</span>
                                                        @else
                                                            <span class="badge bg-warning">Ожидает</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $comment->created_at->format('d.m.Y H:i') }}</td>
                                                    <td>
                                                        @if(!$comment->is_approved)
                                                            <form method="POST" action="{{ route('admin.news-comments.approve', $comment->id) }}" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success btn-sm">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <a href="{{ route('admin.news-comments.index') }}" class="btn btn-info btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">Нет комментариев для отображения</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
