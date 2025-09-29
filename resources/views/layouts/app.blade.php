<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('home_meta_description') }}">
    <meta name="robots" content="index">
    <title>{{ __('home.home_page_title') }} - {{ config('app.name') }}</title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-home.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/wow-armory.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/how-to-play.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/online-players.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/character.css') }}?v={{ time() }}">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <script src="{{ asset('assets/js/wow-header.js') }}" defer></script>
    <script src="{{ asset('assets/js/home.js') }}" defer></script>
    <script src="{{ asset('assets/js/character.js') }}?v={{ time() }}" defer></script>
    @stack('scripts')
</body>
</html>