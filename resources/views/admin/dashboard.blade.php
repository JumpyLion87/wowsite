@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Верхняя панель: Быстрая статистика -->
    <div class="card quick-stats">
        <div class="card-header">{{ __('admin_dashboard.quick_stats_header') }}</div>
        <div class="card-body">
            <ul>
                <li><span class="stat-label">{{ __('admin_dashboard.total_website_users') }}:</span> <span class="stat-value">{{ $totalUsers }}</span></li>
                <li><span class="stat-label">{{ __('admin_dashboard.total_ingame_accounts') }}:</span> <span class="stat-value">{{ $totalAccounts }}</span></li>
                <li><span class="stat-label">{{ __('admin_dashboard.total_characters') }}:</span> <span class="stat-value">{{ $totalChars }}</span></li>
                <li><span class="stat-label">{{ __('admin_dashboard.active_bans') }}:</span> <span class="stat-value">{{ $totalBans }}</span></li>
            </ul>
        </div>
    </div>

    <!-- Нижний блок: Админы и бани (в две колонки) -->
    <div class="grid-container">
        <!-- Левая колонка: Админы и модераторы -->
        <div class="grid-item">
            <div class="card recent-staff">
                <div class="card-header">{{ __('admin_dashboard.recent_staff_header') }}</div>
                <div class="card-body">
                    <form class="search-form" method="GET" action="{{ route('admin.dashboard') }}">
                        <!-- поля формы -->
                    </form>
                    <div class="table-wrapper">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('admin_dashboard.table_username') }}</th>
                                    <th>{{ __('admin_dashboard.table_email') }}</th>
                                    <th>{{ __('admin_dashboard.table_points') }}</th>
                                    <th>{{ __('admin_dashboard.table_tokens') }}</th>
                                    <th>{{ __('admin_dashboard.table_role') }}</th>
                                    <th>{{ __('admin_dashboard.table_online') }}</th>
                                    <th>{{ __('admin_dashboard.table_ban_status') }}</th>
                                    <th>{{ __('admin_dashboard.table_last_updated') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email ?? __('admin_dashboard.email_not_set') }}</td>
                                        <td>{{ $user->points }}</td>
                                        <td>{{ $user->tokens }}</td>
                                        <td><span class="status-{{ $user->role }}">{{ ucfirst(__($user->role)) }}</span></td>
                                        <td>@onlineStatus($user->online)</td>
                                        <td>@accountStatus(['isLocked' => $user->locked, 'banInfo' => $user->banInfo ?? []])</td>
                                        <td>{{ $user->last_updated ? \Carbon\Carbon::parse($user->last_updated)->format('M j, Y H:i') : __('admin_dashboard.never') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Правая колонка: Бани -->
        <div class="grid-item">
            <div class="card recent-bans">
                <div class="card-header">{{ __('admin_dashboard.recent_bans_header') }}</div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('admin_dashboard.table_account_id') }}</th>
                                    <th>{{ __('admin_dashboard.table_username') }}</th>
                                    <th>{{ __('admin_dashboard.table_ban_reason') }}</th>
                                    <th>{{ __('admin_dashboard.table_ban_date') }}</th>
                                    <th>{{ __('admin_dashboard.table_unban_date') }}</th>
                                    <th>{{ __('admin_dashboard.table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bans as $ban)
                                    <tr>
                                        <td>{{ $ban->id }}</td>
                                        <td>{{ $ban->username }}</td>
                                        <td>{{ $ban->banreason ?? __('admin_dashboard.no_reason_provided') }}</td>
                                        <td>{{ $ban->bandate ? \Carbon\Carbon::parse($ban->bandate)->format('M j, Y H:i') : __('admin_dashboard.na') }}</td>
                                        <td>{{ $ban->unbandate ? \Carbon\Carbon::parse($ban->unbandate)->format('M j, Y H:i') : __('admin_dashboard.permanent') }}</td>
                                        <td>
                                            <a href="/admin/users#user-{{ $ban->id }}" class="btn">{{ __('admin_dashboard.manage_button') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
