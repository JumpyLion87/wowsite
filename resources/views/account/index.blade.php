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
                    <div class="card account-card h-100 d-flex flex-column">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('account.basicinfo') }}</h3>
                            <img src="{{ asset('img/accountimg/profile_pics/' . ($currencies['avatar'] ?? 'user.jpg')) }}" alt="avatar" class="account-profile-pic mb-3">
                            <div class="row text-start justify-content-center">
                                <div class="col-10 col-md-8">
                                    <p><strong>{{ __('account.username') }}:</strong> {{ $accountInfo['username'] }}</p>
                                    <p><strong>{{ __('account.accountid') }}:</strong> {{ $accountInfo['id'] }}</p>
                                    <p><strong>{{ __('account.joindate') }}:</strong> {{ $accountInfo['joindate'] }}</p>
                                    <p><strong>{{ __('account.lastlogin') }}:</strong> {{ $accountInfo['last_login'] ?? __('Never') }}</p>
                                    @if($banInfo)
                                        <p><strong>{{ __('account.status') }}:</strong>
                                            <span class="text-danger">{{ __('account.banned') }}</span>
                                            ({{ $banInfo->banreason ?? 'No reason' }},
                                            {{ $banInfo->unbandate ? date('Y-m-d H:i:s', $banInfo->unbandate) : __('account.permanent') }})
                                        </p>
                                    @else
                                        <p><strong>{{ __('account.status') }}:</strong> <span class="text-success">{{ __('account.active') }}</span></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($activityLog && $activityLog->count())
                    <div class="card account-card flex-grow-1 d-flex flex-column">
                        <div class="card-body">
                            <h3 class="h4 text-warning mb-3">{{ __('account.recent_activity') }}</h3>
                            <div class="table-responsive account-table">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('account.action') }}</th>
                                            <th>{{ __('account.character') }}</th>
                                            <th>{{ __('account.timestamp') }}</th>
                                            <th>{{ __('account.details') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activityLog as $log)
                                        <tr>
                                            <td>{{ $log->action }}</td>
                                            <td>{{ $log->character_name ?? '—' }}</td>
                                            <td>{{ date('Y-m-d H:i:s', $log->timestamp) }}</td>
                                            <td>{{ $log->details ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
                            @if(in_array($role, ['admin','moderator']) || ($gmlevel ?? 0) > 0)
                                <a href="{{ route('home') }}" class="btn btn-account mt-2">{{ __('account.adminpanel') }}</a>
                            @endif
                        </div>
                    </div>

                     <!-- Вставьте в раздел "Overview" -->
                    <div class="card account-card d-flex flex-column">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('vote.title') }}</h3>
                            <p>{{ __('vote.description', ['hours' => env('VOTE_COOLDOWN_HOURS'), 'points' => env('VOTE_REWARD_POINTS')]) }}</p>
                            
                            <!-- Ссылка на страницу голосования -->
                            <a href="{{ route('vote.generate') }}" class="btn btn-secondary" target="_blank">
                                {{ __('vote.vote_button') }}
                            </a>
                            @if(session('message') && strpos(session('message'), 'vote') !== false)
                                <div class="alert alert-success mb-3">{{ session('message') }}</div>
                            @endif
                            @if($errors->has('vote'))
                                <div class="alert alert-danger mb-3">{{ $errors->first('vote') }}</div>
                            @endif    
                        </div>
                    </div>

                    <div class="card account-card d-flex flex-column">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('account.wealth') }}</h3>
                            <p><strong>{{ __('account.points') }}:</strong> {{ $currencies['points'] }}</p>
                            <p><strong>{{ __('account.tokens') }}:</strong> {{ $currencies['tokens'] }}</p>
                            <hr>
                            <p><strong>{{ __('account.totalcharacters') }}:</strong> {{ $totalCharacters }}</p>
                            <p><strong>{{ __('account.highestlevel') }}:</strong> {{ $highestLevel }}</p>
                            <p><strong>{{ __('account.onlinenow') }}:</strong> {{ $onlineCount }}</p>
                            <p><strong>{{ __('account.totalgold') }}:</strong> <span class="account-gold">{{ number_format($totalGold / 10000, 2) }}g</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="characters" role="tabpanel" aria-labelledby="characters-tab">
            <h2 class="h3 text-warning mb-4">{{ __('account.yourcharacters') }}</h2>
            @if ($characters->count())
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                @foreach ($characters as $char)
                <div class="col">
                    <div class="card account-card h-100 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-2 fw-bold text-warning">{{ $char->name }}</div>
                            <p>{{ __('account.level') }} {{ $char->level }}</p>
                            <p>{{ __('account.class') }}: {{ $char->class }}</p>
                            <p>{{ __('account.guild') }}: {{ $char->guild_name ?? __('account.noneguild') }}</p>                            
                            <p>{{ __('account.gold') }}: <span class="account-gold">{{ number_format($char->money / 10000, 2) }}g</span></p>
                            <p>{{ __('account.status') }}: {{ $char->online ? __('account.online') : __('account.offline') }}</p>
                            @php
                                $cooldownTs = $teleportCooldowns[$char->guid] ?? 0;
                                $remaining = max(0, ($cooldownTs + 900) - time());
                                $minutes = (int) ceil($remaining / 60);
                            @endphp
                            <form method="post" class="mt-2" action="{{ route('account.teleport') }}">
                                @csrf
                                <input type="hidden" name="guid" value="{{ $char->guid }}">
                                <div class="mb-2">
                                    <select class="form-select" name="destination" required>
                                        <option value="">{{ __('account.selectcity') }}</option>
                                        <option value="shattrath">{{ __('account.shattrath') }}</option>
                                        <option value="dalaran">{{ __('account.dalaran') }}</option>
                                    </select>
                                </div>
                                <button class="btn btn-account" type="submit" {{ $remaining > 0 ? 'disabled' : '' }}>{{ __('account.teleport') }}</button>
                            </form>
                            @if($remaining > 0)
                                <div class="teleport-cooldown mt-2">
                                    <p>{{ __('account.teleportcooldown: :min minutes', ['min' => $minutes]) }}</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($remaining / 900) * 100 }}%" aria-valuenow="{{ $remaining }}" aria-valuemin="0" aria-valuemax="900"></div>
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
    <!-- Разделы безопасности -->
    <div class="row g-4">
        <!-- Смена Email -->
        <div class="col-12 col-md-6">
            <div class="card account-card">
                <div class="card-body">
                    <h3 class="h4 text-warning mb-3">{{ __('account.changeemail') }}</h3>
                    <form method="post" class="needs-validation" action="{{ route('account.email') }}" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="current-password-email" class="form-label">{{ __('account.currentpassword') }}</label>
                                <input type="password" name="current_password" id="current-password-email" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="new-email" class="form-label">{{ __('account.newemail') }}</label>
                                <input type="email" name="new_email" id="new-email" 
                                       class="form-control @error('new_email') is-invalid @enderror" 
                                       value="{{ old('new_email', $accountInfo['email'] ?? '') }}" required>
                                @error('new_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-account d-flex align-items-center gap-2">
                                    {{ __('account.updateemail') }}
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Смена Пароля -->
        <div class="col-12 col-md-6">
            <div class="card account-card">
                <div class="card-body">
                    <h3 class="h4 text-warning mb-3">{{ __('account.changepassword') }}</h3>
                    <form method="post" class="needs-validation" action="{{ route('account.password') }}" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="current-password" class="form-label">{{ __('account.currentpassword') }}</label>
                                <input type="password" name="current_password" id="current-password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="new-password" class="form-label">{{ __('account.newpassword') }}</label>
                                <input type="password" name="new_password" id="new-password" 
                                       class="form-control @error('new_password') is-invalid @enderror" 
                                       minlength="6" maxlength="32" required>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="confirm-password" class="form-label">{{ __('account.confirmnewpassword') }}</label>
                                <input type="password" name="confirm_password" id="confirm-password" 
                                       class="form-control @error('confirm_password') is-invalid @enderror" 
                                       minlength="6" maxlength="32" required>
                                @error('confirm_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-account d-flex align-items-center gap-2">
                                    {{ __('account.changepassword') }}
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Активные сессии -->
        <div class="col-12 col-md-6">
            <div class="card account-card">
                <div class="card-body">
                    <h3 class="h4 text-warning mb-3">{{ __('account.active_sessions') }}</h3>
                    <ul class="list-group list-group-flush">
                        @foreach($activeSessions as $session)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $session->device_type }}
                            <span class="badge bg-secondary">{{ $session->ip_address }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <form method="post" action="{{ route('account.sessions.destroy') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger mt-2">
                            {{ __('account.logout_all_devices') }}
                        </button>
                    </form>
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
@endsection

@push('scripts')

@endpush


