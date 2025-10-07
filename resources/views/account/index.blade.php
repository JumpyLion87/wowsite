@extends('layouts.app')

@section('content')
<div class="container account-container">
    <section class="account-hero">
        <h1 class="account-title">{{ __('account.dashboard_title') }}</h1>
        <p class="account-subtitle">{{ $accountInfo['username'] }} — ID: {{ $accountInfo['id'] }}</p>
    </section>
    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @if(session('vote_success'))
    <div class="alert alert-success">{{ session('vote_success') }}</div>
    @endif
    
    <ul class="nav nav-tabs account-tabs mb-4 justify-content-center" role="tablist" id="accountTabs">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">{{ __('account.overview') }}</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#characters" type="button" role="tab">{{ __('account.characters') }}</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">{{ __('account.security') }}</button>
        </li>
    </ul>

    <div class="tab-content" id="accountTabContent">
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4 align-items-stretch overview-grid">
                <div class="col-12 col-lg-8 d-flex flex-column gap-4">
                    <!-- Основная информация -->
                    <div class="card account-card h-100 d-flex flex-column">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('account.basicinfo') }}</h3>
                            <img src="{{ asset('img/accountimg/profile_pics/' . ($currencies['avatar'] ?? 'user.jpg')) }}" alt="avatar" class="account-profile-pic mb-3">
                            <div class="row text-start justify-content-center">
                                <div class="col-12 col-lg-6">
                                    <p><strong><i class="fas fa-user text-warning"></i> {{ __('account.username') }}:</strong> {{ $accountInfo['username'] }}</p>
                                    <p><strong><i class="fas fa-id-card text-info"></i> {{ __('account.accountid') }}:</strong> {{ $accountInfo['id'] }}</p>
                                    <p><strong><i class="fas fa-calendar-plus text-success"></i> {{ __('account.joindate') }}:</strong> {{ $accountInfo['joindate'] }}</p>
                                    <p><strong><i class="fas fa-sign-in-alt text-primary"></i> {{ __('account.lastlogin') }}:</strong> 
                                        @if($accountInfo['last_login'] && $accountInfo['last_login'] !== 'Never')
                                            {{ $accountInfo['last_login'] }}
                                            <br><small class="text-muted">({{ \Carbon\Carbon::parse($accountInfo['last_login'])->diffForHumans() }})</small>
                                        @else
                                            {{ __('Never') }}
                                        @endif
                                    </p>
                                    <p><strong><i class="fas fa-shield-alt text-danger"></i> {{ __('account.status') }}:</strong>
                                        @if($banInfo)
                                            <span class="text-danger">{{ __('account.banned') }}</span>
                                            <br><small>({{ $banInfo->banreason ?? 'No reason' }},
                                            {{ $banInfo->unbandate ? date('Y-m-d H:i:s', $banInfo->unbandate) : __('account.permanent') }})</small>
                                        @else
                                            <span class="text-success">{{ __('account.active') }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <p><strong><i class="fas fa-envelope text-warning"></i> {{ __('account.email') }}:</strong> {{ $accountInfo['email'] ?? __('Not set') }}</p>
                                    <p><strong><i class="fas fa-gamepad text-info"></i> {{ __('account.expansion') }}:</strong> {{ __('expansions.' . $accountInfo['expansion']) }}</p>
                                    <p><strong><i class="fas fa-users text-success"></i> {{ __('account.totalcharacters') }}:</strong> {{ $totalCharacters }}</p>
                                    <p><strong><i class="fas fa-circle {{ $characters->where('online', 1)->count() > 0 ? 'text-success' : 'text-secondary' }}"></i> {{ __('account.online_now') }}:</strong> 
                                        {{ $characters->where('online', 1)->count() }} / {{ $totalCharacters }}
                                    </p>
                                    @php
                                        $totalGold = $characters->sum('money') / 10000;
                                    @endphp
                                    <p><strong><i class="fas fa-coins text-warning"></i> {{ __('account.total_gold') }}:</strong> 
                                        <span class="account-gold">{{ number_format($totalGold, 0) }}g</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика аккаунта -->
                    <div class="card account-card">
                        <div class="card-body">
                            <h3 class="h4 text-warning mb-4">
                                <i class="fas fa-chart-line"></i> {{ __('account.statistics') }}
                            </h3>
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="stat-box text-center p-3">
                                        <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                        <div class="stat-value">
                                            @php
                                                $days = floor($totalPlaytime / 86400);
                                                $hours = floor(($totalPlaytime % 86400) / 3600);
                                            @endphp
                                            <strong>{{ $days }}</strong>{{ __('account.days') }} 
                                            <strong>{{ $hours }}</strong>{{ __('account.hours') }}
                                        </div>
                                        <div class="stat-label text-muted small">{{ __('account.total_playtime') }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-box text-center p-3">
                                        <i class="fas fa-skull-crossbones fa-2x text-danger mb-2"></i>
                                        <div class="stat-value">
                                            <strong>{{ number_format($totalKills) }}</strong>
                                        </div>
                                        <div class="stat-label text-muted small">{{ __('account.total_kills') }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-box text-center p-3">
                                        <i class="fas fa-level-up-alt fa-2x text-success mb-2"></i>
                                        <div class="stat-value">
                                            <strong>{{ $avgLevel }}</strong>
                                        </div>
                                        <div class="stat-label text-muted small">{{ __('account.avg_level') }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-box text-center p-3">
                                        <i class="fas fa-users fa-2x text-warning mb-2"></i>
                                        <div class="stat-value">
                                            <strong>{{ $totalCharacters }}</strong>
                                        </div>
                                        <div class="stat-label text-muted small">{{ __('account.totalcharacters') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Топ персонажи -->
                    @if($topCharacterByLevel)
                    <div class="card account-card">
                        <div class="card-body">
                            <h3 class="h4 text-warning mb-4">
                                <i class="fas fa-trophy"></i> {{ __('account.top_characters') }}
                            </h3>
                            <div class="row g-3">
                                @if($topCharacterByLevel)
                                <div class="col-12 col-md-6">
                                    <div class="top-char-box p-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-crown fa-2x text-warning me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ __('account.highest_level_char') }}</div>
                                                <div class="text-warning">{{ $topCharacterByLevel->name }}</div>
                                                <div class="small">
                                                    <span class="{{ \App\Helpers\WowHelper::getClassColor($topCharacterByLevel->class) }}">
                                                        {{ \App\Helpers\WowHelper::getClassName($topCharacterByLevel->class) }}
                                                    </span>
                                                    - {{ __('account.level') }} {{ $topCharacterByLevel->level }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                @if($topCharacterByGold)
                                <div class="col-12 col-md-6">
                                    <div class="top-char-box p-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-coins fa-2x text-warning me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ __('account.richest_char') }}</div>
                                                <div class="text-warning">{{ $topCharacterByGold->name }}</div>
                                                <div class="small">
                                                    <span class="{{ \App\Helpers\WowHelper::getClassColor($topCharacterByGold->class) }}">
                                                        {{ \App\Helpers\WowHelper::getClassName($topCharacterByGold->class) }}
                                                    </span>
                                                    - {{ number_format($topCharacterByGold->money / 10000, 2) }}g
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                @if($topCharacterByPlaytime)
                                <div class="col-12 col-md-6">
                                    <div class="top-char-box p-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-hourglass-half fa-2x text-primary me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ __('account.most_active_char') }}</div>
                                                <div class="text-warning">{{ $topCharacterByPlaytime->name }}</div>
                                                <div class="small">
                                                    <span class="{{ \App\Helpers\WowHelper::getClassColor($topCharacterByPlaytime->class) }}">
                                                        {{ \App\Helpers\WowHelper::getClassName($topCharacterByPlaytime->class) }}
                                                    </span>
                                                    - {{ floor($topCharacterByPlaytime->totaltime / 3600) }}{{ __('account.hours') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                @if($topCharacterByKills)
                                <div class="col-12 col-md-6">
                                    <div class="top-char-box p-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-skull fa-2x text-danger me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ __('account.most_kills_char') }}</div>
                                                <div class="text-warning">{{ $topCharacterByKills->name }}</div>
                                                <div class="small">
                                                    <span class="{{ \App\Helpers\WowHelper::getClassColor($topCharacterByKills->class) }}">
                                                        {{ \App\Helpers\WowHelper::getClassName($topCharacterByKills->class) }}
                                                    </span>
                                                    - {{ number_format($topCharacterByKills->totalKills) }} {{ __('account.total_kills') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($activityLog && $activityLog->count())
                    <div class="card account-card flex-grow-1 d-flex flex-column">
                        <div class="card-body">
                            <h3 class="h4 text-warning mb-3">
                                <i class="fas fa-history"></i> {{ __('account.recent_activity') }}
                            </h3>
                            <div class="activity-timeline">
                                @foreach($activityLog as $log)
                                @php
                                    $actionConfig = [
                                        'teleport' => ['icon' => 'fa-map-marker-alt', 'color' => 'primary', 'bg' => 'rgba(0, 112, 222, 0.1)'],
                                        'vote_redirect' => ['icon' => 'fa-vote-yea', 'color' => 'info', 'bg' => 'rgba(105, 204, 240, 0.1)'],
                                        'vote_rewarded' => ['icon' => 'fa-gift', 'color' => 'success', 'bg' => 'rgba(171, 212, 115, 0.1)'],
                                        'login' => ['icon' => 'fa-sign-in-alt', 'color' => 'warning', 'bg' => 'rgba(255, 215, 0, 0.1)'],
                                        'logout' => ['icon' => 'fa-sign-out-alt', 'color' => 'secondary', 'bg' => 'rgba(160, 160, 160, 0.1)'],
                                        'purchase' => ['icon' => 'fa-shopping-cart', 'color' => 'danger', 'bg' => 'rgba(196, 31, 59, 0.1)'],
                                    ];
                                    
                                    $config = $actionConfig[$log->action] ?? ['icon' => 'fa-circle', 'color' => 'light', 'bg' => 'rgba(255, 255, 255, 0.05)'];
                                    $timeAgo = \Carbon\Carbon::createFromTimestamp($log->timestamp)->diffForHumans();
                                @endphp
                                <div class="activity-item" style="background: {{ $config['bg'] }};">
                                    <div class="activity-icon text-{{ $config['color'] }}">
                                        <i class="fas {{ $config['icon'] }}"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-header">
                                            <span class="activity-action text-{{ $config['color'] }}">
                                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                            </span>
                                            @if($log->character_name)
                                                <span class="activity-character">
                                                    <i class="fas fa-user"></i> {{ $log->character_name }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($log->details)
                                        <div class="activity-details">
                                            {{ $log->details }}
                                        </div>
                                        @endif
                                        <div class="activity-time">
                                            <i class="far fa-clock"></i> {{ $timeAgo }}
                                            <small class="text-muted ms-2">({{ date('d.m.Y H:i', $log->timestamp) }})</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-12 col-lg-4 d-flex flex-column gap-4">
                    <div class="card account-card d-flex flex-column">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('account.contact') }}</h3>
                            <p><strong>{{ __('account.email') }}:</strong> {{ $accountInfo['email'] ?? __('Not set') }}</p>
                            <p>
                                <strong class="text-warning">{{ __('account.expansion') }}:</strong> 
                                {{ __('expansions.' . $accountInfo['expansion']) }}
                            </p>
                            @if(auth()->user()->isAdministrator())
                                <a href="{{ \App\Helpers\DashboardHelper::getDashboardRoute() }}" class="btn btn-account mt-2">
                                    <i class="{{ \App\Helpers\DashboardHelper::getDashboardIcon() }} me-2"></i>
                                    {{ \App\Helpers\DashboardHelper::getDashboardTitle() }}
                                </a>
                            @elseif(auth()->user()->isModerator())
                                <a href="{{ \App\Helpers\DashboardHelper::getDashboardRoute() }}" class="btn btn-account mt-2">
                                    <i class="{{ \App\Helpers\DashboardHelper::getDashboardIcon() }} me-2"></i>
                                    {{ \App\Helpers\DashboardHelper::getDashboardTitle() }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Достижения и Прогресс -->
                    <div class="card account-card d-flex flex-column">
                        <div class="card-body">
                            <h3 class="card-title text-center mb-3">
                                <i class="fas fa-trophy"></i> {{ __('account.achievements') }}
                            </h3>
                            <div class="achievement-list">
                                <!-- Достижение: Максимальный уровень -->
                                @if($characters->where('level', 80)->count() > 0)
                                <div class="achievement-item unlocked">
                                    <div class="achievement-icon">
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <div class="achievement-info">
                                        <div class="achievement-name">{{ __('account.achievement_max_level') }}</div>
                                        <div class="achievement-desc">{{ __('account.achievement_max_level_desc') }}</div>
                                    </div>
                                </div>
                                @endif

                                <!-- Достижение: Богач -->
                                @php
                                    $totalGold = $characters->sum('money') / 10000;
                                @endphp
                                @if($totalGold >= 10000)
                                <div class="achievement-item unlocked">
                                    <div class="achievement-icon">
                                        <i class="fas fa-coins text-warning"></i>
                                    </div>
                                    <div class="achievement-info">
                                        <div class="achievement-name">{{ __('account.achievement_rich') }}</div>
                                        <div class="achievement-desc">{{ __('account.achievement_rich_desc') }}</div>
                                    </div>
                                </div>
                                @endif

                                <!-- Достижение: Коллекционер -->
                                @if($totalCharacters >= 5)
                                <div class="achievement-item unlocked">
                                    <div class="achievement-icon">
                                        <i class="fas fa-users text-info"></i>
                                    </div>
                                    <div class="achievement-info">
                                        <div class="achievement-name">{{ __('account.achievement_collector') }}</div>
                                        <div class="achievement-desc">{{ __('account.achievement_collector_desc') }}</div>
                                    </div>
                                </div>
                                @endif

                                <!-- Достижение: Убийца -->
                                @if($totalKills >= 1000)
                                <div class="achievement-item unlocked">
                                    <div class="achievement-icon">
                                        <i class="fas fa-skull text-danger"></i>
                                    </div>
                                    <div class="achievement-info">
                                        <div class="achievement-name">{{ __('account.achievement_killer') }}</div>
                                        <div class="achievement-desc">{{ __('account.achievement_killer_desc') }}</div>
                                    </div>
                                </div>
                                @endif

                                <!-- Достижение: Ветеран -->
                                @php
                                    $accountAge = \Carbon\Carbon::parse($accountInfo['joindate'])->diffInDays(now());
                                @endphp
                                @if($accountAge >= 365)
                                <div class="achievement-item unlocked">
                                    <div class="achievement-icon">
                                        <i class="fas fa-shield-alt text-success"></i>
                                    </div>
                                    <div class="achievement-info">
                                        <div class="achievement-name">{{ __('account.achievement_veteran') }}</div>
                                        <div class="achievement-desc">{{ __('account.achievement_veteran_desc') }}</div>
                                    </div>
                                </div>
                                @endif

                                <!-- Если нет достижений -->
                                @if($characters->where('level', 80)->count() == 0 && $totalGold < 10000 && $totalCharacters < 5 && $totalKills < 1000 && $accountAge < 365)
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle"></i>
                                    <p class="mb-0">{{ __('account.no_achievements') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Голосование -->
                    <div class="card account-card d-flex flex-column">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('vote.title') }}</h3>
                            <p>{{ __('vote.description', ['hours' => env('VOTE_COOLDOWN_HOURS', 24), 'points' => env('VOTE_REWARD_POINTS', 100)]) }}</p>
                            
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i> {{ __('vote.description', ['hours' => env('VOTE_COOLDOWN_HOURS', 24), 'points' => env('VOTE_REWARD_POINTS', 100)]) }}
                            </div>
                            <a href="{{ route('vote.redirect') }}" class="btn btn-primary" target="_blank">
                                <i class="fas fa-vote-yea"></i> {{ __('vote.vote_button') }}
                            </a>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> {{ __('vote.auto_check_info') }}
                                </small>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="characters" role="tabpanel" aria-labelledby="characters-tab">
            <h2 class="h3 text-warning mb-4">{{ __('account.yourcharacters') }}</h2>
            @if ($characters->count())
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach ($characters as $char)
                <div class="col">
                    <div class="card account-card h-100 d-flex flex-column">
                        <div class="card-body text-center">
                            <!-- Имя персонажа с иконками фракции и расы -->
                            <div class="d-flex justify-content-center align-items-center gap-2 mb-3 flex-wrap">
                                <span>{!! \App\Helpers\WowHelper::getFactionIcon($char->race) !!}</span>
                                <span>{!! \App\Helpers\WowHelper::getRaceIcon($char->race, $char->gender) !!}</span>
                                <span class="fw-bold text-warning">{{ $char->name }}</span>
                            </div>
                            
                            <!-- Класс с иконкой и цветом -->
                            <p class="mb-2">
                                {!! \App\Helpers\WowHelper::getClassIcon($char->class) !!}
                                <span class="{{ \App\Helpers\WowHelper::getClassColor($char->class) }}">
                                    {{ \App\Helpers\WowHelper::getClassName($char->class) }}
                                </span>
                                <span class="text-muted">{{ __('account.level') }} {{ $char->level }}</span>
                            </p>
                            
                            <!-- Гильдия -->
                            <p class="mb-2">
                                <i class="fas fa-shield-alt text-info"></i>
                                {{ __('account.guild') }}: 
                                @if($char->guild_name)
                                    <span class="text-warning">{{ $char->guild_name }}</span>
                                @else
                                    <span class="text-muted">{{ __('account.noneguild') }}</span>
                                @endif
                            </p>
                            
                            <!-- Золото -->
                            <p class="mb-2">
                                <i class="fas fa-coins text-warning"></i>
                                {{ __('account.gold') }}: <span class="account-gold">{{ number_format($char->money / 10000, 2) }}g</span>
                            </p>
                            
                            <!-- Статус -->
                            <p class="mb-3">
                                <i class="fas fa-circle {{ $char->online ? 'text-success' : 'text-secondary' }}"></i>
                                {{ __('account.status') }}: 
                                @if($char->online)
                                    <span class="text-success">{{ __('account.online') }}</span>
                                @else
                                    <span class="text-muted">{{ __('account.offline') }}</span>
                                @endif
                            </p>
                            
                            @php
                                $cooldownTs = $teleportCooldowns[$char->guid] ?? 0;
                                $remaining = max(0, ($cooldownTs + 900) - time());
                                $minutes = (int) ceil($remaining / 60);
                            @endphp
                            
                            <!-- Форма телепорта -->
                            <form method="post" class="mt-auto" action="{{ route('account.teleport') }}">
                                @csrf
                                <input type="hidden" name="guid" value="{{ $char->guid }}">
                                <div class="mb-2">
                                    <select class="form-select form-select-sm" name="destination" required>
                                        <option value="">{{ __('account.selectcity') }}</option>
                                        <option value="shattrath">{{ __('account.shattrath') }}</option>
                                        <option value="dalaran">{{ __('account.dalaran') }}</option>
                                    </select>
                                </div>
                                <button class="btn btn-account btn-sm w-100" type="submit" {{ $remaining > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-map-marker-alt"></i> {{ __('account.teleport') }}
                                </button>
                            </form>
                            
                            @if($remaining > 0)
                                <div class="teleport-cooldown mt-2">
                                    <small class="text-warning">
                                        <i class="far fa-clock"></i> {{ __('account.cooldown') }}: {{ $minutes }} {{ __('account.minutes') }}
                                    </small>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: {{ ($remaining / 900) * 100 }}%" 
                                             aria-valuenow="{{ $remaining }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="900">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <p class="text-center">{{ __('account.nocharacteryet') }}</p>
            @endif
        </div>

        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
    @php
        $securityScore = 0;
        if(!empty($accountInfo['email'])) $securityScore += 25;
        if($accountInfo['last_login'] !== 'Never') $securityScore += 25;
        $passwordAge = \Carbon\Carbon::parse($lastPasswordChange)->diffInDays(now());
        if($passwordAge < 90) $securityScore += 25;
        if($activeSessions->count() <= 2) $securityScore += 25;
        
        // Проверяем, была ли когда-либо смена пароля
        $hasChangedPassword = isset($userCurrency) && $userCurrency->last_password_change !== null;
        
        $securityLevel = $securityScore >= 75 ? 'strong' : ($securityScore >= 50 ? 'medium' : 'weak');
        $securityColor = $securityScore >= 75 ? 'success' : ($securityScore >= 50 ? 'warning' : 'danger');
        $securityIcon = $securityScore >= 75 ? 'fa-shield-alt' : ($securityScore >= 50 ? 'fa-shield-alt' : 'fa-exclamation-triangle');
    @endphp

    <div class="row g-3">
        <!-- Левая колонка: Обзор безопасности + Активные сессии -->
        <div class="col-12 col-lg-4">
            <!-- Обзор безопасности -->
            <div class="card account-card h-100">
                <div class="card-body">
                    <h3 class="h5 text-warning mb-3">
                        <i class="fas fa-shield-alt"></i> {{ __('account.security_overview') }}
                    </h3>
                    
                    <!-- Уровень безопасности -->
                    <div class="security-main-stat text-center mb-3 p-3">
                        <i class="fas {{ $securityIcon }} fa-3x text-{{ $securityColor }} mb-2"></i>
                        <div class="security-level text-{{ $securityColor }} fw-bold fs-5">
                            {{ __('account.security_' . $securityLevel) }}
                        </div>
                        <div class="security-score text-muted">{{ $securityScore }}/100</div>
                    </div>

                    <!-- Мини-статистика -->
                    <div class="security-mini-stats">
                        <div class="security-mini-item">
                            <i class="fas fa-key text-info"></i>
                            <div class="security-mini-content">
                                <div class="security-mini-label">{{ __('account.password_age') }}</div>
                                <div class="security-mini-value">{{ round($passwordAge) }} {{ __('account.days_ago') }}</div>
                                @if(!$hasChangedPassword)
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ __('account.never_changed') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="security-mini-item">
                            <i class="fas fa-laptop text-primary"></i>
                            <div class="security-mini-content">
                                <div class="security-mini-label">{{ __('account.active_devices') }}</div>
                                <div class="security-mini-value">{{ $activeSessions->count() }}</div>
                            </div>
                        </div>
                        <div class="security-mini-item">
                            @if(!empty($accountInfo['email']))
                                <i class="fas fa-check-circle text-success"></i>
                                <div class="security-mini-content">
                                    <div class="security-mini-label">{{ __('account.email_status') }}</div>
                                    <div class="security-mini-value text-success">{{ __('account.verified') }}</div>
                                </div>
                            @else
                                <i class="fas fa-times-circle text-danger"></i>
                                <div class="security-mini-content">
                                    <div class="security-mini-label">{{ __('account.email_status') }}</div>
                                    <div class="security-mini-value text-danger">{{ __('account.not_set') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Рекомендации -->
                    @if($securityScore < 100)
                    <div class="alert alert-warning mt-3 mb-0 p-2">
                        <small>
                            <i class="fas fa-lightbulb"></i> <strong>{{ __('account.security_tips') }}:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                @if(empty($accountInfo['email']))
                                    <li>{{ __('account.tip_add_email') }}</li>
                                @endif
                                @if($passwordAge > 90)
                                    <li>{{ __('account.tip_change_password') }}</li>
                                @endif
                                @if($activeSessions->count() > 2)
                                    <li>{{ __('account.tip_logout_devices') }}</li>
                                @endif
                            </ul>
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Средняя колонка: Смена Email + Смена Пароля -->
        <div class="col-12 col-lg-4">
            <!-- Смена Email -->
            <div class="card account-card mb-3">
                <div class="card-body">
                    <h3 class="h6 text-warning mb-3">
                        <i class="fas fa-envelope"></i> {{ __('account.changeemail') }}
                    </h3>
                    <form method="post" class="needs-validation" action="{{ route('account.email') }}" novalidate>
                        @csrf
                        <div class="mb-2">
                            <label for="current-password-email" class="form-label small">{{ __('account.currentpassword') }}</label>
                            <div class="password-input-wrapper">
                                <input type="password" name="current_password" id="current-password-email" 
                                       class="form-control form-control-sm @error('current_password') is-invalid @enderror" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('current-password-email')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="new-email" class="form-label small">{{ __('account.newemail') }}</label>
                            <input type="email" name="new_email" id="new-email" 
                                   class="form-control form-control-sm @error('new_email') is-invalid @enderror" 
                                   value="{{ old('new_email', $accountInfo['email'] ?? '') }}" required>
                            @error('new_email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-account btn-sm w-100">
                            <i class="fas fa-save"></i> {{ __('account.updateemail') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Смена Пароля -->
            <div class="card account-card">
                <div class="card-body">
                    <h3 class="h6 text-warning mb-3">
                        <i class="fas fa-lock"></i> {{ __('account.changepassword') }}
                    </h3>
                    <form method="post" class="needs-validation" action="{{ route('account.password') }}" novalidate>
                        @csrf
                        <div class="mb-2">
                            <label for="current-password" class="form-label small">{{ __('account.currentpassword') }}</label>
                            <div class="password-input-wrapper">
                                <input type="password" name="current_password" id="current-password" 
                                       class="form-control form-control-sm @error('current_password') is-invalid @enderror" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('current-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="new-password" class="form-label small">{{ __('account.newpassword') }}</label>
                            <div class="password-input-wrapper">
                                <input type="password" name="new_password" id="new-password" 
                                       class="form-control form-control-sm @error('new_password') is-invalid @enderror" 
                                       minlength="6" maxlength="32" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('new-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <!-- Индикатор силы пароля -->
                            <div class="password-strength mt-1">
                                <div class="password-strength-bar">
                                    <div class="password-strength-fill" id="password-strength-fill"></div>
                                </div>
                                <div class="password-strength-text text-muted small mt-1" id="password-strength-text">
                                    {{ __('account.password_strength') }}: <span id="strength-label">-</span>
                                </div>
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="confirm-password" class="form-label small">{{ __('account.confirmnewpassword') }}</label>
                            <div class="password-input-wrapper">
                                <input type="password" name="confirm_password" id="confirm-password" 
                                       class="form-control form-control-sm @error('confirm_password') is-invalid @enderror" 
                                       minlength="6" maxlength="32" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('confirm_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Требования к паролю -->
                        <div class="password-requirements-compact mb-2">
                            <small class="text-muted">
                                <span id="req-length" class="req-item">
                                    <i class="fas fa-circle"></i> 6+ {{ __('account.chars') }}
                                </span>
                                <span id="req-uppercase" class="req-item">
                                    <i class="fas fa-circle"></i> A-Z
                                </span>
                                <span id="req-number" class="req-item">
                                    <i class="fas fa-circle"></i> 0-9
                                </span>
                            </small>
                        </div>
                        <button type="submit" class="btn btn-account btn-sm w-100">
                            <i class="fas fa-save"></i> {{ __('account.changepassword') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Правая колонка: Активные сессии -->
        <div class="col-12 col-lg-4">
            <div class="card account-card h-100">
                <div class="card-body d-flex flex-column">
                    <h3 class="h6 text-warning mb-3">
                        <i class="fas fa-laptop"></i> {{ __('account.active_sessions') }}
                    </h3>
                    <div class="sessions-list-compact flex-grow-1">
                        @foreach($activeSessions as $index => $session)
                        <div class="session-item-compact">
                            <div class="session-icon-compact">
                                @if(str_contains(strtolower($session->device_type ?? ''), 'mobile'))
                                    <i class="fas fa-mobile-alt text-info"></i>
                                @elseif(str_contains(strtolower($session->device_type ?? ''), 'tablet'))
                                    <i class="fas fa-tablet-alt text-info"></i>
                                @else
                                    <i class="fas fa-desktop text-primary"></i>
                                @endif
                            </div>
                            <div class="session-info-compact">
                                <div class="session-device-compact">
                                    {{ $session->device_type ?? __('account.unknown_device') }}
                                    @if($index === 0)
                                        <span class="badge bg-success badge-sm">{{ __('account.current') }}</span>
                                    @endif
                                </div>
                                <div class="session-details-compact">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> {{ $session->ip_address }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <form method="post" action="{{ route('account.sessions.destroy') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            <i class="fas fa-sign-out-alt"></i> {{ __('account.logout_all_devices') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


        <!-- Смена Аватара -->
        <div class="col-12">
            <div class="card account-card">
                <div class="card-body">
                    <h3 class="h4 text-warning mb-3">{{ __('account.changeavatar') }}</h3>
                    <form method="post" action="{{ route('account.avatar') }}">
                        @csrf
                        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3 mb-3">
                            @foreach($availableAvatars as $av)
                                <div class="col text-center">
                                    <img src="{{ asset('img/accountimg/profile_pics/' . $av->filename) }}"
                                         class="avatar-option img-fluid {{ ($currencies['avatar'] ?? '') === $av->filename ? 'selected' : '' }}"
                                         data-value="{{ $av->filename }}" alt="{{ $av->display_name }}">
                                    <span class="mt-2 small">{{ $av->display_name }}</span>
                                </div>
                            @endforeach
                            <div class="col text-center">
                                <img src="{{ asset('img/accountimg/profile_pics/user.jpg') }}"
                                     class="avatar-option img-fluid {{ empty($currencies['avatar']) ? 'selected' : '' }}"
                                     data-value="" alt="Default avatar">
                                <span class="mt-2 small">Default</span>
                            </div>
                        </div>
                        <input type="hidden" name="avatar" id="avatar" value="{{ $currencies['avatar'] ?? '' }}">
                        <div class="text-center">
                            <button type="submit" class="btn btn-account">{{ __('account.updateavatar') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Avatar selection
document.addEventListener('DOMContentLoaded', function() {
    const avatarOptions = document.querySelectorAll('.avatar-option');
    const avatarInput = document.getElementById('avatar');
    
    if (avatarOptions.length > 0 && avatarInput) {
        avatarOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                avatarOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update hidden input value
                avatarInput.value = this.getAttribute('data-value');
            });
            
            // Make cursor pointer
            option.style.cursor = 'pointer';
        });
    }
});

// Password strength checker
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new-password');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            checkPasswordStrength(password);
        });
    }
});

function checkPasswordStrength(password) {
    const fill = document.getElementById('password-strength-fill');
    const label = document.getElementById('strength-label');
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqNumber = document.getElementById('req-number');
    
    if (!fill || !label) return;
    
    let strength = 0;
    
    // Check length
    if (password.length >= 6) {
        strength += 1;
        if (reqLength) reqLength.classList.add('valid');
    } else {
        if (reqLength) reqLength.classList.remove('valid');
    }
    
    // Check for uppercase
    if (/[A-Z]/.test(password)) {
        strength += 1;
        if (reqUppercase) reqUppercase.classList.add('valid');
    } else {
        if (reqUppercase) reqUppercase.classList.remove('valid');
    }
    
    // Check for number
    if (/[0-9]/.test(password)) {
        strength += 1;
        if (reqNumber) reqNumber.classList.add('valid');
    } else {
        if (reqNumber) reqNumber.classList.remove('valid');
    }
    
    // Update UI
    fill.className = 'password-strength-fill';
    if (strength === 0 || password.length === 0) {
        label.textContent = '-';
        label.style.color = '#a0a0a0';
    } else if (strength === 1) {
        fill.classList.add('weak');
        label.textContent = '{{ __("account.weak") }}';
        label.style.color = '#dc3545';
    } else if (strength === 2) {
        fill.classList.add('medium');
        label.textContent = '{{ __("account.medium") }}';
        label.style.color = '#ffc107';
    } else {
        fill.classList.add('strong');
        label.textContent = '{{ __("account.strong") }}';
        label.style.color = '#28a745';
    }
}
</script>
@endpush

@endsection


