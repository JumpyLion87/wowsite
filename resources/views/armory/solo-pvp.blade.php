@extends('layouts.app')

@section('content')
<div class="armory-main">
    <div class="armory-container">
        <h1 class="armory-title">{{ __('armory.solo_pvp_title') }}</h1>

        @include('armory.partials.navbar')

        <div class="table-container">
            <table class="wow-table">
                <thead>
                    <tr>
                        <th>{{ __('armory.solo_pvp_rank') }}</th>
                        <th>{{ __('armory.solo_pvp_name') }}</th>
                        <th>{{ __('armory.solo_pvp_guild') }}</th>
                        <th>{{ __('armory.solo_pvp_faction') }}</th>
                        <th>{{ __('armory.solo_pvp_race') }}</th>
                        <th>{{ __('armory.solo_pvp_class') }}</th>
                        <th>{{ __('armory.solo_pvp_level') }}</th>
                        <th>{{ __('armory.solo_pvp_kills') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($players) == 0)
                        <tr>
                            <td colspan="8" class="no-data">{{ __('armory.solo_pvp_no_players') }}</td>
                        </tr>
                    @else
                        @php
                            $rank = 1;
                            $playerCount = count($players);
                        @endphp
                        @foreach($players as $player)
                            @php
                                $rowClass = ($rank <= 5 && $playerCount >= 5) ? 'top5' : '';
                            @endphp
                            <tr class="{{ $rowClass }}" onclick="window.location='/character?guid={{ $player->guid }}';">
                                <td>{{ $rank }}</td>
                                <td>
                                    <a href="/character?guid={{ $player->guid }}" class="player-link">
                                        {{ $player->name }}
                                    </a>
                                </td>
                                <td>{{ $player->guild_name ?? __('armory.solo_pvp_no_guild') }}</td>
                                <td>
                                    <img src="{{ $player->faction_icon }}" alt="{{ __('armory.faction') }}" class="icon-small">
                                </td>
                                <td>
                                    <img src="{{ $player->race_icon }}" alt="{{ __('armory.race') }}" class="icon-small">
                                </td>
                                <td>
                                    <img src="{{ $player->class_icon }}" alt="{{ __('armory.class') }}" class="icon-small">
                                </td>
                                <td>{{ $player->level }}</td>
                                <td>{{ $player->totalKills }}</td>
                            </tr>
                            @php
                                $rank++;
                            @endphp
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.table-container {
    overflow-x: auto;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.7);
    border: 2px solid #ffd700;
    margin-top: 2rem;
}

.wow-table {
    width: 100%;
    border-collapse: collapse;
    color: #fff;
}

.wow-table th {
    background: linear-gradient(135deg, #8b4513 0%, #a0522d 100%);
    color: #ffd700;
    padding: 1rem;
    text-transform: uppercase;
    font-weight: bold;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.wow-table td {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid #444;
    text-align: center;
}

.wow-table tr {
    transition: background-color 0.3s ease;
    cursor: pointer;
}

.wow-table tr:hover {
    background-color: rgba(139, 69, 19, 0.3);
}

.wow-table tr.top5 {
    background: linear-gradient(to right, #161616, #043a9e);
}

.wow-table tr.top5:hover {
    background: linear-gradient(to right, #5807db, #0609c79c);
    filter: brightness(1.2);
}

.player-link {
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.player-link:hover {
    color: #ffd700;
    text-decoration: underline;
}

.icon-small {
    width: 24px;
    height: 24px;
    border-radius: 4px;
}

.no-data {
    text-align: center;
    color: #ffd700;
    font-size: 1.2rem;
    padding: 2rem;
}
</style>
@endsection