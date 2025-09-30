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
    
    <ul class="nav nav-tabs account-tabs mb-4 justify-content-center" role="tablist" id="accountTabs">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">{{ __('Overview') }}</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#characters" type="button" role="tab">{{ __('Characters') }}</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">{{ __('Security') }}</button>
        </li>
    </ul>

    <div class="tab-content" id="accountTabContent">
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4 align-items-stretch overview-grid">
                <div class="col-12 col-lg-8 d-flex flex-column gap-4">
                    <div class="card account-card h-100 d-flex flex-column">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('Basic Info') }}</h3>
                            <img src="{{ asset('img/accountimg/profile_pics/' . ($currencies['avatar'] ?? 'user.jpg')) }}" alt="avatar" class="account-profile-pic mb-3">
                            <div class="row text-start justify-content-center">
                                <div class="col-10 col-md-8">
                                    <p><strong>{{ __('Username') }}:</strong> {{ $accountInfo['username'] }}</p>
                                    <p><strong>{{ __('Account ID') }}:</strong> {{ $accountInfo['id'] }}</p>
                                    <p><strong>{{ __('Join Date') }}:</strong> {{ $accountInfo['joindate'] }}</p>
                                    <p><strong>{{ __('Last Login') }}:</strong> {{ $accountInfo['last_login'] ?? __('Never') }}</p>
                                    @if($banInfo)
                                        <p><strong>{{ __('Status') }}:</strong>
                                            <span class="text-danger">{{ __('Banned') }}</span>
                                            ({{ $banInfo->banreason ?? 'No reason' }},
                                            {{ $banInfo->unbandate ? date('Y-m-d H:i:s', $banInfo->unbandate) : __('Permanent') }})
                                        </p>
                                    @else
                                        <p><strong>{{ __('Status') }}:</strong> <span class="text-success">{{ __('Active') }}</span></p>
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
                                            <th>{{ __('Action') }}</th>
                                            <th>{{ __('Character') }}</th>
                                            <th>{{ __('Timestamp') }}</th>
                                            <th>{{ __('Details') }}</th>
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
                    <div class="card account-card">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('Contact') }}</h3>
                            <p><strong>{{ __('Email') }}:</strong> {{ $accountInfo['email'] ?? __('Not set') }}</p>
                            <p><strong class="text-warning">{{ __('Expansion') }}:</strong> {{ $accountInfo['expansion'] }}</p>
                            @if(in_array($role, ['admin','moderator']) || ($gmlevel ?? 0) > 0)
                                <a href="{{ route('home') }}" class="btn btn-account mt-2">{{ __('Admin Panel') }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="card account-card">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ __('Wealth') }}</h3>
                            <p><strong>{{ __('Points') }}:</strong> {{ $currencies['points'] }}</p>
                            <p><strong>{{ __('Tokens') }}:</strong> {{ $currencies['tokens'] }}</p>
                            <hr>
                            <p><strong>{{ __('Total characters') }}:</strong> {{ $totalCharacters }}</p>
                            <p><strong>{{ __('Highest level') }}:</strong> {{ $highestLevel }}</p>
                            <p><strong>{{ __('Online now') }}:</strong> {{ $onlineCount }}</p>
                            <p><strong>{{ __('Total gold') }}:</strong> <span class="account-gold">{{ number_format($totalGold / 10000, 2) }}g</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="characters" role="tabpanel" aria-labelledby="characters-tab">
            <h2 class="h3 text-warning mb-4">{{ __('Your Characters') }}</h2>
            @if ($characters->count())
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                @foreach ($characters as $char)
                <div class="col">
                    <div class="card account-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-2 fw-bold text-warning">{{ $char->name }}</div>
                            <p>{{ __('Level') }} {{ $char->level }}</p>
                            <p>{{ __('Gold') }}: <span class="account-gold">{{ number_format($char->money / 10000, 2) }}g</span></p>
                            <p>{{ __('Status') }}: {{ $char->online ? __('Online') : __('Offline') }}</p>
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
                                        <option value="">{{ __('Select a city') }}</option>
                                        <option value="shattrath">Shattrath</option>
                                        <option value="dalaran">Dalaran</option>
                                    </select>
                                </div>
                                <button class="btn btn-account" type="submit" {{ $remaining > 0 ? 'disabled' : '' }}>{{ __('Teleport') }}</button>
                            </form>
                            @if($remaining > 0)
                                <p class="teleport-cooldown mt-2">{{ __('Teleport Cooldown: :min minutes', ['min' => $minutes]) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <p class="text-center">{{ __('You have no characters yet.') }}</p>
            @endif
        </div>

        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
            <div class="mb-4">
                <h3 class="h4 text-warning">{{ __('Change Email') }}</h3>
                <form method="post" class="row g-3 justify-content-center" action="{{ route('account.email') }}">
                    @csrf
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="current-password-email">{{ __('Current Password') }}</label>
                        <input class="form-control" type="password" id="current-password-email" name="current_password" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="new-email">{{ __('New Email') }}</label>
                        <input class="form-control" type="email" id="new-email" name="new_email" required value="{{ $accountInfo['email'] ?? '' }}">
                    </div>
                    <div class="col-12 text-center">
                        <button class="btn btn-account" type="submit">{{ __('Update Email') }}</button>
                    </div>
                </form>
            </div>

            <div class="mb-4">
                <h3 class="h4 text-warning">{{ __('Change Password') }}</h3>
                <form method="post" class="row g-3 justify-content-center" action="{{ route('account.password') }}">
                    @csrf
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="current-password">{{ __('Current Password') }}</label>
                        <input class="form-control" type="password" id="current-password" name="current_password" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="new-password">{{ __('New Password') }}</label>
                        <input class="form-control" type="password" id="new-password" name="new_password" required minlength="6" maxlength="32">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="confirm-password">{{ __('Confirm New Password') }}</label>
                        <input class="form-control" type="password" id="confirm-password" name="confirm_password" required minlength="6" maxlength="32">
                    </div>
                    <div class="col-12 text-center">
                        <button class="btn btn-account" type="submit">{{ __('Change Password') }}</button>
                    </div>
                </form>
            </div>

            <div class="mb-4">
                <h3 class="h4 text-warning">{{ __('Change Avatar') }}</h3>
                <form method="post" class="row g-3 justify-content-center" action="{{ route('account.avatar') }}">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">{{ __('Select Avatar') }}</label>
                        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-2 account-gallery">
                            @foreach($availableAvatars as $av)
                                <div class="col text-center">
                                    <img src="{{ asset('img/accountimg/profile_pics/' . $av->filename) }}"
                                         class="{{ ($currencies['avatar'] ?? '') === $av->filename ? 'selected' : '' }}"
                                         onclick="document.getElementById('avatar').value='{{ $av->filename }}'"
                                         alt="{{ $av->display_name }}">
                                    <span>{{ $av->display_name }}</span>
                                </div>
                            @endforeach
                            <div class="col text-center">
                                <img src="{{ asset('img/accountimg/profile_pics/user.jpg') }}"
                                     class="{{ empty($currencies['avatar']) ? 'selected' : '' }}"
                                     onclick="document.getElementById('avatar').value=''"
                                     alt="Default avatar">
                                <span>Default</span>
                            </div>
                        </div>
                        <input type="hidden" name="avatar" id="avatar" value="{{ $currencies['avatar'] ?? '' }}">
                    </div>
                    <div class="col-12 text-center">
                        <button class="btn btn-account" type="submit">{{ __('Update Avatar') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('#accountTabs .nav-link');
    const panes = document.querySelectorAll('#accountTabContent .tab-pane');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('show', 'active'));
            this.classList.add('active');
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            if (target) {
                target.classList.add('show', 'active');
            }
        });
    });
});
</script>
@endpush


