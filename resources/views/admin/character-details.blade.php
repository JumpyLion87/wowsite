@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-user me-3"></i>
                {{ __('admin_character_details.title') }}: {{ $character->name }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_character_details.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.characters') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_character_details.back_to_list') }}
            </a>
        </div>
    </div>

    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_character_details.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.characters') }}">
                    <i class="fas fa-users me-1"></i>
                    {{ __('admin_character_details.characters') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-user me-1"></i>
                {{ $character->name }}
            </li>
        </ol>
    </nav>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle me-2"></i>
                {{ __('admin_character_details.character_info') }}
            </h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>{{ __('admin_character_details.name') }}:</label>
                    <span class="info-value">{{ $character->name }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.account') }}:</label>
                    <span class="info-value">{{ $character->account_name }} ({{ $character->email }})</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.race') }}:</label>
                    <span class="info-value">{{ \App\Http\Controllers\AdminController::getRaceName($character->race) }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.class') }}:</label>
                    <span class="info-value">{{ \App\Http\Controllers\AdminController::getClassName($character->class) }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.level') }}:</label>
                    <span class="info-value">{{ $character->level }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.money') }}:</label>
                    <span class="info-value">{{ \App\Http\Controllers\AdminController::formatMoney($character->money) }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.status') }}:</label>
                    <span class="status-badge {{ $character->online ? 'status-online' : 'status-offline' }}">
                        <span class="status-dot"></span>
                        {{ $character->online ? __('admin_character_details.online') : __('admin_character_details.offline') }}
                    </span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.position') }}:</label>
                    <span class="info-value">
                        X: {{ number_format($character->position_x, 2) }}, 
                        Y: {{ number_format($character->position_y, 2) }}, 
                        Z: {{ number_format($character->position_z, 2) }}
                    </span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.map') }}:</label>
                    <span class="info-value">{{ \App\Http\Controllers\AdminController::getMapName($character->map) }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.zone') }}:</label>
                    <span class="info-value">{{ $character->zone }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_character_details.playtime') }}:</label>
                    <span class="info-value">{{ \App\Http\Controllers\AdminController::formatPlaytime($character->totaltime) }}</span>
                </div>
            </div>

            @if($character->online)
                <div class="form-actions" style="margin-top: 1.5rem;">
                    <form method="POST" action="{{ route('admin.character.kick', $character->guid) }}" class="kick-form">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kick_type" class="form-label">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ __('admin_character_details.kick_type') }} *
                                </label>
                                <select id="kick_type" name="kick_type" class="form-select" required>
                                    <option value="soft">{{ __('admin_character_details.kick_soft') }}</option>
                                    <option value="hard">{{ __('admin_character_details.kick_hard') }}</option>
                                    <option value="force">{{ __('admin_character_details.kick_force') }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="reason" class="form-label">
                                    <i class="fas fa-comment me-1"></i>
                                    {{ __('admin_character_details.kick_reason') }}
                                </label>
                                <input type="text" id="reason" name="reason" class="form-control" 
                                       placeholder="{{ __('admin_character_details.kick_reason_placeholder') }}"
                                       value="Admin kick">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-warning" onclick="return confirm('{{ __('admin_character_details.kick_confirm') }}')">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                {{ __('admin_character_details.kick_character') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Телепорт персонажа -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-map-marker-alt me-2"></i>
                {{ __('admin_character_details.teleport') }}
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.character.teleport', $character->guid) }}" class="teleport-form">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="x" class="form-label">
                            <i class="fas fa-crosshairs me-1"></i>
                            {{ __('admin_character_details.x_coordinate') }} *
                        </label>
                        <input type="number" id="x" name="x" class="form-control" 
                               value="{{ $character->position_x }}" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="y" class="form-label">
                            <i class="fas fa-crosshairs me-1"></i>
                            {{ __('admin_character_details.y_coordinate') }} *
                        </label>
                        <input type="number" id="y" name="y" class="form-control" 
                               value="{{ $character->position_y }}" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="z" class="form-label">
                            <i class="fas fa-crosshairs me-1"></i>
                            {{ __('admin_character_details.z_coordinate') }} *
                        </label>
                        <input type="number" id="z" name="z" class="form-control" 
                               value="{{ $character->position_z }}" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="map" class="form-label">
                            <i class="fas fa-map me-1"></i>
                            {{ __('admin_character_details.map_id') }} *
                        </label>
                        <input type="number" id="map" name="map" class="form-control" 
                               value="{{ $character->map }}" required>
                    </div>
                    <div class="form-group">
                        <label for="zone" class="form-label">
                            <i class="fas fa-map-marked-alt me-1"></i>
                            {{ __('admin_character_details.zone_id') }}
                        </label>
                        <input type="number" id="zone" name="zone" class="form-control" 
                               value="{{ $character->zone }}">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-teleport me-2"></i>
                        {{ __('admin_character_details.teleport_character') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

