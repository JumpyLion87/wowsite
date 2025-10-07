@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Заголовок админки -->
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-tachometer-alt me-3"></i>
                {{ __('admin_dashboard.title') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_dashboard.welcome_message') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.users') }}" class="btn btn-primary me-2">
                <i class="fas fa-users me-2"></i>
                {{ __('admin_dashboard.manage_users') }}
            </a>
            <a href="{{ route('admin.shop-items') }}" class="btn btn-success me-2">
                <i class="fas fa-store me-2"></i>
                {{ __('admin_dashboard.manage_items') }}
            </a>
            <a href="{{ route('admin.purchases') }}" class="btn btn-warning me-2">
                <i class="fas fa-shopping-cart me-2"></i>
                {{ __('admin_dashboard.manage_purchases') }}
            </a>
            <a href="{{ route('admin.characters') }}" class="btn btn-info me-2">
                <i class="fas fa-users me-2"></i>
                {{ __('admin_dashboard.manage_characters') }}
            </a>
            <a href="{{ route('admin.news.index') }}" class="btn btn-success me-2">
                <i class="fas fa-newspaper me-2"></i>
                {{ __('admin_dashboard.manage_news') }}
            </a>
            <a href="{{ route('admin.news-comments.index') }}" class="btn btn-warning me-2">
                <i class="fas fa-comments me-2"></i>
                {{ __('admin_dashboard.moderate_comments') }}
            </a>
            <a href="{{ route('admin.bans.index') }}" class="btn btn-danger me-2">
                <i class="fas fa-ban me-2"></i>
                {{ __('admin_dashboard.ban_management') }}
            </a>
            <a href="{{ route('admin.settings') }}" class="btn btn-settings me-2">
                <i class="fas fa-cogs me-2"></i>
                {{ __('admin_dashboard.settings') }}
            </a>
            <a href="{{ route('admin.soap') }}" class="btn btn-secondary">
                <i class="fas fa-plug me-2"></i>
                {{ __('admin_dashboard.check_soap') }}
            </a>
        </div>
    </div>

    <!-- Статистические карточки -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $totalUsers }}</h3>
                <p class="stat-label">{{ __('admin_dashboard.total_website_users') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $totalAccounts }}</h3>
                <p class="stat-label">{{ __('admin_dashboard.total_ingame_accounts') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-ninja"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $totalChars }}</h3>
                <p class="stat-label">{{ __('admin_dashboard.total_characters') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $totalBans }}</h3>
                <p class="stat-label">{{ __('admin_dashboard.active_bans') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-circle text-success"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $onlineUsers }}</h3>
                <p class="stat-label">{{ __('admin_dashboard.online_players') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $dailyStats['new_purchases'] }}</h3>
                <p class="stat-label">{{ __('admin_dashboard.today_purchases') }}</p>
            </div>
        </div>
        
        
    </div>

    <!-- Дополнительная информация -->
    <div class="info-cards">
        <!-- Статус сервера -->
        <div class="info-card">
            <h3 class="info-title">
                <i class="fas fa-server me-2"></i>
                {{ __('admin_dashboard.server_status') }}
            </h3>
            <div class="server-status">
                <div class="status-indicator {{ $serverStatus['online'] ? 'online' : 'offline' }}">
                    <span class="status-dot"></span>
                    <i class="fas fa-server me-2"></i>
                    {{ $serverStatus['online'] ? __('admin_dashboard.server_online') : __('admin_dashboard.server_offline') }}
                </div>
                <div class="status-details">
                    <div class="status-item">
                        <i class="fas fa-users text-info me-2"></i>
                        <span class="status-label">{{ __('admin_dashboard.players_online') }}:</span>
                        <span class="status-value">{{ $serverStatus['players'] ?? 0 }}</span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-clock text-warning me-2"></i>
                        <span class="status-label">{{ __('admin_dashboard.uptime') }}:</span>
                        <span class="status-value">{{ $serverStatus['uptime'] ?? 'Unknown' }}</span>
                    </div>
                    @if(isset($serverStatus['total_characters']))
                    <div class="status-item">
                        <i class="fas fa-user-ninja text-info me-2"></i>
                        <span class="status-label">{{ __('admin_dashboard.total_characters') }}:</span>
                        <span class="status-value">{{ $serverStatus['total_characters'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Последние покупки -->
        <div class="info-card">
            <h3 class="info-title">
                <i class="fas fa-shopping-bag me-2"></i>
                {{ __('admin_dashboard.recent_purchases') }}
            </h3>
            <div class="purchases-list">
                @if($recentPurchases->count() > 0)
                    @foreach($recentPurchases->take(5) as $purchase)
                        <div class="purchase-item">
                            <div class="purchase-info">
                                <div class="purchase-item-header">
                                    <i class="fas fa-shopping-bag text-primary me-2"></i>
                                    <span class="item-name">{{ $purchase->item_name }}</span>
                                </div>
                                <div class="purchase-user">
                                    <i class="fas fa-user text-muted me-1"></i>
                                    <span class="purchase-email">{{ $purchase->email }}</span>
                                </div>
                            </div>
                            <div class="purchase-meta">
                                <div class="purchase-price">
                                    <i class="fas fa-coins text-warning me-1"></i>
                                    <span>{{ number_format($purchase->point_cost + $purchase->token_cost, 0) }}</span>
                                </div>
                                <div class="purchase-date">
                                    <i class="fas fa-clock text-muted me-1"></i>
                                    <span>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="no-data">{{ __('admin_dashboard.no_recent_purchases') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Основной контент в две колонки -->
    <div class="dashboard-content">
        <!-- Левая колонка: Персонал -->
        <div class="content-column">
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-tie me-2"></i>
                        {{ __('admin_dashboard.recent_staff_header') }}
                    </h3>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-refresh" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Форма поиска -->
                    <form class="search-form" method="GET" action="{{ route('admin.dashboard') }}">
                        <div class="search-row">
                            <div class="search-group">
                                <input type="text" name="search_username" class="form-control" 
                                       placeholder="{{ __('admin_dashboard.search_username') }}" 
                                       value="{{ $searchUsername }}">
                            </div>
                            <div class="search-group">
                                <input type="text" name="search_email" class="form-control" 
                                       placeholder="{{ __('admin_dashboard.search_email') }}" 
                                       value="{{ $searchEmail }}">
                            </div>
                            <div class="search-group">
                                <select name="role_filter" class="form-select">
                                    <option value="">{{ __('admin_dashboard.all_roles') }}</option>
                                    <option value="admin" {{ $roleFilter == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="moderator" {{ $roleFilter == 'moderator' ? 'selected' : '' }}>Moderator</option>
                                </select>
                            </div>
                            <div class="search-group">
                                <button type="submit" class="btn btn-search">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('admin_dashboard.search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Таблица персонала -->
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('admin_dashboard.table_username') }}</th>
                                    <th>{{ __('admin_dashboard.table_email') }}</th>
                                    <th>{{ __('admin_dashboard.table_points') }}</th>
                                    <th>{{ __('admin_dashboard.table_tokens') }}</th>
                                    <th>{{ __('admin_dashboard.table_role') }}</th>
                                    <th>{{ __('admin_dashboard.table_online') }}</th>
                                    <th>{{ __('admin_dashboard.table_last_updated') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="username-cell">
                                            <div class="user-info">
                                                <span class="username">{{ $user->username }}</span>
                                            </div>
                                        </td>
                                        <td class="email-cell">{{ $user->email ?? __('admin_dashboard.email_not_set') }}</td>
                                        <td class="points-cell">{{ number_format($user->points ?? 0) }}</td>
                                        <td class="tokens-cell">{{ number_format($user->tokens ?? 0) }}</td>
                                        <td class="role-cell">
                                            <span class="role-badge role-{{ $user->role }}">
                                                {{ ucfirst(__($user->role)) }}
                                            </span>
                                        </td>
                                        <td class="online-cell">
                                            <span class="admin-online-status {{ isset($user->online) && $user->online ? 'admin-online' : 'admin-offline' }}">
                                                <span class="admin-status-dot"></span>
                                                {{ isset($user->online) && $user->online ? __('admin_dashboard.online') : __('admin_dashboard.offline') }}
                                            </span>
                                        </td>
                                        <td class="date-cell">
                                            {{ $user->last_updated ? \Carbon\Carbon::parse($user->last_updated)->format('M j, Y H:i') : __('admin_dashboard.never') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center no-data">
                                            <i class="fas fa-users fa-2x mb-2"></i>
                                            <p>{{ __('admin_dashboard.no_staff_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Правая колонка: Бани -->
        <div class="content-column">
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-ban me-2"></i>
                        {{ __('admin_dashboard.recent_bans_header') }}
                    </h3>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-refresh" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('admin_dashboard.table_username') }}</th>
                                    <th>{{ __('admin_dashboard.table_ban_reason') }}</th>
                                    <th>{{ __('admin_dashboard.table_ban_date') }}</th>
                                    <th>{{ __('admin_dashboard.table_unban_date') }}</th>
                                    <th>{{ __('admin_dashboard.table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bans as $ban)
                                    <tr>
                                        <td class="username-cell">
                                            <div class="user-info">
                                                <span class="username">{{ $ban->username }}</span>
                                                <small class="user-id">ID: {{ $ban->id }}</small>
                                            </div>
                                        </td>
                                        <td class="reason-cell">
                                            <span class="ban-reason">{{ $ban->banreason ?? __('admin_dashboard.no_reason_provided') }}</span>
                                        </td>
                                        <td class="date-cell">
                                            {{ $ban->bandate ? \Carbon\Carbon::parse($ban->bandate)->format('M j, Y H:i') : __('admin_dashboard.na') }}
                                        </td>
                                        <td class="date-cell">
                                            {{ $ban->unbandate ? \Carbon\Carbon::parse($ban->unbandate)->format('M j, Y H:i') : __('admin_dashboard.permanent') }}
                                        </td>
                                        <td class="action-cell">
                                            <a href="/admin/users#user-{{ $ban->id }}" class="btn btn-manage">
                                                <i class="fas fa-cog me-1"></i>
                                                {{ __('admin_dashboard.manage_button') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center no-data">
                                            <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                            <p>{{ __('admin_dashboard.no_bans_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
