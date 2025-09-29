@extends('layouts.app')

@section('title', $title ?? __('armory.arenateam_page_title'))

@section('content')
<div class="arena-content tw-bg-900 tw-text-white">
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
        @if (!$team)
            <h1 class="tw-text-4xl tw-font-bold tw-text-center tw-text-amber-400 tw-mb-6">{{ __('armory.team_not_found') }}</h1>
            <div class="tw-text-center">
                <a href="{{ route('armory.index') }}" class="tw-text-blue-400 hover:tw-underline">{{ __('armory.title') }}</a>
            </div>
        @else
            <h1 class="tw-text-4xl tw-font-bold tw-text-center tw-text-amber-400 tw-mb-6">{{ $team->team_name }} - {{ App\Http\Controllers\ArmoryController::getTeamTypeName($team->type) }} {{ __('armory.arenateam_suffix') }}</h1>

            @include('armory.partials.navbar')

            <!-- Team Summary -->
            <h2 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-amber-400 tw-mb-4">{{ __('armory.arenateam_team_summary') }}</h2>
            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4 tw-mb-8">
                <div class="summary-item summary-item-{{ $team->type == 2 ? '2v2' : ($team->type == 3 ? '3v3' : ($team->type == 5 ? '5v5' : 'default')) }} tw-p-2 sm:tw-p-3 tw-rounded-lg">
                    <p class="tw-text-base sm:tw-text-lg tw-text-gray-300">{{ __('armory.arenateam_rating') }}</p>
                    <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gold-300 summary-value">{{ $team->rating }}</p>
                </div>
                <div class="summary-item summary-item-{{ $team->type == 2 ? '2v2' : ($team->type == 3 ? '3v3' : ($team->type == 5 ? '5v5' : 'default')) }} tw-p-2 sm:tw-p-3 tw-rounded-lg">
                    <p class="tw-text-base sm:tw-text-lg tw-text-gray-300">{{ __('armory.arenateam_winrate') }}</p>
                    <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gold-300 summary-value">{{ $team->winrate }}%</p>
                </div>
                <div class="summary-item summary-item-{{ $team->type == 2 ? '2v2' : ($team->type == 3 ? '3v3' : ($team->type == 5 ? '5v5' : 'default')) }} tw-p-2 sm:tw-p-3 tw-rounded-lg">
                    <p class="tw-text-base sm:tw-text-lg tw-text-gray-300">{{ __('armory.arenateam_season_games') }}</p>
                    <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gold-300 summary-value">{{ $team->seasonGames }}</p>
                </div>
                <div class="summary-item summary-item-{{ $team->type == 2 ? '2v2' : ($team->type == 3 ? '3v3' : ($team->type == 5 ? '5v5' : 'default')) }} tw-p-2 sm:tw-p-3 tw-rounded-lg">
                    <p class="tw-text-base sm:tw-text-lg tw-text-gray-300">{{ __('armory.arenateam_season_wins') }}</p>
                    <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gold-300 summary-value">{{ $team->seasonWins }}</p>
                </div>
                <div class="summary-item summary-item-{{ $team->type == 2 ? '2v2' : ($team->type == 3 ? '3v3' : ($team->type == 5 ? '5v5' : 'default')) }} tw-p-2 sm:tw-p-3 tw-rounded-lg">
                    <p class="tw-text-base sm:tw-text-lg tw-text-gray-300">{{ __('armory.arenateam_season_losses') }}</p>
                    <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gold-300 summary-value">{{ $team->seasonLosses }}</p>
                </div>
                <div class="summary-item summary-item-{{ $team->type == 2 ? '2v2' : ($team->type == 3 ? '3v3' : ($team->type == 5 ? '5v5' : 'default')) }} tw-p-2 sm:tw-p-3 tw-rounded-lg">
                    <p class="tw-text-base sm:tw-text-lg tw-text-gray-300">{{ __('armory.arenateam_week_games') }}</p>
                    <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gold-300 summary-value">{{ $team->weekGames }}</p>
                </div>
                <div class="summary-item summary-item-{{ $team->type == 2 ? '2v2' : ($team->type == 3 ? '3v3' : ($team->type == 5 ? '5v5' : 'default')) }} tw-p-2 sm:tw-p-3 tw-rounded-lg">
                    <p class="tw-text-base sm:tw-text-lg tw-text-gray-300">{{ __('armory.arenateam_week_wins') }}</p>
                    <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gold-300 summary-value">{{ $team->weekWins }}</p>
                </div>
                <div class="summary-item summary-item-{{ $team->type == 2 ? '2v2' : ($team->type == 3 ? '3v3' : ($team->type == 5 ? '5v5' : 'default')) }} tw-p-2 sm:tw-p-3 tw-rounded-lg">
                    <p class="tw-text-base sm:tw-text-lg tw-text-gray-300">{{ __('armory.arenateam_week_losses') }}</p>
                    <p class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gold-300 summary-value">{{ $team->weekLosses }}</p>
                </div>
            </div>

            <!-- Team Members -->
            <h2 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-amber-400 tw-mb-4">{{ __('armory.arenateam_team_members') }}</h2>
            @if (count($members) == 0)
                <p class="tw-text-center tw-text-lg tw-text-amber-400">{{ __('armory.arenateam_no_members') }}</p>
            @else
                <div class="table-container tw-overflow-x-auto tw-rounded-lg tw-shadow-lg">
                    <table class="tw-w-full tw-text-sm tw-text-center tw-bg-gray-800">
                        <thead class="tw-bg-gray-700 tw-text-amber-400 tw-uppercase">
                            <tr>
                                <th class="tw-py-3 tw-px-6">{{ __('armory.arenateam_name') }}</th>
                                <th class="tw-py-3 tw-px-6">{{ __('armory.arenateam_faction') }}</th>
                                <th class="tw-py-3 tw-px-6">{{ __('armory.arenateam_race') }}</th>
                                <th class="tw-py-3 tw-px-6">{{ __('armory.arenateam_class') }}</th>
                                <th class="tw-py-3 tw-px-6">{{ __('armory.arenateam_personal_rating') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($members as $member)
                                @php
                                    $faction = App\Http\Controllers\ArmoryController::getFaction($member->race);
                                @endphp
                                <tr class="tw-transition tw-duration-200 hover:tw-bg-gray-700">
                                    <td class="tw-py-3 tw-px-6">{{ $member->name }}</td>
                                    <td class="tw-py-3 tw-px-6">
                                        <img src="{{ App\Http\Controllers\ArmoryController::factionIconByName($faction) }}" alt="{{ $faction }}" title="{{ $faction }}" class="tw-inline-block tw-w-6 tw-h-6 tw-rounded">
                                    </td>
                                    <td class="tw-py-3 tw-px-6">
                                        <img src="{{ App\Http\Controllers\ArmoryController::raceIcon($member->race, $member->gender) }}" alt="{{ $member->race }}" title="{{ $member->race }}" class="tw-inline-block tw-w-6 tw-h-6 tw-rounded">
                                    </td>
                                    <td class="tw-py-3 tw-px-6">
                                        <img src="{{ App\Http\Controllers\ArmoryController::classIcon($member->class) }}" alt="{{ $member->class }}" title="{{ $member->class }}" class="tw-inline-block tw-w-6 tw-h-6 tw-rounded">
                                    </td>
                                    <td class="tw-py-3 tw-px-6">{{ $member->personal_rating }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .arena-content {
        min-height: calc(100vh - 200px);
    }

    .summary-item-2v2 {
        background: linear-gradient(to right, #dc2626, #7f1d1d);
    }

    .summary-item-3v3 {
        background: linear-gradient(to right, #15803d, #064e3b);
    }

    .summary-item-5v5 {
        background: linear-gradient(to right, #1e40af, #1e1b4b);
    }

    .summary-item-default {
        background: linear-gradient(to right, #4b5563, #1f2937);
    }

    .summary-value {
        color: #ffcc00 !important;
    }

    .table-container {
        scrollbar-width: thin;
        scrollbar-color: #ffcc00 #1f2937;
        font-family: 'Arial', sans-serif;
    }

    .table-container::-webkit-scrollbar {
        width: 8px;
    }

    .table-container::-webkit-scrollbar-track {
        background: #1f2937;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: #ffcc00;
        border-radius: 4px;
    }

    .arena-nav-wrapper .nav-container {
        border: 2px double #dc2626;
    }
</style>
@endsection