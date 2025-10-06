@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Заголовок -->
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-users me-3"></i>
                {{ __('admin_users.title') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_users.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_users.back_to_dashboard') }}
            </a>
        </div>
    </div>

    <!-- Хлебные крошки -->
    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_users.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-users me-1"></i>
                {{ __('admin_users.title') }}
            </li>
        </ol>
    </nav>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['total_users'] }}</h3>
                <p class="stat-label">{{ __('admin_users.total_users') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-circle text-success"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['online_users'] }}</h3>
                <p class="stat-label">{{ __('admin_users.online_users') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ban text-danger"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['banned_users'] }}</h3>
                <p class="stat-label">{{ __('admin_users.banned_users') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-shield text-warning"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['admin_users'] }}</h3>
                <p class="stat-label">{{ __('admin_users.admin_users') }}</p>
            </div>
        </div>
    </div>

    <!-- Основной контент -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list me-2"></i>
                {{ __('admin_users.users_list') }}
            </h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-refresh" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Форма поиска и фильтрации -->
            <form class="search-form" method="GET" action="{{ route('admin.users') }}">
                <div class="search-row">
                    <div class="search-group">
                        <input type="text" name="search_username" class="form-control" 
                               placeholder="{{ __('admin_users.search_username') }}" 
                               value="{{ $searchUsername }}">
                    </div>
                    <div class="search-group">
                        <input type="text" name="search_email" class="form-control" 
                               placeholder="{{ __('admin_users.search_email') }}" 
                               value="{{ $searchEmail }}">
                    </div>
                    <div class="search-group">
                        <select name="role_filter" class="form-select">
                            <option value="">{{ __('admin_users.all_roles') }}</option>
                            <option value="user" {{ $roleFilter == 'user' ? 'selected' : '' }}>User</option>
                            <option value="moderator" {{ $roleFilter == 'moderator' ? 'selected' : '' }}>Moderator</option>
                            <option value="admin" {{ $roleFilter == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <select name="status_filter" class="form-select">
                            <option value="">{{ __('admin_users.all_status') }}</option>
                            <option value="active" {{ $statusFilter == 'active' ? 'selected' : '' }}>{{ __('admin_users.active') }}</option>
                            <option value="banned" {{ $statusFilter == 'banned' ? 'selected' : '' }}>{{ __('admin_users.banned') }}</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <select name="per_page" class="form-select">
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20 {{ __('admin_users.per_page') }}</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 {{ __('admin_users.per_page') }}</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 {{ __('admin_users.per_page') }}</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search me-1"></i>
                            {{ __('admin_users.search') }}
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Таблица пользователей -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('admin_users.table_username') }}</th>
                            <th>{{ __('admin_users.table_email') }}</th>
                            <th>{{ __('admin_users.table_role') }}</th>
                            <th>{{ __('admin_users.table_points') }}</th>
                            <th>{{ __('admin_users.table_tokens') }}</th>
                            <th>{{ __('admin_users.table_status') }}</th>
                            <th>{{ __('admin_users.table_joined') }}</th>
                            <th>{{ __('admin_users.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="username-cell">
                                    <div class="user-info">
                                        <span class="username">{{ $user->username }}</span>
                                        <small class="user-id">ID: {{ $user->account_id }}</small>
                                    </div>
                                </td>
                                <td class="email-cell">{{ $user->email ?? __('admin_users.email_not_set') }}</td>
                                <td class="role-cell">
                                    <span class="role-badge role-{{ $user->role }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="points-cell">{{ number_format($user->points ?? 0) }}</td>
                                <td class="tokens-cell">{{ number_format($user->tokens ?? 0) }}</td>
                                <td class="status-cell">
                                    @if($user->bandate)
                                        <span class="status-badge status-banned">
                                            <i class="fas fa-ban me-1"></i>
                                            {{ __('admin_users.banned') }}
                                        </span>
                                    @else
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle me-1"></i>
                                            {{ __('admin_users.active') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="date-cell">
                                    {{ isset($user->joindate) && $user->joindate ? \Carbon\Carbon::parse($user->joindate)->format('M j, Y') : __('admin_users.unknown') }}
                                </td>
                                <td class="action-cell">
                                    <a href="{{ route('admin.user.details', $user->account_id) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>
                                        {{ __('admin_users.view') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center no-data">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p>{{ __('admin_users.no_users_found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            @if($users->hasPages())
                {{ $users->appends(request()->query())->links('pagination.admin-pagination') }}
            @endif
        </div>
    </div>
</div>
@endsection