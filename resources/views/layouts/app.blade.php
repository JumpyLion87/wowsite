<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('home_meta_description') }}">
    <meta name="robots" content="index">
    <title>{{ __('home.home_page_title') }} - {{ config('app.name') }}</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-home.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-armory.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/how-to-play.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/online-players.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/character.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vote-notification.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-classes.css') }}">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-authenticated" content="true">
    @endauth
</head>
<body class="home">
    <!-- Header will be included here -->
    @include('layouts.header')
    
    <main style="padding-top: 100px; padding-bottom: 10px;">
        @yield('content')
    </main>
    
    <!-- Footer will be included here -->
    @include('layouts.footer')
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @if (config('app.recaptcha_site_key'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <script src="{{ asset('assets/js/wow-header.js') }}" defer></script>
    <script src="{{ asset('assets/js/home.js') }}" defer></script>
    <script src="{{ asset('assets/js/character.js') }}?v={{ time() }}" defer></script>
    @auth
    <script src="{{ asset('assets/js/vote-notification.js') }}" defer></script>
    @endauth
    
    @if(session('vote_notification'))
    <div data-vote-notification 
         data-notification-type="{{ session('vote_notification.type') }}"
         data-notification-message="{{ session('vote_notification.message') }}"
         data-notification-points="{{ session('vote_notification.points') ?? 0 }}"
         style="display: none;">
    </div>
    @endif
    
    @stack('scripts')
</body>
</html>