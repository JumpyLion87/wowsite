@extends('layouts.app')

@section('content')
<div class="admin-settings-container">
    <div class="settings-header">
        <h1 class="settings-title">
            <i class="fas fa-cogs me-2"></i>
            {{ __('admin_settings.title') }}
        </h1>
        <p class="settings-description">{{ __('admin_settings.description') }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="settings-form">
        @csrf
        
        <!-- Навигация по табам -->
        <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                    <i class="fas fa-globe me-2"></i>
                    {{ __('admin_settings.general_tab') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="server-tab" data-bs-toggle="tab" data-bs-target="#server" type="button" role="tab">
                    <i class="fas fa-server me-2"></i>
                    {{ __('admin_settings.server_tab') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shop-tab" data-bs-toggle="tab" data-bs-target="#shop" type="button" role="tab">
                    <i class="fas fa-store me-2"></i>
                    {{ __('admin_settings.shop_tab') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>
                    {{ __('admin_settings.users_tab') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mail-tab" data-bs-toggle="tab" data-bs-target="#mail" type="button" role="tab">
                    <i class="fas fa-envelope me-2"></i>
                    {{ __('admin_settings.mail_tab') }}
                </button>
            </li>
        </ul>

        <!-- Содержимое табов -->
        <div class="tab-content settings-content" id="settingsTabContent">
            <!-- Общие настройки -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-globe me-2"></i>
                            {{ __('admin_settings.general_settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">{{ __('admin_settings.site_name') }}</label>
                                    <input type="text" class="form-control" id="site_name" name="general[site_name]" 
                                           value="{{ $settings['general']['site_name'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="maintenance_mode" class="form-label">{{ __('admin_settings.maintenance_mode') }}</label>
                                    <select class="form-select" id="maintenance_mode" name="general[maintenance_mode]">
                                        <option value="0" {{ !$settings['general']['maintenance_mode'] ? 'selected' : '' }}>
                                            {{ __('admin_settings.disabled') }}
                                        </option>
                                        <option value="1" {{ $settings['general']['maintenance_mode'] ? 'selected' : '' }}>
                                            {{ __('admin_settings.enabled') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="site_description" class="form-label">{{ __('admin_settings.site_description') }}</label>
                            <textarea class="form-control" id="site_description" name="general[site_description]" rows="3">{{ $settings['general']['site_description'] }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="site_keywords" class="form-label">{{ __('admin_settings.site_keywords') }}</label>
                            <input type="text" class="form-control" id="site_keywords" name="general[site_keywords]" 
                                   value="{{ $settings['general']['site_keywords'] }}" 
                                   placeholder="{{ __('admin_settings.keywords_placeholder') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Настройки сервера -->
            <div class="tab-pane fade" id="server" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-server me-2"></i>
                            {{ __('admin_settings.server_settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="server_name" class="form-label">{{ __('admin_settings.server_name') }}</label>
                                    <input type="text" class="form-control" id="server_name" name="server[server_name]" 
                                           value="{{ $settings['server']['server_name'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="server_realm" class="form-label">{{ __('admin_settings.server_realm') }}</label>
                                    <input type="text" class="form-control" id="server_realm" name="server[server_realm]" 
                                           value="{{ $settings['server']['server_realm'] }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="server_type" class="form-label">{{ __('admin_settings.server_type') }}</label>
                                    <select class="form-select" id="server_type" name="server[server_type]" required>
                                        <option value="PvP" {{ $settings['server']['server_type'] == 'PvP' ? 'selected' : '' }}>PvP</option>
                                        <option value="PvE" {{ $settings['server']['server_type'] == 'PvE' ? 'selected' : '' }}>PvE</option>
                                        <option value="RP" {{ $settings['server']['server_type'] == 'RP' ? 'selected' : '' }}>RP</option>
                                        <option value="RP-PvP" {{ $settings['server']['server_type'] == 'RP-PvP' ? 'selected' : '' }}>RP-PvP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="server_version" class="form-label">{{ __('admin_settings.server_version') }}</label>
                                    <input type="text" class="form-control" id="server_version" name="server[server_version]" 
                                           value="{{ $settings['server']['server_version'] }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Настройки магазина -->
            <div class="tab-pane fade" id="shop" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-store me-2"></i>
                            {{ __('admin_settings.shop_settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shop_enabled" class="form-label">{{ __('admin_settings.shop_enabled') }}</label>
                                    <select class="form-select" id="shop_enabled" name="shop[shop_enabled]">
                                        <option value="1" {{ $settings['shop']['shop_enabled'] ? 'selected' : '' }}>
                                            {{ __('admin_settings.enabled') }}
                                        </option>
                                        <option value="0" {{ !$settings['shop']['shop_enabled'] ? 'selected' : '' }}>
                                            {{ __('admin_settings.disabled') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shop_currency_points" class="form-label">{{ __('admin_settings.currency_points') }}</label>
                                    <input type="text" class="form-control" id="shop_currency_points" name="shop[shop_currency_points]" 
                                           value="{{ $settings['shop']['shop_currency_points'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shop_currency_tokens" class="form-label">{{ __('admin_settings.currency_tokens') }}</label>
                                    <input type="text" class="form-control" id="shop_currency_tokens" name="shop[shop_currency_tokens]" 
                                           value="{{ $settings['shop']['shop_currency_tokens'] }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Настройки пользователей -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>
                            {{ __('admin_settings.user_settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('admin_settings.user_settings_info') }}
                        </div>
                        <!-- Здесь можно добавить настройки для пользователей -->
                    </div>
                </div>
            </div>

            <!-- Настройки почты -->
            <div class="tab-pane fade" id="mail" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            {{ __('admin_settings.mail_settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_driver" class="form-label">{{ __('admin_settings.mail_driver') }}</label>
                                    <select class="form-select" id="mail_driver" name="mail[mail_driver]" required>
                                        <option value="smtp" {{ $settings['mail']['mail_driver'] == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                        <option value="mail" {{ $settings['mail']['mail_driver'] == 'mail' ? 'selected' : '' }}>Mail</option>
                                        <option value="sendmail" {{ $settings['mail']['mail_driver'] == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_host" class="form-label">{{ __('admin_settings.mail_host') }}</label>
                                    <input type="text" class="form-control" id="mail_host" name="mail[mail_host]" 
                                           value="{{ $settings['mail']['mail_host'] }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_port" class="form-label">{{ __('admin_settings.mail_port') }}</label>
                                    <input type="number" class="form-control" id="mail_port" name="mail[mail_port]" 
                                           value="{{ $settings['mail']['mail_port'] }}" required min="1" max="65535">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_username" class="form-label">{{ __('admin_settings.mail_username') }}</label>
                                    <input type="text" class="form-control" id="mail_username" name="mail[mail_username]" 
                                           value="{{ $settings['mail']['mail_username'] }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_encryption" class="form-label">{{ __('admin_settings.mail_encryption') }}</label>
                                    <select class="form-select" id="mail_encryption" name="mail[mail_encryption]">
                                        <option value="">None</option>
                                        <option value="tls" {{ $settings['mail']['mail_encryption'] == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ $settings['mail']['mail_encryption'] == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="settings-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>
                {{ __('admin_settings.save_settings') }}
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_settings.back_to_dashboard') }}
            </a>
        </div>
    </form>
</div>

<style>
.admin-settings-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: linear-gradient(135deg, rgba(26, 26, 26, 0.95) 0%, rgba(45, 45, 45, 0.95) 50%, rgba(26, 26, 26, 0.95) 100%);
    border: 2px solid #8b4513;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
    color: #fff;
    font-family: 'Cinzel', sans-serif;
}

.settings-header {
    margin-bottom: 30px;
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, rgba(139, 69, 19, 0.8) 0%, rgba(160, 82, 45, 0.8) 100%);
    border: 2px solid #8b4513;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.settings-title {
    color: #ffd700;
    font-size: 2.5rem;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.settings-description {
    color: #ccc;
    font-size: 1.1rem;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.settings-tabs {
    border-bottom: 2px solid #8b4513;
    margin-bottom: 20px;
    background: rgba(26, 26, 26, 0.8);
    border-radius: 8px 8px 0 0;
}

.settings-tabs .nav-link {
    color: #ccc;
    border: none;
    border-bottom: 3px solid transparent;
    background: transparent;
    padding: 15px 20px;
    transition: all 0.3s ease;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.settings-tabs .nav-link:hover {
    color: #ffd700;
    border-bottom-color: #ffd700;
    background: rgba(255, 215, 0, 0.1);
}

.settings-tabs .nav-link.active {
    color: #ffd700;
    border-bottom-color: #ffd700;
    background: rgba(255, 215, 0, 0.15);
}

.settings-content {
    background: rgba(26, 26, 26, 0.8);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #8b4513;
}

.settings-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding: 20px 0;
}

.card {
    background: linear-gradient(135deg, rgba(26, 26, 26, 0.9) 0%, rgba(45, 45, 45, 0.9) 100%);
    border: 2px solid #8b4513;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 6px 20px rgba(255, 215, 0, 0.2);
    transform: translateY(-2px);
}

.card-header {
    background: linear-gradient(135deg, rgba(139, 69, 19, 0.8) 0%, rgba(160, 82, 45, 0.8) 100%);
    border-bottom: 2px solid #8b4513;
    color: #ffd700;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.card-body {
    background: rgba(26, 26, 26, 0.7);
}

.form-label {
    color: #ccc;
    font-weight: 500;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.form-control, .form-select {
    background: rgba(26, 26, 26, 0.9);
    border: 2px solid #8b4513;
    color: #fff;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    background: rgba(26, 26, 26, 0.9);
    border-color: #ffd700;
    color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

.alert {
    border-radius: 8px;
    margin-bottom: 20px;
    border: 2px solid;
}

.alert-success {
    border-color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.alert-danger {
    border-color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    border: 2px solid #8b4513;
    color: #8b4513;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #ffed4e 0%, #ffd700 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
    border: 2px solid #495057;
    color: #fff;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #868e96 0%, #6c757d 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}
</style>
@endsection
