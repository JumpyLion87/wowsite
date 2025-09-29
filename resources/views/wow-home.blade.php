@extends('layouts.app')

@section('content')
<div class="home-container">
    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title">{{ __('home.home_intro_title') }}</h1>
        <p class="hero-subtitle">{{ __('home.home_intro_tagline') }}</p>
        
        <div class="hero-buttons">
            <a href="{{ route('register') }}" class="hero-button">
                <i class="fas fa-user-plus"></i>
                {{ __('home.home_create_account') }}
            </a>
            <a href="{{ route('download') }}" class="hero-button">
                <i class="fas fa-download"></i>
                {{ __('home.home_download') }}
            </a>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="feature-title">{{ __('home.feature_security_title') }}</h3>
            <p class="feature-description">{{ __('home.feature_security_description') }}</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="feature-title">{{ __('home.feature_community_title') }}</h3>
            <p class="feature-description">{{ __('home.feature_community_description') }}</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-gamepad"></i>
            </div>
            <h3 class="feature-title">{{ __('home.feature_gameplay_title') }}</h3>
            <p class="feature-description">{{ __('home.feature_gameplay_description') }}</p>
        </div>
    </section>

    <!-- News Section -->
    <section class="news-section">
        <h2 class="section-title">{{ __('home.home_news_title') }}</h2>
        
        <div class="news-grid">
            @if($latestNews->isEmpty())
                <p>{{ __('home.home_no_news') }}</p>
            @else
                @foreach($latestNews as $news)
                    <div class="news-item">
                        <a href="{{ route('news.show', $news->slug) }}">
                            <img src="{{ asset($news->image_url) }}" alt="{{ $news->title }}" class="news-image">
                            <h4 class="news-title">{{ $news->title }}</h4>
                            <p class="news-date">{{ $news->post_date->format('M j, Y') }}</p>
                        </a>
                    </div>
                @endforeach
            @endif
        </div>
    </section>

    <!-- Server Status -->
    <div class="server-status-widget">
        <h1 class="server-status-title">{{ __('home.server_status_title') }}</h1>
        @include('partials.server-status')
    </div>

    <!-- Discord Widget -->
    <section class="discord-widget">
        <h3 class="section-title">{{ __('home.home_discord_title') }}</h3>
        <iframe src="https://discord.com/widget?id=1405755152085815337&theme=dark"
                width="100%" height="400" allowtransparency="true"
                frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts">
        </iframe>
    </section>

    <!-- Bug Tracker Section -->
    <section class="bugtracker-section">
        <h2 class="section-title">{{ __('home.home_bugtracker_title') }}</h2>
        <div class="bugtracker-content">
            <p>{{ __('home.home_bugtracker_content') }}</p>
            <a href="{{ route('bugtracker') }}" class="hero-button">
                <i class="fas fa-bug"></i>
                {{ __('home.home_report_bug') }}
            </a>
        </div>
    </section>
</div>
@endsection