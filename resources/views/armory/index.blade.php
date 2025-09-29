@extends('layouts.app')

@section('title', __('armory.title'))

@section('content')
<div class="armory-main">
    <div class="armory-container">
        <h1 class="armory-title">{{ __('armory.title') }}</h1>

        <!-- Navigation Cards -->
        <div class="armory-grid">
            <!-- Arena 2v2 -->
            <a href="{{ route('armory.arena-2v2') }}" class="armory-card">
                <div class="card-icon">
                    <img src="{{ asset('img/armory/arena.webp') }}" alt="2v2 Arena">
                </div>
                <h3 class="card-title">{{ __('armory.arena_2v2_title') }}</h3>
                <p class="card-description">{{ __('armory.arena_2v2_description') }}</p>
            </a>

            <!-- Arena 3v3 -->
            <a href="{{ route('armory.arena-3v3') }}" class="armory-card">
                <div class="card-icon">
                    <img src="{{ asset('img/armory/arena.webp') }}" alt="3v3 Arena">
                </div>
                <h3 class="card-title">{{ __('armory.arena_3v3_title') }}</h3>
                <p class="card-description">{{ __('armory.arena_3v3_description') }}</p>
            </a>

            <!-- Arena 5v5 -->
            <a href="{{ route('armory.arena-5v5') }}" class="armory-card">
                <div class="card-icon">
                    <img src="{{ asset('img/armory/arena.webp') }}" alt="5v5 Arena">
                </div>
                <h3 class="card-title">{{ __('armory.arena_5v5_title') }}</h3>
                <p class="card-description">{{ __('armory.arena_5v5_description') }}</p>
            </a>

            <!-- Solo PvP -->
            <a href="{{ route('armory.solo-pvp') }}" class="armory-card">
                <div class="card-icon">
                    <img src="{{ asset('img/armory/sword.webp') }}" alt="Solo PvP">
                </div>
                <h3 class="card-title">{{ __('armory.solo_pvp_title') }}</h3>
                <p class="card-description">{{ __('armory.solo_pvp_description') }}</p>
            </a>
        </div>

        <!-- Statistics Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ number_format($totalPlayers) }}</div>
                <div class="stat-label">{{ __('armory.stat_players') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ number_format($totalTeams) }}</div>
                <div class="stat-label">{{ __('armory.stat_teams') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ number_format($totalMatches) }}</div>
                <div class="stat-label">{{ __('armory.stat_matches') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection