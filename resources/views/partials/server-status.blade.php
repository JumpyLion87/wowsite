<link rel="stylesheet" href="{{ asset('assets/css/server-status.css') }}">

<div class="server-status">
    <ul>
        @foreach($realmlist as $index => $realm)
            <li>
                <img src="{{ asset($realm['logo']) }}" alt="{{ __('home.server_status.realm_logo_alt') }}" height="40"><br>
                <strong>{{ $realm['name'] }}:</strong><br>
                @php
                    $realmStatus = $serverStatus[$index] ?? ['online' => false, 'online_players' => 0, 'uptime' => __('home.server_status.uptime_none')];
                @endphp
                @if($realmStatus['online'])
                    <span class="online">{{ __('home.server_status.status_online') }}</span><br>
                    <span class="players">{{ __('home.server_status.players_online', ['count' => $realmStatus['online_players']]) }}</span><br>
                    <span class="uptime">{{ __('home.server_status.uptime', ['time' => $realmStatus['uptime']]) }}</span><br>
                @else
                    <span class="offline">{{ __('home.server_status.status_offline') }}</span><br>
                    <span class="players">{{ __('home.server_status.players_online_none') }}</span><br>
                    <span class="uptime">{{ __('home.server_status.uptime_none') }}</span><br>
                @endif
                <span class="realm-ip">{{ __('home.server_status.realmlist', ['address' => $realm['address']]) }}</span>
            </li>
        @endforeach
        
        <!-- Server Statistics -->
        <li>
            <strong class="server-stats-title">{{ __('home.server_status.server_stats_title') }}</strong>
            <span class="players">{{ __('home.server_status.total_accounts') }}: {{ $totalAccounts }}</span><br>
            <span class="players">{{ __('home.server_status.total_characters') }}: {{ $totalCharacters }}</span><br>
            <span class="players">{{ __('home.server_status.last_registered') }}: {{ $lastRegisteredUser }}</span>
        </li>
    </ul>
</div>