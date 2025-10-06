@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Заголовок -->
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-user me-3"></i>
                {{ __('admin_user_details.title') }}: {{ $user->username }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_user_details.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary me-2">
                <i class="fas fa-tachometer-alt me-2"></i>
                {{ __('admin_user_details.dashboard') }}
            </a>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_user_details.back_to_users') }}
            </a>
        </div>
    </div>

    <!-- Хлебные крошки -->
    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_user_details.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.users') }}">
                    <i class="fas fa-users me-1"></i>
                    {{ __('admin_user_details.users') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-user me-1"></i>
                {{ $user->username }}
            </li>
        </ol>
    </nav>

    <!-- Основной контент в две колонки -->
    <div class="dashboard-content">
        <!-- Левая колонка: Информация о пользователе -->
        <div class="content-column">
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user me-2"></i>
                        {{ __('admin_user_details.user_info') }}
                    </h3>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-primary" onclick="toggleEditForm()">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('admin_user_details.edit') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Форма редактирования (скрыта по умолчанию) -->
                    <form id="editForm" method="POST" action="{{ route('admin.user.update', $user->account_id) }}" style="display: none;">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">{{ __('admin_user_details.username') }}</label>
                                <input type="text" name="username" id="username" class="form-control" 
                                       value="{{ $user->username }}" required>
                            </div>
                            <div class="form-group">
                                <label for="email">{{ __('admin_user_details.email') }}</label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       value="{{ $user->email }}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="role">{{ __('admin_user_details.role') }}</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                                    <option value="moderator" {{ $user->role == 'moderator' ? 'selected' : '' }}>Moderator</option>
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="points">{{ __('admin_user_details.points') }}</label>
                                <input type="number" name="points" id="points" class="form-control" 
                                       value="{{ $user->points }}" min="0" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tokens">{{ __('admin_user_details.tokens') }}</label>
                                <input type="number" name="tokens" id="tokens" class="form-control" 
                                       value="{{ $user->tokens }}" min="0" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('admin_user_details.save') }}
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEditForm()">
                                <i class="fas fa-times me-1"></i>
                                {{ __('admin_user_details.cancel') }}
                            </button>
                        </div>
                    </form>

                    <!-- Информация о пользователе (показывается по умолчанию) -->
                    <div id="userInfo">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>{{ __('admin_user_details.username') }}:</label>
                                <span class="info-value">{{ $user->username }}</span>
                            </div>
                            <div class="info-item">
                                <label>{{ __('admin_user_details.email') }}:</label>
                                <span class="info-value">{{ $user->email ?? __('admin_user_details.not_set') }}</span>
                            </div>
                            <div class="info-item">
                                <label>{{ __('admin_user_details.role') }}:</label>
                                <span class="role-badge role-{{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                            <div class="info-item">
                                <label>{{ __('admin_user_details.points') }}:</label>
                                <span class="info-value">{{ number_format($user->points ?? 0) }}</span>
                            </div>
                            <div class="info-item">
                                <label>{{ __('admin_user_details.tokens') }}:</label>
                                <span class="info-value">{{ number_format($user->tokens ?? 0) }}</span>
                            </div>
                            <div class="info-item">
                                <label>{{ __('admin_user_details.joined') }}:</label>
                                <span class="info-value">
                                    {{ isset($user->joindate) && $user->joindate ? \Carbon\Carbon::parse($user->joindate)->format('M j, Y H:i') : __('admin_user_details.unknown') }}
                                </span>
                            </div>
                            <div class="info-item">
                                <label>{{ __('admin_user_details.last_updated') }}:</label>
                                <span class="info-value">
                                    {{ $user->last_updated ? \Carbon\Carbon::parse($user->last_updated)->format('M j, Y H:i') : __('admin_user_details.never') }}
                                </span>
                            </div>
                            <div class="info-item">
                                <label>{{ __('admin_user_details.status') }}:</label>
                                @if($user->bandate)
                                    <span class="status-badge status-banned">
                                        <i class="fas fa-ban me-1"></i>
                                        {{ __('admin_user_details.banned') }}
                                    </span>
                                @else
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check-circle me-1"></i>
                                        {{ __('admin_user_details.active') }}
                                    </span>
                                @endif
                            </div>
                            <div class="info-item">
                                <label>{{ __('admin_user_details.game_role') }}:</label>
                                <span class="game-role-badge {{ $user->gmlevel == 0 ? 'game-role-player' : ($user->gmlevel == 1 ? 'game-role-moderator' : 'game-role-gm') }}">
                                    <i class="fas fa-{{ $user->gmlevel == 0 ? 'user' : ($user->gmlevel == 1 ? 'user-shield' : 'crown') }} me-1"></i>
                                    @if($user->gmlevel == 0)
                                        {{ __('admin_user_details.player') }}
                                    @elseif($user->gmlevel == 1)
                                        {{ __('admin_user_details.moderator') }} ({{ __('admin_user_details.level') }} {{ $user->gmlevel }})
                                    @else
                                        {{ __('admin_user_details.gm') }} ({{ __('admin_user_details.level') }} {{ $user->gmlevel }})
                                    @endif
                                </span>
                            </div>
                            @if($user->gmcomment)
                                <div class="info-item">
                                    <label>{{ __('admin_user_details.gm_comment') }}:</label>
                                    <span class="info-value">{{ $user->gmcomment }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Персонажи пользователя -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-ninja me-2"></i>
                        {{ __('admin_user_details.characters') }}
                    </h3>
                </div>
                <div class="card-body">
                    @if($characters->count() > 0)
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin_user_details.character_name') }}</th>
                                        <th>{{ __('admin_user_details.character_level') }}</th>
                                        <th>{{ __('admin_user_details.character_class') }}</th>
                                        <th>{{ __('admin_user_details.character_race') }}</th>
                                        <th>{{ __('admin_user_details.character_online') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($characters as $character)
                                        <tr>
                                            <td class="username-cell">
                                                <span class="username">{{ $character->name }}</span>
                                            </td>
                                            <td class="level-cell">{{ $character->level }}</td>
                                            <td class="class-cell">{{ $character->class }}</td>
                                            <td class="race-cell">{{ $character->race }}</td>
                                            <td class="online-cell">
                                                @if($character->online)
                                                    <span class="status-badge status-active">
                                                        <i class="fas fa-circle me-1"></i>
                                                        {{ __('admin_user_details.online') }}
                                                    </span>
                                                @else
                                                    <span class="status-badge status-offline">
                                                        <i class="fas fa-circle me-1"></i>
                                                        {{ __('admin_user_details.offline') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="no-data">{{ __('admin_user_details.no_characters') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Правая колонка: Покупки и баны -->
        <div class="content-column">
            <!-- История покупок -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-bag me-2"></i>
                        {{ __('admin_user_details.purchase_history') }}
                    </h3>
                </div>
                <div class="card-body">
                    @if($purchases->count() > 0)
                        <div class="purchases-list">
                            @foreach($purchases as $purchase)
                                <div class="purchase-item">
                                    <div class="purchase-info">
                                        <div class="purchase-item-header">
                                            <i class="fas fa-shopping-bag text-primary me-2"></i>
                                            <span class="item-name">{{ $purchase->item_name }}</span>
                                        </div>
                                        <div class="purchase-meta">
                                            <div class="purchase-price">
                                                <i class="fas fa-coins text-warning me-1"></i>
                                                <span>{{ number_format($purchase->point_cost + $purchase->token_cost, 0) }}</span>
                                            </div>
                                            <div class="purchase-date">
                                                <i class="fas fa-clock text-muted me-1"></i>
                                                <span>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M j, Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="no-data">{{ __('admin_user_details.no_purchases') }}</p>
                    @endif
                </div>
            </div>

            <!-- История банов -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-ban me-2"></i>
                        {{ __('admin_user_details.ban_history') }}
                    </h3>
                    <div class="card-actions">
                        @if($user->bandate)
                            <form method="POST" action="{{ route('admin.user.unban', $user->account_id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-unlock me-1"></i>
                                    {{ __('admin_user_details.unban') }}
                                </button>
                            </form>
                        @else
                            <button class="btn btn-sm btn-danger" onclick="toggleBanForm()">
                                <i class="fas fa-ban me-1"></i>
                                {{ __('admin_user_details.ban') }}
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Форма бана (скрыта по умолчанию) -->
                    <form id="banForm" method="POST" action="{{ route('admin.user.ban', $user->account_id) }}" style="display: none;">
                        @csrf
                        <div class="form-group">
                            <label for="ban_reason">{{ __('admin_user_details.ban_reason') }}</label>
                            <textarea name="ban_reason" id="ban_reason" class="form-control" 
                                      rows="3" required placeholder="{{ __('admin_user_details.ban_reason_placeholder') }}"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="ban_duration">{{ __('admin_user_details.ban_duration') }}</label>
                            <input type="number" name="ban_duration" id="ban_duration" class="form-control" 
                                   min="1" placeholder="{{ __('admin_user_details.ban_duration_placeholder') }}">
                            <small class="form-text text-muted">{{ __('admin_user_details.ban_duration_help') }}</small>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-ban me-1"></i>
                                {{ __('admin_user_details.ban_user') }}
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleBanForm()">
                                <i class="fas fa-times me-1"></i>
                                {{ __('admin_user_details.cancel') }}
                            </button>
                        </div>
                    </form>

                    @if($banHistory->count() > 0)
                        <div class="bans-list">
                            @foreach($banHistory as $ban)
                                <div class="ban-item">
                                    <div class="ban-info">
                                        <div class="ban-reason">{{ $ban->banreason ?? __('admin_user_details.no_reason') }}</div>
                                        <div class="ban-dates">
                                            <div class="ban-date">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ __('admin_user_details.banned_on') }}: {{ \Carbon\Carbon::createFromTimestamp($ban->bandate)->format('M j, Y H:i') }}
                                            </div>
                                            @if($ban->unbandate)
                                                <div class="unban-date">
                                                    <i class="fas fa-calendar-check me-1"></i>
                                                    {{ __('admin_user_details.unbanned_on') }}: {{ \Carbon\Carbon::createFromTimestamp($ban->unbandate)->format('M j, Y H:i') }}
                                                </div>
                                            @else
                                                <div class="permanent-ban">
                                                    <i class="fas fa-infinity me-1"></i>
                                                    {{ __('admin_user_details.permanent_ban') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="no-data">{{ __('admin_user_details.no_bans') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleEditForm() {
    const editForm = document.getElementById('editForm');
    const userInfo = document.getElementById('userInfo');
    
    if (editForm.style.display === 'none') {
        editForm.style.display = 'block';
        userInfo.style.display = 'none';
    } else {
        editForm.style.display = 'none';
        userInfo.style.display = 'block';
    }
}

function toggleBanForm() {
    const banForm = document.getElementById('banForm');
    
    if (banForm.style.display === 'none') {
        banForm.style.display = 'block';
    } else {
        banForm.style.display = 'none';
    }
}
</script>
@endpush

@push('styles')
<style>
/* Дополнительные стили для страницы деталей пользователя */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
    padding: 10px;
    background-color: #2c3e50;
    border-radius: 5px;
    border: 1px solid #34495e;
}

.info-item label {
    font-size: 12px;
    color: #bdc3c7;
    margin-bottom: 5px;
    text-transform: uppercase;
    font-weight: 500;
}

.info-value {
    color: #ecf0f1;
    font-weight: 500;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 12px;
    color: #bdc3c7;
    margin-bottom: 5px;
    text-transform: uppercase;
    font-weight: 500;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.bans-list {
    max-height: 300px;
    overflow-y: auto;
}

.ban-item {
    padding: 10px;
    margin-bottom: 10px;
    background-color: #2c3e50;
    border-radius: 5px;
    border: 1px solid #34495e;
}

.ban-reason {
    color: #ecf0f1;
    font-weight: 500;
    margin-bottom: 5px;
}

.ban-dates {
    font-size: 12px;
    color: #bdc3c7;
}

.ban-date, .unban-date, .permanent-ban {
    margin-bottom: 3px;
}

.status-offline {
    background-color: #6c757d;
    color: white;
}

@media (max-width: 768px) {
    .info-grid, .form-row {
        grid-template-columns: 1fr;
    }
    
    .dashboard-content {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
