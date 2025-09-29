
@extends('layouts.app')

@section('title', __('online_players.online_players_title'))

@section('content')
<div class="online-container">
    <!-- Header Section -->
    <div class="header-section">
        <h1 class="realm-title">{{ $realm_info->name ?? 'Night of Elune' }}</h1>
        <div class="online-count">
            <span class="online-status"></span>
            {{ __('online_players.online_players_count') }}
            <strong>{{ $total_online }}</strong>
        </div>
        <button class="refresh-btn" id="refreshButton">
            <i class="fas fa-sync-alt"></i>
            {{ __('online_players.refresh') }}
        </button>
    </div>
    
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <!-- Class Distribution -->
        <div class="stat-card">
            <h3 class="stat-title">{{ __('online_players.class_distribution') }}</h3>
            @php
                $display_class_stats = $class_stats;
                arsort($display_class_stats);
            @endphp
            @foreach ($display_class_stats as $class_id => $count)
                @php
                    $class_name = \App\Http\Controllers\OnlinePlayersController::getClassNames()[$class_id] ?? 'Unknown';
                    $class_css = \App\Http\Controllers\OnlinePlayersController::getClassCssClasses()[$class_id] ?? '';
                @endphp
                <div class="stat-item">
                    <span class="{{ $class_css }}">{{ $class_name }}</span>
                    <span class="text-warning">{{ $count }}</span>
                </div>
            @endforeach
        </div>
        
        <!-- Race Distribution -->
        <div class="stat-card">
            <h3 class="stat-title">{{ __('online_players.race_distribution') }}</h3>
            @php
                $display_race_stats = $race_stats;
                arsort($display_race_stats);
            @endphp
            @foreach ($display_race_stats as $race_id => $count)
                @php
                    $race_name = \App\Http\Controllers\OnlinePlayersController::getRaceNames()[$race_id] ?? 'Unknown';
                    $race_css = \App\Http\Controllers\OnlinePlayersController::getRaceCssClasses()[$race_id] ?? '';
                @endphp
                <div class="stat-item">
                    <span class="{{ $race_css }}">{{ $race_name }}</span>
                    <span class="text-warning">{{ $count }}</span>
                </div>
            @endforeach
        </div>
        
        <!-- Level Range -->
        <div class="stat-card">
            <h3 class="stat-title">{{ __('online_players.level_range') }}</h3>
            @php
                $level_ranges = [
                    'level_range_1_10' => 0,
                    'level_range_11_20' => 0,
                    'level_range_21_30' => 0,
                    'level_range_31_40' => 0,
                    'level_range_41_50' => 0,
                    'level_range_51_60' => 0,
                    'level_range_61_70' => 0,
                    'level_range_71_80' => 0
                ];
                
                foreach ($online_players as $player) {
                    $level = $player->level;
                    if ($level <= 10) $level_ranges['level_range_1_10']++;
                    elseif ($level <= 20) $level_ranges['level_range_11_20']++;
                    elseif ($level <= 30) $level_ranges['level_range_21_30']++;
                    elseif ($level <= 40) $level_ranges['level_range_31_40']++;
                    elseif ($level <= 50) $level_ranges['level_range_41_50']++;
                    elseif ($level <= 60) $level_ranges['level_range_51_60']++;
                    elseif ($level <= 70) $level_ranges['level_range_61_70']++;
                    else $level_ranges['level_range_71_80']++;
                }
            @endphp
            @foreach ($level_ranges as $range_key => $count)
                @if ($count > 0)
                    <div class="stat-item">
                        <span>{{ __('online_players.' . $range_key) }}</span>
                        <span class="text-warning">{{ $count }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    
    <!-- Players Table -->
    <div class="players-table">
        @if ($total_online > 0)
            <table class="table table-dark table-hover">
                <thead class="table-header">
                    <tr>
                        <th>{{ __('online_players.player_name') }}</th>
                        <th>{{ __('online_players.class') }}</th>
                        <th>{{ __('online_players.race') }}</th>
                        <th>{{ __('online_players.level') }}</th>
                        <th>{{ __('online_players.guild') }}</th>
                        <th>{{ __('online_players.zone') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($online_players as $player)
                        <tr>
                            <td>
                                <a href="/character?guid={{ $player->guid }}" class="player-name-link">
                                    <span class="player-name">{{ $player->name }}</span>
                                </a>
                            </td>
                            <td>
                                @php
                                    $class_id = $player->class;
                                    $class_names = \App\Http\Controllers\OnlinePlayersController::getClassNames();
                                    $class_icons = \App\Http\Controllers\OnlinePlayersController::getClassIcons();
                                    $class_name = $class_names[$class_id] ?? 'Unknown';
                                    $class_image = asset('img/accountimg/class/' . $class_icons[$class_id] . '.webp');
                                @endphp
                                <img src="{{ $class_image }}"
                                     alt="{{ $class_name }}"
                                     class="class-icon"
                                     width="32"
                                     height="32"
                                     loading="lazy"
                                     onerror="this.src='{{ asset('img/accountimg/class/warrior.webp') }}'">
                                <br>
                                <small class="{{ \App\Http\Controllers\OnlinePlayersController::getClassCssClasses()[$class_id] ?? '' }}">{{ $class_name }}</small>
                            </td>
                            <td>
                                @php
                                    $race_id = $player->race;
                                    $race_names = \App\Http\Controllers\OnlinePlayersController::getRaceNames();
                                    $race_icons = \App\Http\Controllers\OnlinePlayersController::getRaceIcons();
                                    $race_name = $race_names[$race_id] ?? 'Unknown';
                                    $gender = $player->gender == 0 ? 'male' : 'female';
                                    $race_image = asset('img/accountimg/race/' . $gender . '/' . $race_icons[$race_id] . '.png');
                                @endphp
                                <img src="{{ $race_image }}"
                                     alt="{{ $race_name }}"
                                     class="class-icon"
                                     width="32"
                                     height="32"
                                     loading="lazy"
                                     onerror="this.src='{{ asset('img/accountimg/race/male/human.png') }}'">
                                <br>
                                <small class="{{ \App\Http\Controllers\OnlinePlayersController::getRaceCssClasses()[$race_id] ?? '' }}">{{ $race_name }}</small>
                            </td>
                            <td>
                                <span class="level-badge">{{ $player->level }}</span>
                            </td>
                            <td>
                                @if ($player->guild && $player->guild->name)
                                    <span class="text-info">{{ $player->guild->name }}</span>
                                @else
                                    <span class="text-muted">{{ __('online_players.no_guild') }}</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ \App\Http\Controllers\OnlinePlayersController::getZoneNames()[$player->map] ?? __('online_players.zone_unknown') }}</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-players">
                <i class="fas fa-users-slash fa-3x mb-3 online-icon-gray"></i>
                <p>{{ __('online_players.no_players_online') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh button functionality
        const refreshButton = document.getElementById('refreshButton');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                this.classList.add('refreshing');
                // Add slight delay to show animation before reload
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            });
        }
    });
</script>
@endsection