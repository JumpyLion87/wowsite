@extends('layouts.app')

@section('title', __('admin_bans.ban_management'))

@section('content')
<div class="dashboard-container">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="admin-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ \App\Helpers\DashboardHelper::getDashboardRoute() }}">{{ \App\Helpers\DashboardHelper::getDashboardTitle() }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('admin_bans.ban_management') }}</li>
        </ol>
    </nav>

    <!-- Заголовок -->
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-ban me-3"></i>
                {{ __('admin_bans.ban_management') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_bans.ban_management_description') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ \App\Helpers\DashboardHelper::getDashboardRoute() }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_dashboard.back_to_dashboard') }}
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#banModal">
                <i class="fas fa-plus me-2"></i>
                {{ __('admin_bans.create_ban') }}
            </button>
        </div>
    </div>

    <!-- Статистика -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['total'] }}</h3>
                <p>{{ __('admin_bans.total_bans') }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['active'] }}</h3>
                <p>{{ __('admin_bans.active_bans') }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['expired'] }}</h3>
                <p>{{ __('admin_bans.expired_bans') }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-infinity"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['permanent'] }}</h3>
                <p>{{ __('admin_bans.permanent_bans') }}</p>
            </div>
        </div>
    </div>

    <!-- Фильтры и поиск -->
    <div class="filter-section mb-4">
        <form method="GET" action="{{ route('admin.bans.index') }}" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="status">{{ __('admin_bans.filter_by_status') }}:</label>
                    <select name="status" id="status" class="form-select">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('admin_bans.all_statuses') }}</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>{{ __('admin_bans.active_bans') }}</option>
                        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>{{ __('admin_bans.expired_bans') }}</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>{{ __('admin_bans.inactive_bans') }}</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="search">{{ __('admin_bans.search') }}:</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           value="{{ $search }}" placeholder="{{ __('admin_bans.search_placeholder') }}">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>
                        {{ __('admin_bans.filter') }}
                    </button>
                    <a href="{{ route('admin.bans.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        {{ __('admin_bans.clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Массовые операции -->
    <div class="bulk-actions mb-3" id="bulkActions" style="display: none;">
        <form method="POST" action="{{ route('admin.bans.bulk') }}" id="bulkForm">
            @csrf
            <div class="bulk-actions-content">
                <span class="bulk-selected-count"></span>
                <select name="action" class="form-select me-3" style="width: auto;">
                    <option value="unban">{{ __('admin_bans.bulk_unban') }}</option>
                    <option value="delete">{{ __('admin_bans.bulk_delete') }}</option>
                </select>
                <button type="submit" class="btn btn-warning me-2">
                    <i class="fas fa-check me-1"></i>
                    {{ __('admin_bans.apply_action') }}
                </button>
                <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                    <i class="fas fa-times me-1"></i>
                    {{ __('admin_bans.cancel') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Таблица банов -->
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>{{ __('admin_bans.user') }}</th>
                    <th>{{ __('admin_bans.type') }}</th>
                    <th>{{ __('admin_bans.reason') }}</th>
                    <th>{{ __('admin_bans.banned_by') }}</th>
                    <th>{{ __('admin_bans.ban_date') }}</th>
                    <th>{{ __('admin_bans.unban_date') }}</th>
                    <th>{{ __('admin_bans.status') }}</th>
                    <th>{{ __('admin_bans.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bans as $ban)
                    <tr class="ban-row" data-ban-id="{{ $ban->id }}">
                        <td>
                            <input type="checkbox" class="ban-checkbox" value="{{ $ban->id }}" onchange="updateBulkActions()">
                        </td>
                        <td class="user-cell">
                            <div class="user-info">
                                <div class="username">{{ $ban->username }}</div>
                                <div class="user-details">
                                    <small class="text-muted">ID: {{ $ban->id }}</small>
                                    @if($ban->email)
                                        <br><small class="text-muted">{{ $ban->email }}</small>
                                    @endif
                                    @if($ban->character_name)
                                        <br><small class="text-info">Персонаж: {{ $ban->character_name }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="type-cell">
                            @if($ban->ban_type === 'character')
                                <span class="badge badge-info">{{ __('admin_bans.character_ban') }}</span>
                            @elseif($ban->ban_type === 'ip')
                                <span class="badge badge-danger">{{ __('admin_bans.ip_ban') }}</span>
                            @else
                                <span class="badge badge-warning">{{ __('admin_bans.account_ban') }}</span>
                            @endif
                        </td>
                        <td class="reason-cell">
                            <div class="ban-reason">{{ $ban->banreason ?: __('admin_bans.no_reason') }}</div>
                        </td>
                        <td class="banned-by-cell">
                            <span class="banned-by">{{ $ban->bannedby ?: __('admin_bans.system') }}</span>
                        </td>
                        <td class="date-cell">
                            {{ $ban->bandate ? \Carbon\Carbon::createFromTimestamp($ban->bandate)->format('M j, Y H:i') : __('admin_bans.unknown') }}
                        </td>
                        <td class="date-cell">
                            @if($ban->unbandate)
                                {{ \Carbon\Carbon::createFromTimestamp($ban->unbandate)->format('M j, Y H:i') }}
                            @else
                                <span class="permanent-ban">{{ __('admin_bans.permanent') }}</span>
                            @endif
                        </td>
                        <td class="status-cell">
                            @if($ban->active)
                                @if($ban->unbandate && $ban->unbandate <= time())
                                    <span class="badge badge-warning">{{ __('admin_bans.expired') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('admin_bans.active') }}</span>
                                @endif
                            @else
                                <span class="badge badge-secondary">{{ __('admin_bans.inactive') }}</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            <div class="action-buttons">
                                <a href="{{ route('admin.bans.show', $ban->id) }}" class="btn btn-sm btn-info" title="{{ __('admin_bans.view_details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($ban->active && $ban->ban_type !== 'ip')
                                    <form method="POST" action="{{ route('admin.bans.unban', $ban->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                title="{{ __('admin_bans.unban') }}"
                                                onclick="return confirm('{{ __('admin_bans.unban_confirm') }}')">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.bans.destroy', $ban->id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            title="{{ __('admin_bans.delete') }}"
                                            onclick="return confirm('{{ __('admin_bans.delete_confirm') }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                                <h4>{{ __('admin_bans.no_bans_found') }}</h4>
                                <p>{{ __('admin_bans.no_bans_description') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Пагинация -->
    @if($bans->hasPages())
        <div class="pagination-container">
            {{ $bans->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Модальное окно создания бана -->
<div class="modal fade" id="banModal" tabindex="-1" aria-labelledby="banModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="banModalLabel">
                    <i class="fas fa-ban me-2"></i>
                    {{ __('admin_bans.create_ban') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.bans.store') }}">
                @csrf
                <div class="modal-body">
                    <!-- Поле для выбора аккаунта (по умолчанию) -->
                    <div class="form-group mb-3 position-relative" id="account_field">
                        <label for="account_search" class="form-label">{{ __('admin_bans.select_account') }}</label>
                        <input type="text" id="account_search" class="form-control" 
                               placeholder="{{ __('admin_bans.search_account_placeholder') }}"
                               autocomplete="off">
                        <div id="account_results" class="account-results" style="display: none;"></div>
                        <input type="hidden" name="account_id" id="selected_account_id">
                        <div id="selected_account_info" class="selected-account-info mt-2" style="display: none;"></div>
                    </div>
                    
                    <!-- Поле для ввода IP адреса -->
                    <div class="form-group mb-3" id="ip_field" style="display: none;">
                        <label for="ip_address" class="form-label">{{ __('admin_bans.ip_address') }} <span class="text-danger">*</span></label>
                        <input type="text" name="ip_address" id="ip_address" class="form-control" 
                               placeholder="{{ __('admin_bans.ip_address_placeholder') }}">
                        <small class="form-text text-muted">{{ __('admin_bans.ip_address_help') }}</small>
                    </div>
                    
                    <!-- Поле для выбора персонажа -->
                    <div class="form-group mb-3" id="character_field" style="display: none;">
                        <!-- Сначала выбираем аккаунт -->
                        <div class="form-group mb-3 position-relative">
                            <label for="character_account_search" class="form-label">{{ __('admin_bans.select_account_for_character') }}</label>
                            <input type="text" id="character_account_search" class="form-control" 
                                   placeholder="{{ __('admin_bans.search_account_placeholder') }}"
                                   autocomplete="off">
                            <div id="character_account_results" class="account-results" style="display: none;"></div>
                            <input type="hidden" id="selected_character_account_id">
                            <div id="selected_character_account_info" class="selected-account-info mt-2" style="display: none;"></div>
                        </div>
                        
                        <!-- Затем выбираем персонажа -->
                        <div class="form-group mb-3 position-relative" id="character_select_container" style="display: none;">
                            <label for="character_select" class="form-label">{{ __('admin_bans.select_character') }}</label>
                            <select id="character_select" name="character_name" class="form-select">
                                <option value="">{{ __('admin_bans.select_character_first') }}</option>
                            </select>
                            <div id="selected_character_info" class="selected-account-info mt-2" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="ban_reason" class="form-label">{{ __('admin_bans.ban_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="ban_reason" id="ban_reason" class="form-control" rows="3" 
                                  placeholder="{{ __('admin_bans.ban_reason_placeholder') }}" required></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="ban_duration" class="form-label">{{ __('admin_bans.ban_duration') }}</label>
                        <div class="input-group">
                            <input type="number" name="ban_duration" id="ban_duration" class="form-control" 
                                   min="1" placeholder="{{ __('admin_bans.ban_duration_placeholder') }}">
                            <span class="input-group-text">{{ __('admin_bans.days') }}</span>
                        </div>
                        <small class="form-text text-muted">{{ __('admin_bans.ban_duration_help') }}</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="ban_type" class="form-label">{{ __('admin_bans.ban_type') }}</label>
                        <select name="ban_type" id="ban_type" class="form-select">
                            <option value="account">{{ __('admin_bans.account_ban') }}</option>
                            <option value="ip">{{ __('admin_bans.ip_ban') }}</option>
                            <option value="character">{{ __('admin_bans.character_ban') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('admin_bans.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-2"></i>
                        {{ __('admin_bans.create_ban') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Переключение полей в зависимости от типа бана
document.addEventListener('DOMContentLoaded', function() {
    const banTypeSelect = document.getElementById('ban_type');
    const accountField = document.getElementById('account_field');
    const ipField = document.getElementById('ip_field');
    const characterField = document.getElementById('character_field');
    
    function toggleFields() {
        const banType = banTypeSelect.value;
        
        // Скрываем все поля
        accountField.style.display = 'none';
        ipField.style.display = 'none';
        characterField.style.display = 'none';
        
        // Показываем нужное поле
        switch(banType) {
            case 'account':
                accountField.style.display = 'block';
                break;
            case 'ip':
                ipField.style.display = 'block';
                break;
            case 'character':
                characterField.style.display = 'block';
                break;
        }
    }
    
    if (banTypeSelect) {
        banTypeSelect.addEventListener('change', toggleFields);
        toggleFields(); // Инициализация
    }
});

// Поиск аккаунтов
let searchTimeout;
const accountSearchInput = document.getElementById('account_search');
const accountResults = document.getElementById('account_results');

if (accountSearchInput) {
    accountSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 3) {
            accountResults.innerHTML = '';
            accountResults.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            // Проверяем, что пользователь авторизован
            if (!document.querySelector('meta[name="csrf-token"]')) {
                accountResults.innerHTML = '<div class="no-results">Необходимо войти в систему. <a href="/login">Войти</a></div>';
                accountResults.style.display = 'block';
                return;
            }
            
                       fetch(`{{ route('ajax.search-accounts') }}?search=${encodeURIComponent(query)}&_t=${Date.now()}&_r=${Math.random()}&_force=1`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                },
                credentials: 'same-origin',
                cache: 'no-store'
            })
                .then(response => {
                    console.log('Response status (account search):', response.status);
                    console.log('Response headers (account search):', response.headers);
                    
                    // Проверяем Content-Type
                    const contentType = response.headers.get('content-type');
                    console.log('Content-Type (account search):', contentType);
                    
                    // Если статус 401, обрабатываем как ошибку авторизации
                    if (response.status === 401) {
                        return response.json().then(data => {
                            if (data.message === 'Unauthenticated.') {
                                throw new Error('UNAUTHENTICATED');
                            }
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('Expected JSON but got:', contentType);
                        return response.text().then(text => {
                            console.error('Response text:', text.substring(0, 500));
                            throw new Error(`Expected JSON response but got ${contentType}. Response: ${text.substring(0, 100)}...`);
                        });
                    }
                    
                    return response.json();
                })
                .then(data => {
                    console.log('Search results (account search):', data);
                    accountResults.innerHTML = '';
                    
                    if (data.error) {
                        accountResults.innerHTML = '<div class="no-results">Ошибка: ' + data.error + '</div>';
                        accountResults.style.display = 'block';
                        return;
                    }
                    
                    if (data.message && data.message === 'Unauthenticated.') {
                        accountResults.innerHTML = '<div class="no-results">Необходимо войти в систему. <a href="/login">Войти</a></div>';
                        accountResults.style.display = 'block';
                        return;
                    }
                    
                    // Обработка HTML ответов (страницы с ошибками)
                    if (typeof data === 'string' && data.includes('<!DOCTYPE')) {
                        accountResults.innerHTML = '<div class="no-results">Ошибка сервера. Возможно, сессия истекла. <a href="/login">Войти заново</a></div>';
                        accountResults.style.display = 'block';
                        return;
                    }
                    
                    if (data.accounts && data.accounts.length > 0) {
                        data.accounts.forEach(account => {
                            const div = document.createElement('div');
                            div.className = 'account-result-item';
                            div.innerHTML = `
                                <div class="account-info">
                                    <strong>${account.username}</strong>
                                    <small>ID: ${account.id}</small>
                                    ${account.email ? `<br><small>${account.email}</small>` : ''}
                                </div>
                            `;
                            div.addEventListener('click', () => selectAccount(account));
                            accountResults.appendChild(div);
                        });
                        accountResults.style.display = 'block';
                    } else {
                        accountResults.innerHTML = '<div class="no-results">Аккаунты не найдены</div>';
                        accountResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error (account search):', error);
                    console.error('Error details (account search):', {
                        name: error.name,
                        message: error.message,
                        stack: error.stack
                    });
                    
                    let errorMessage = 'Ошибка поиска: ' + error.message;
                    
                    // Специальная обработка ошибки авторизации
                    if (error.message === 'UNAUTHENTICATED') {
                        errorMessage = 'Необходимо войти в систему. <a href="/login">Войти</a>';
                    }
                    // Если это ошибка парсинга JSON, показываем более детальную информацию
                    else if (error.message.includes('Unexpected token')) {
                        errorMessage = 'Сервер вернул HTML вместо JSON. Возможно, сессия истекла или произошла ошибка на сервере. <a href="/login">Войти заново</a>';
                    }
                    
                    accountResults.innerHTML = '<div class="no-results">' + errorMessage + '</div>';
                    accountResults.style.display = 'block';
                });
        }, 300);
    });
    
    // Скрываем результаты при клике вне поля
    document.addEventListener('click', function(e) {
        if (!accountSearchInput.contains(e.target) && !accountResults.contains(e.target)) {
            accountResults.style.display = 'none';
        }
    });
}

function selectAccount(account) {
    document.getElementById('selected_account_id').value = account.id;
    document.getElementById('account_search').value = account.username;
    document.getElementById('account_results').innerHTML = '';
    document.getElementById('account_results').style.display = 'none';
    
    const info = document.getElementById('selected_account_info');
    info.innerHTML = `
        <div class="alert alert-info">
            <strong>${account.username}</strong> (ID: ${account.id})
            ${account.email ? `<br>Email: ${account.email}` : ''}
        </div>
    `;
    info.style.display = 'block';
}

// Поиск аккаунта для персонажей
let characterAccountSearchTimeout;
const characterAccountSearchInput = document.getElementById('character_account_search');
const characterAccountResults = document.getElementById('character_account_results');

if (characterAccountSearchInput) {
    characterAccountSearchInput.addEventListener('input', function() {
        clearTimeout(characterAccountSearchTimeout);
        const query = this.value.trim();
        
        if (query.length < 3) {
            characterAccountResults.innerHTML = '';
            characterAccountResults.style.display = 'none';
            return;
        }
        
        characterAccountSearchTimeout = setTimeout(() => {
            console.log('Searching for accounts with query:', query);
            
            // Проверяем, что пользователь авторизован
            if (!document.querySelector('meta[name="csrf-token"]')) {
                characterAccountResults.innerHTML = '<div class="no-results">Необходимо войти в систему. <a href="/login">Войти</a></div>';
                characterAccountResults.style.display = 'block';
                return;
            }
            
                       fetch(`{{ route('ajax.search-accounts') }}?search=${encodeURIComponent(query)}&_t=${Date.now()}&_r=${Math.random()}&_force=1`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                },
                credentials: 'same-origin',
                cache: 'no-store'
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    console.log('Response URL:', response.url);
                    
                    // Проверяем Content-Type
                    const contentType = response.headers.get('content-type');
                    console.log('Content-Type:', contentType);
                    
                    // Если статус 401, обрабатываем как ошибку авторизации
                    if (response.status === 401) {
                        return response.json().then(data => {
                            if (data.message === 'Unauthenticated.') {
                                throw new Error('UNAUTHENTICATED');
                            }
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('Expected JSON but got:', contentType);
                        
                        // Попробуем получить текст ответа для диагностики
                        return response.text().then(text => {
                            console.error('Response text:', text.substring(0, 500));
                            throw new Error(`Expected JSON response but got ${contentType}. Response: ${text.substring(0, 100)}...`);
                        });
                    }
                    
                    return response.json();
                })
                .then(data => {
                    console.log('Search results:', data);
                    characterAccountResults.innerHTML = '';
                    
                    if (data.error) {
                        characterAccountResults.innerHTML = '<div class="no-results">Ошибка: ' + data.error + '</div>';
                        characterAccountResults.style.display = 'block';
                        return;
                    }
                    
                    if (data.message && data.message === 'Unauthenticated.') {
                        characterAccountResults.innerHTML = '<div class="no-results">Необходимо войти в систему. <a href="/login">Войти</a></div>';
                        characterAccountResults.style.display = 'block';
                        return;
                    }
                    
                    // Обработка HTML ответов (страницы с ошибками)
                    if (typeof data === 'string' && data.includes('<!DOCTYPE')) {
                        characterAccountResults.innerHTML = '<div class="no-results">Ошибка сервера. Возможно, сессия истекла. <a href="/login">Войти заново</a></div>';
                        characterAccountResults.style.display = 'block';
                        return;
                    }
                    
                    if (data.accounts && data.accounts.length > 0) {
                        data.accounts.forEach(account => {
                            const item = document.createElement('div');
                            item.className = 'account-result-item';
                            item.innerHTML = `
                                <strong>${account.username}</strong> (ID: ${account.id})
                                ${account.email ? `<br><small>${account.email}</small>` : ''}
                            `;
                            item.onclick = () => selectAccountForCharacter(account);
                            characterAccountResults.appendChild(item);
                        });
                        characterAccountResults.style.display = 'block';
                    } else {
                        characterAccountResults.innerHTML = '<div class="no-results">Аккаунты не найдены</div>';
                        characterAccountResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    console.error('Error details:', {
                        name: error.name,
                        message: error.message,
                        stack: error.stack
                    });
                    
                    let errorMessage = 'Ошибка поиска: ' + error.message;
                    
                    // Специальная обработка ошибки авторизации
                    if (error.message === 'UNAUTHENTICATED') {
                        errorMessage = 'Необходимо войти в систему. <a href="/login">Войти</a>';
                    }
                    // Если это ошибка парсинга JSON, показываем более детальную информацию
                    else if (error.message.includes('Unexpected token')) {
                        errorMessage = 'Сервер вернул HTML вместо JSON. Возможно, сессия истекла или произошла ошибка на сервере. <a href="/login">Войти заново</a>';
                    }
                    
                    characterAccountResults.innerHTML = '<div class="no-results">' + errorMessage + '</div>';
                    characterAccountResults.style.display = 'block';
                });
        }, 300);
    });
}

function selectAccountForCharacter(account) {
    document.getElementById('selected_character_account_id').value = account.id;
    document.getElementById('character_account_search').value = account.username;
    document.getElementById('character_account_results').innerHTML = '';
    document.getElementById('character_account_results').style.display = 'none';
    
    const info = document.getElementById('selected_character_account_info');
    info.innerHTML = `
        <div class="alert alert-info">
            <strong>${account.username}</strong> (ID: ${account.id})
            ${account.email ? `<br>Email: ${account.email}` : ''}
        </div>
    `;
    info.style.display = 'block';
    
    // Показываем контейнер выбора персонажа и загружаем персонажей
    document.getElementById('character_select_container').style.display = 'block';
    loadCharactersForAccount(account.id);
}

function loadCharactersForAccount(accountId) {
    // Проверяем, что пользователь авторизован
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const characterSelect = document.getElementById('character_select');
        characterSelect.innerHTML = '<option value="">Необходимо войти в систему</option>';
        return;
    }
    
    fetch(`{{ route('ajax.account-characters') }}?account_id=${accountId}&_t=${Date.now()}&_r=${Math.random()}&_force=1`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        },
        credentials: 'same-origin',
        cache: 'no-store'
    })
        .then(response => {
            console.log('Response status (load characters):', response.status);
            console.log('Response headers (load characters):', response.headers);
            
            // Проверяем Content-Type
            const contentType = response.headers.get('content-type');
            console.log('Content-Type (load characters):', contentType);
            
            // Если статус 401, обрабатываем как ошибку авторизации
            if (response.status === 401) {
                return response.json().then(data => {
                    if (data.message === 'Unauthenticated.') {
                        throw new Error('UNAUTHENTICATED');
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Expected JSON but got:', contentType);
                return response.text().then(text => {
                    console.error('Response text:', text.substring(0, 500));
                    throw new Error(`Expected JSON response but got ${contentType}. Response: ${text.substring(0, 100)}...`);
                });
            }
            
            return response.json();
        })
        .then(data => {
            const characterSelect = document.getElementById('character_select');
            characterSelect.innerHTML = '<option value="">{{ __("admin_bans.select_character_first") }}</option>';
            
            if (data.characters && data.characters.length > 0) {
                data.characters.forEach(character => {
                    const option = document.createElement('option');
                    option.value = character.name;
                    option.textContent = `${character.name} (${character.level} уровень, ${character.race_name} ${character.class_name})`;
                    characterSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'У этого аккаунта нет персонажей';
                characterSelect.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Error loading characters:', error);
            console.error('Error details (load characters):', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            const characterSelect = document.getElementById('character_select');
            
            let errorMessage = 'Ошибка загрузки персонажей: ' + error.message;
            
            // Специальная обработка ошибки авторизации
            if (error.message === 'UNAUTHENTICATED') {
                errorMessage = 'Необходимо войти в систему. <a href="/login">Войти</a>';
            }
            // Если это ошибка парсинга JSON, показываем более детальную информацию
            else if (error.message.includes('Unexpected token')) {
                errorMessage = 'Сервер вернул HTML вместо JSON. Возможно, сессия истекла или произошла ошибка на сервере. <a href="/login">Войти заново</a>';
            }
            
            characterSelect.innerHTML = '<option value="">' + errorMessage + '</option>';
        });
}

// Обработка выбора персонажа
document.addEventListener('change', function(e) {
    if (e.target.id === 'character_select') {
        const selectedCharacter = e.target.value;
        const info = document.getElementById('selected_character_info');
        
        if (selectedCharacter) {
            const option = e.target.selectedOptions[0];
            info.innerHTML = `
                <div class="alert alert-info">
                    <strong>${selectedCharacter}</strong>
                    <br><small>${option.textContent}</small>
                </div>
            `;
            info.style.display = 'block';
        } else {
            info.style.display = 'none';
        }
    }
});

// Массовые операции
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.ban-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.ban-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const count = checkboxes.length;
    
    if (count > 0) {
        bulkActions.style.display = 'block';
        document.querySelector('.bulk-selected-count').textContent = 
            `Выбрано: ${count} ${count === 1 ? 'бан' : 'банов'}`;
        
        // Добавляем скрытые поля для выбранных банов
        const form = document.getElementById('bulkForm');
        const existingInputs = form.querySelectorAll('input[name="ban_ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        checkboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ban_ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
    } else {
        bulkActions.style.display = 'none';
    }
}

function clearSelection() {
    document.querySelectorAll('.ban-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}
</script>
@endpush
@endsection
