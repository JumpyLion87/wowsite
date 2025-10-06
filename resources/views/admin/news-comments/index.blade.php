@extends('layouts.app')

@section('title', __('admin_news_comments.comments_moderation'))

@section('content')
<div class="dashboard-container">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="admin-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_news_comments.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-comments me-1"></i>
                {{ __('admin_news_comments.comments_moderation') }}
            </li>
        </ol>
    </nav>

    <div class="admin-header">
        <h1 class="admin-title">
            <i class="fas fa-comments me-2"></i>
            {{ __('admin_news_comments.comments_moderation') }}
        </h1>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_news_comments.back_to_dashboard') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="admin-content">
        <!-- Массовые действия -->
        <div class="bulk-actions mb-4">
            <form id="bulk-form" method="POST" action="">
                @csrf
                <div class="bulk-controls">
                    <button type="button" class="btn btn-success" onclick="bulkAction('approve')">
                        <i class="fas fa-check me-2"></i>
                        {{ __('admin_news_comments.bulk_approve') }}
                    </button>
                    <button type="button" class="btn btn-warning" onclick="bulkAction('reject')">
                        <i class="fas fa-times me-2"></i>
                        {{ __('admin_news_comments.bulk_reject') }}
                    </button>
                    <button type="button" class="btn btn-danger" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-2"></i>
                        {{ __('admin_news_comments.bulk_delete') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Фильтры -->
        <div class="admin-filters mb-4">
            <div class="filter-group">
                <label for="status-filter" class="filter-label">{{ __('admin_news_comments.filter_by_status') }}:</label>
                <select id="status-filter" class="form-control">
                    <option value="">{{ __('admin_news_comments.all_statuses') }}</option>
                    <option value="pending">{{ __('admin_news_comments.pending') }}</option>
                    <option value="approved">{{ __('admin_news_comments.approved') }}</option>
                    <option value="rejected">{{ __('admin_news_comments.rejected') }}</option>
                </select>
            </div>
        </div>

        <!-- Таблица комментариев -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="checkbox-column">
                            <input type="checkbox" id="select-all" class="form-check-input">
                        </th>
                        <th>{{ __('admin_news_comments.user') }}</th>
                        <th>{{ __('admin_news_comments.news') }}</th>
                        <th>{{ __('admin_news_comments.comment') }}</th>
                        <th>{{ __('admin_news_comments.date') }}</th>
                        <th>{{ __('admin_news_comments.status') }}</th>
                        <th>{{ __('admin_news_comments.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comments as $comment)
                        <tr class="comment-row" data-status="{{ $comment->is_approved ? 'approved' : 'pending' }}">
                            <td class="checkbox-column">
                                <input type="checkbox" name="comment_ids[]" value="{{ $comment->id }}" class="form-check-input comment-checkbox">
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-details">
                                        <div class="username">{{ $comment->user->username }}</div>
                                        <div class="user-id">ID: {{ $comment->user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="news-info">
                                    <div class="news-title">{{ Str::limit($comment->news->title, 30) }}</div>
                                    <div class="news-slug">{{ $comment->news->slug }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="comment-content">
                                    {{ Str::limit($comment->content, 100) }}
                                    @if($comment->parent)
                                        <div class="reply-indicator">
                                            <i class="fas fa-reply me-1"></i>
                                            {{ __('admin_news_comments.reply_to_comment') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="date-info">
                                    <div class="date">{{ $comment->created_at->format('d.m.Y') }}</div>
                                    <div class="time">{{ $comment->created_at->format('H:i') }}</div>
                                </div>
                            </td>
                            <td>
                                @if($comment->is_approved)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check me-1"></i>
                                        {{ __('admin_news_comments.approved') }}
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ __('admin_news_comments.pending') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if(!$comment->is_approved)
                                        <form action="{{ route('admin.news-comments.approve', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="{{ __('admin_news_comments.approve') }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($comment->is_approved)
                                        <form action="{{ route('admin.news-comments.reject', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="{{ __('admin_news_comments.reject') }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form action="{{ route('admin.news-comments.destroy', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('admin_news_comments.delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin_news_comments.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <p>{{ __('admin_news_comments.no_comments') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        @if($comments->hasPages())
            <div class="pagination-container">
                {{ $comments->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Выбор всех комментариев
    const selectAllCheckbox = document.getElementById('select-all');
    const commentCheckboxes = document.querySelectorAll('.comment-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        commentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Фильтрация по статусу
    const statusFilter = document.getElementById('status-filter');
    const commentRows = document.querySelectorAll('.comment-row');
    
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value;
        
        commentRows.forEach(row => {
            if (selectedStatus === '' || row.dataset.status === selectedStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

function bulkAction(action) {
    const selectedComments = document.querySelectorAll('.comment-checkbox:checked');
    const commentIds = Array.from(selectedComments).map(cb => cb.value);
    
    if (commentIds.length === 0) {
        alert('{{ __('admin_news_comments.select_comments_first') }}');
        return;
    }
    
    let actionText = '';
    let actionUrl = '';
    
    switch(action) {
        case 'approve':
            actionText = '{{ __('admin_news_comments.approve') }}';
            actionUrl = '{{ route("admin.news-comments.bulk-approve") }}';
            break;
        case 'reject':
            actionText = '{{ __('admin_news_comments.reject') }}';
            actionUrl = '{{ route("admin.news-comments.bulk-reject") }}';
            break;
        case 'delete':
            actionText = '{{ __('admin_news_comments.delete') }}';
            actionUrl = '{{ route("admin.news-comments.bulk-delete") }}';
            break;
    }
    
    if (confirm('{{ __('admin_news_comments.bulk_action_confirm') }}'.replace('{action}', actionText))) {
        const form = document.getElementById('bulk-form');
        form.action = actionUrl;
        
        // Добавляем скрытые поля с ID комментариев
        commentIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'comment_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        form.submit();
    }
}
</script>
@endpush
@endsection
