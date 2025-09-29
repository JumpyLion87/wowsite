@extends('layouts.app')

@section('title', __('armory.arena_3v3_page_title'))

@section('content')
<div class="armory-main">
    <div class="armory-container">
        <h1 class="armory-title">{{ __('armory.arena_3v3_title') }}</h1>

        @include('armory.partials.navbar')

        @if (count($teams) == 0)
            <div class="no-data">{{ __('armory.arena_3v3_no_teams') }}</div>
        @else
            <div class="table-container">
                <table class="wow-table">
                    <thead>
                        <tr>
                            <th>{{ __('armory.arena_3v3_rank') }}</th>
                            <th>{{ __('armory.arena_3v3_name') }}</th>
                            <th>{{ __('armory.arena_3v3_faction') }}</th>
                            <th>{{ __('armory.arena_3v3_wins') }}</th>
                            <th>{{ __('armory.arena_3v3_losses') }}</th>
                            <th>{{ __('armory.arena_3v3_winrate') }}</th>
                            <th>{{ __('armory.arena_3v3_rating') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rank = 1;
                            $teamCount = count($teams);
                        @endphp
                        @foreach ($teams as $team)
                            @php
                                $rowClass = ($rank <= 3 && $teamCount >= 3) ? 'top3' : '';
                                $faction = App\Http\Controllers\ArmoryController::getFaction($team->race);
                            @endphp
                            <tr class="{{ $rowClass }}" onclick="window.location='/armory/arenateam?arenaTeamId={{ $team->arenaTeamId }}';">
                                <td>{{ $rank }}</td>
                                <td>{{ $team->team_name }}</td>
                                <td>
                                    <img src="{{ App\Http\Controllers\ArmoryController::factionIconByName($faction) }}" alt="{{ $faction }}" class="icon-small">
                                </td>
                                <td>{{ $team->seasonWins }}</td>
                                <td>{{ $team->seasonLosses }}</td>
                                <td>{{ $team->winrate }}%</td>
                                <td>{{ $team->rating }}</td>
                            </tr>
                            @php $rank++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<style>
.armory-main {
    min-height: 100vh;
    padding: 20px 0;
}

.armory-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.armory-title {
    text-align: center;
    color: #ffd700;
    font-size: 2.5rem;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px #000;
}

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

.wow-table tr.top3 {
    background: linear-gradient(to right, #15803d, #064e3b);
}

.wow-table tr.top3:hover {
    filter: brightness(1.2);
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