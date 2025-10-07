@extends('layouts.app')

@section('title', __('admin_bans.ban_details'))

@section('content')
<div class="dashboard-container">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="admin-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ \App\Helpers\DashboardHelper::getDashboardRoute() }}">{{ \App\Helpers\DashboardHelper::getDashboardTitle() }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.bans.index') }}">{{ __('admin_bans.ban_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('admin_bans.ban_details') }}</li>
        </ol>
    </nav>

    <!-- Заголовок -->
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-ban me-3"></i>
                {{ __('admin_bans.ban_details') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_bans.ban_details_description') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.bans.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_bans.back_to_bans') }}
            </a>
            @if($ban->active)
                <form method="POST" action="{{ route('admin.bans.unban', $ban->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success me-2"
                            onclick="return confirm('{{ __('admin_bans.unban_confirm') }}')">
                        <i class="fas fa-unlock me-2"></i>
                        {{ __('admin_bans.unban') }}
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.bans.destroy', $ban->id) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('{{ __('admin_bans.delete_confirm') }}')">
                    <i class="fas fa-trash me-2"></i>
                    {{ __('admin_bans.delete') }}
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Основная информация о бане -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('admin_bans.ban_information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>{{ __('admin_bans.user') }}:</label>
                                <div class="info-value">
                                    <strong>{{ $ban->username }}</strong>
                                    <small class="text-muted d-block">ID: {{ $ban->id }}</small>
                                    @if($ban->character_name)
                                        <small class="text-info d-block">Персонаж: {{ $ban->character_name }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>{{ __('admin_bans.type') }}:</label>
                                <div class="info-value">
                                    @if($ban->ban_type === 'character')
                                        <span class="badge badge-info">{{ __('admin_bans.character_ban') }}</span>
                                    @elseif($ban->ban_type === 'ip')
                                        <span class="badge badge-danger">{{ __('admin_bans.ip_ban') }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ __('admin_bans.account_ban') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>{{ __('admin_bans.status') }}:</label>
                                <div class="info-value">
                                    @if($ban->active)
                                        @if($ban->unbandate && $ban->unbandate <= time())
                                            <span class="badge badge-warning">{{ __('admin_bans.expired') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __('admin_bans.active') }}</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">{{ __('admin_bans.inactive') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>{{ __('admin_bans.banned_by') }}:</label>
                                <div class="info-value">
                                    {{ $ban->bannedby ?: __('admin_bans.system') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>{{ __('admin_bans.ban_date') }}:</label>
                                <div class="info-value">
                                    {{ $ban->bandate ? \Carbon\Carbon::createFromTimestamp($ban->bandate)->format('M j, Y H:i:s') : __('admin_bans.unknown') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>{{ __('admin_bans.unban_date') }}:</label>
                                <div class="info-value">
                                    @if($ban->unbandate)
                                        {{ \Carbon\Carbon::createFromTimestamp($ban->unbandate)->format('M j, Y H:i:s') }}
                                    @else
                                        <span class="permanent-ban">{{ __('admin_bans.permanent') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>{{ __('admin_bans.duration') }}:</label>
                                <div class="info-value">
                                    @if($ban->unbandate)
                                        {{ \Carbon\Carbon::createFromTimestamp($ban->bandate)->diffForHumans(\Carbon\Carbon::createFromTimestamp($ban->unbandate), true) }}
                                    @else
                                        {{ __('admin_bans.permanent') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>{{ __('admin_bans.email') }}:</label>
                                <div class="info-value">
                                    {{ $ban->email ?: __('admin_bans.not_provided') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <label>{{ __('admin_bans.reason') }}:</label>
                        <div class="info-value">
                            <div class="ban-reason-text">
                                {{ $ban->banreason ?: __('admin_bans.no_reason') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- История банов -->
            @if($banHistory->count() > 1)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>
                            {{ __('admin_bans.ban_history') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($banHistory as $index => $historyBan)
                                <div class="timeline-item {{ $index === 0 ? 'current' : '' }}">
                                    <div class="timeline-marker">
                                        <i class="fas fa-{{ $historyBan->active ? 'ban' : 'unlock' }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <h6>
                                                @if($historyBan->active)
                                                    {{ __('admin_bans.banned') }}
                                                @else
                                                    {{ __('admin_bans.unbanned') }}
                                                @endif
                                                @if($ban->ban_type === 'ip')
                                                    <br><small class="text-info">IP: {{ $historyBan->ip ?? $ban->id }}</small>
                                                @endif
                                            </h6>
                                            <span class="timeline-date">
                                                {{ \Carbon\Carbon::createFromTimestamp($historyBan->bandate)->format('M j, Y H:i') }}
                                            </span>
                                        </div>
                                        <div class="timeline-body">
                                            @if($historyBan->banreason)
                                                <p class="mb-1">{{ $historyBan->banreason }}</p>
                                            @endif
                                            <small class="text-muted">
                                                {{ __('admin_bans.by') }}: {{ $historyBan->bannedby ?: __('admin_bans.system') }}
                                                @if($ban->ban_type === 'ip')
                                                    <br><strong>IP:</strong> {{ $historyBan->ip ?? $ban->id }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <!-- Информация об аккаунте -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ __('admin_bans.account_information') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($ban->ban_type === 'ip')
                        <div class="info-item mb-3">
                            <label>{{ __('admin_bans.ip_address') }}:</label>
                            <div class="info-value">{{ $ban->username }}</div>
                        </div>
                    @else
                        <div class="info-item mb-3">
                            <label>{{ __('admin_bans.username') }}:</label>
                            <div class="info-value">{{ $ban->username }}</div>
                        </div>
                        
                        @if($ban->email)
                            <div class="info-item mb-3">
                                <label>{{ __('admin_bans.email') }}:</label>
                                <div class="info-value">{{ $ban->email }}</div>
                            </div>
                        @endif
                        
                        <div class="info-item mb-3">
                            <label>{{ __('admin_bans.account_id') }}:</label>
                            <div class="info-value">{{ $ban->id }}</div>
                        </div>
                    @endif
                    
                    @if($ban->last_login && $ban->ban_type !== 'ip')
                        <div class="info-item mb-3">
                            <label>{{ __('admin_bans.last_login') }}:</label>
                            <div class="info-value">
                                {{ \Carbon\Carbon::createFromTimestamp($ban->last_login)->format('M j, Y H:i') }}
                            </div>
                        </div>
                    @endif
                    
                    @if($ban->ban_type !== 'ip')
                        <div class="mt-3">
                            <a href="{{ route('admin.users') }}#user-{{ $ban->id }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-user me-1"></i>
                                {{ __('admin_bans.view_account') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ __('admin_bans.quick_actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($ban->active && $ban->ban_type !== 'ip')
                        <form method="POST" action="{{ route('admin.bans.unban', $ban->id) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100"
                                    onclick="return confirm('{{ __('admin_bans.unban_confirm') }}')">
                                <i class="fas fa-unlock me-1"></i>
                                @if($ban->ban_type === 'character')
                                    {{ __('admin_bans.unban_character') }}
                                @else
                                    {{ __('admin_bans.unban_account') }}
                                @endif
                            </button>
                        </form>
                    @elseif($ban->ban_type === 'ip')
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('admin_bans.ip_ban_info') }}
                        </div>
                    @else
                        <button class="btn btn-secondary btn-sm w-100" disabled>
                            <i class="fas fa-check me-1"></i>
                            {{ __('admin_bans.already_unbanned') }}
                        </button>
                    @endif
                    
                    <form method="POST" action="{{ route('admin.bans.destroy', $ban->id) }}" class="mb-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100"
                                onclick="return confirm('{{ __('admin_bans.delete_confirm') }}')">
                            <i class="fas fa-trash me-1"></i>
                            {{ __('admin_bans.delete_ban') }}
                        </button>
                    </form>
                    
                    <a href="{{ route('admin.bans.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('admin_bans.back_to_list') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.info-item {
    margin-bottom: 1rem;
}

.info-item label {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.25rem;
    display: block;
}

.info-value {
    color: #212529;
}

.ban-reason-text {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.375rem;
    border-left: 4px solid #dc3545;
}

.permanent-ban {
    color: #dc3545;
    font-weight: 600;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item.current .timeline-marker {
    background: #dc3545;
    color: white;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 3px solid #dee2e6;
}

.timeline-item.current .timeline-content {
    border-left-color: #dc3545;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.timeline-header h6 {
    margin: 0;
    color: #495057;
}

.timeline-date {
    color: #6c757d;
    font-size: 0.875rem;
}

.timeline-body p {
    margin-bottom: 0.5rem;
}
</style>
@endpush
@endsection
