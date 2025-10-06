@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-users me-3"></i>
                {{ __('admin_characters.title') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_characters.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary me-2">
                <i class="fas fa-tachometer-alt me-2"></i>
                {{ __('admin_characters.dashboard') }}
            </a>
        </div>
    </div>

    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_characters.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-users me-1"></i>
                {{ __('admin_characters.title') }}
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
                <h3 class="stat-number">{{ $stats['total_characters'] }}</h3>
                <p class="stat-label">{{ __('admin_characters.total_characters') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-circle text-success"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['online_characters'] }}</h3>
                <p class="stat-label">{{ __('admin_characters.online_characters') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-level-up-alt"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['max_level'] }}</h3>
                <p class="stat-label">{{ __('admin_characters.max_level') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ \App\Http\Controllers\AdminController::formatMoney($stats['total_money']) }}</h3>
                <p class="stat-label">{{ __('admin_characters.total_money') }}</p>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list me-2"></i>
                {{ __('admin_characters.list') }}
            </h3>
        </div>
        <div class="card-body">
            <form class="search-form" method="GET" action="{{ route('admin.characters') }}">
                <div class="search-row">
                    <div class="search-group">
                        <input type="text" name="search" class="form-control"
                               placeholder="{{ __('admin_characters.search_placeholder') }}"
                               value="{{ $search }}">
                    </div>
                    <div class="search-group">
                        <select name="level_filter" class="form-select">
                            <option value="">{{ __('admin_characters.all_levels') }}</option>
                            <option value="80" {{ $levelFilter == '80' ? 'selected' : '' }}>80+</option>
                            <option value="70" {{ $levelFilter == '70' ? 'selected' : '' }}>70+</option>
                            <option value="60" {{ $levelFilter == '60' ? 'selected' : '' }}>60+</option>
                            <option value="40" {{ $levelFilter == '40' ? 'selected' : '' }}>40+</option>
                            <option value="20" {{ $levelFilter == '20' ? 'selected' : '' }}>20+</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <select name="class_filter" class="form-select">
                            <option value="">{{ __('admin_characters.all_classes') }}</option>
                            <option value="1" {{ $classFilter == '1' ? 'selected' : '' }}>Warrior</option>
                            <option value="2" {{ $classFilter == '2' ? 'selected' : '' }}>Paladin</option>
                            <option value="3" {{ $classFilter == '3' ? 'selected' : '' }}>Hunter</option>
                            <option value="4" {{ $classFilter == '4' ? 'selected' : '' }}>Rogue</option>
                            <option value="5" {{ $classFilter == '5' ? 'selected' : '' }}>Priest</option>
                            <option value="6" {{ $classFilter == '6' ? 'selected' : '' }}>Death Knight</option>
                            <option value="7" {{ $classFilter == '7' ? 'selected' : '' }}>Shaman</option>
                            <option value="8" {{ $classFilter == '8' ? 'selected' : '' }}>Mage</option>
                            <option value="9" {{ $classFilter == '9' ? 'selected' : '' }}>Warlock</option>
                            <option value="11" {{ $classFilter == '11' ? 'selected' : '' }}>Druid</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <select name="online_filter" class="form-select">
                            <option value="">{{ __('admin_characters.all_status') }}</option>
                            <option value="online" {{ $onlineFilter == 'online' ? 'selected' : '' }}>{{ __('admin_characters.online') }}</option>
                            <option value="offline" {{ $onlineFilter == 'offline' ? 'selected' : '' }}>{{ __('admin_characters.offline') }}</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <select name="per_page" class="form-select">
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search me-1"></i>
                            {{ __('admin_characters.search') }}
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('admin_characters.guid') }}</th>
                            <th>{{ __('admin_characters.name') }}</th>
                            <th>{{ __('admin_characters.account') }}</th>
                            <th>{{ __('admin_characters.race') }}</th>
                            <th>{{ __('admin_characters.class') }}</th>
                            <th>{{ __('admin_characters.level') }}</th>
                            <th>{{ __('admin_characters.money') }}</th>
                            <th>{{ __('admin_characters.status') }}</th>
                            <th>{{ __('admin_characters.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($characters as $char)
                            <tr>
                                <td class="id-cell">{{ $char->guid }}</td>
                                <td class="name-cell">
                                    <div class="character-info">
                                        <span class="character-name">{{ $char->name }}</span>
                                    </div>
                                </td>
                                <td class="account-cell">{{ $char->account_name }}</td>
                                <td class="race-cell">{{ \App\Http\Controllers\AdminController::getRaceName($char->race) }}</td>
                                <td class="class-cell">{{ \App\Http\Controllers\AdminController::getClassName($char->class) }}</td>
                                <td class="level-cell">{{ $char->level }}</td>
                                <td class="money-cell">{{ \App\Http\Controllers\AdminController::formatMoney($char->money) }}</td>
                                <td class="status-cell">
                                    <span class="status-badge {{ $char->online ? 'status-online' : 'status-offline' }}">
                                        <span class="status-dot"></span>
                                        {{ $char->online ? __('admin_characters.online') : __('admin_characters.offline') }}
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.character.details', $char->guid) }}" class="btn btn-sm btn-primary" title="{{ __('admin_characters.details') }}">
                                            <i class="fas fa-eye"></i>
                                            <span class="btn-text">{{ __('admin_characters.details') }}</span>
                                        </a>
                                        @if($char->online)
                                            <form method="POST" action="{{ route('admin.character.kick', $char->guid) }}" style="display:inline" onsubmit="return confirm('{{ __('admin_characters.kick_confirm') }}')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" title="{{ __('admin_characters.kick') }}">
                                                    <i class="fas fa-sign-out-alt"></i>
                                                    <span class="btn-text">{{ __('admin_characters.kick') }}</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center no-data">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p>{{ __('admin_characters.no_characters') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($characters->hasPages())
                {{ $characters->appends(request()->query())->links('pagination.admin-pagination') }}
            @endif
        </div>
    </div>
</div>
@endsection

