<header class="wow-header">
    <div class="header-container">
        <a class="logo" href="{{ route('home') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Sahtout Server Logo" height="60">
        </a>
        
        <button class="nav-toggle" id="navToggle">
            <span class="hamburger"></span>
        </button>
        
        <nav class="wow-nav" id="mainNav">
            <button class="nav-close" id="navClose">✕</button>
            
            <ul class="nav-menu">
                <li>
                    <a class="{{ request()->is('/') ? 'active' : '' }}" href="{{ route('home') }}">
                        {{ __('nav.home') }}
                    </a>
                </li>
                <li>
                    <a class="{{ request()->is('how-to-play') ? 'active' : '' }}" href="{{ route('how-to-play') }}">
                        {{ __('nav.how_to_play') }}
                    </a>
                </li>
                <li>
                    <a class="{{ request()->is('news*') ? 'active' : '' }}" href="{{ route('news.index') }}">
                        {{ __('nav.news') }}
                    </a>
                </li>
                <li>
                    <a class="{{ request()->is('armory*') ? 'active' : '' }}" href="{{ route('armory') }}">
                        {{ __('nav.armory') }}
                    </a>
                </li>
                <li>
                    <a class="{{ request()->is('shop*') ? 'active' : '' }}" href="{{ route('shop.index') }}">
                        {{ __('nav.shop') }}
                    </a>
                </li>
                <li>
                    <a class="{{ request()->is('online-players') ? 'active' : '' }}" href="{{ route('online-players') }}">
                        {{ __('nav.online_players') }}
                    </a>
                </li>
            </ul>
            
            <ul class="nav-auth">
                @auth
                    <li class="user-currency">
                        <span class="points">
                            <i class="fas fa-coins"></i> {{ \App\Models\User::getPoints(auth()->id()) }}
                        </span>
                        <span class="tokens">
                            <i class="fas fa-gem"></i> {{ \App\Models\User::getTokens(auth()->id()) }}
                        </span>
                    </li>
                    <li class="profile-dropdown">
                        <img src="{{ asset('img/accountimg/profile_pics/' . \App\Models\User::getAvatar(auth()->id())) }}" 
                             alt="User Profile" class="user-image" id="profileToggle">
                        <div class="dropdown-menu" id="dropdownMenu">
                            <div class="dropdown-header">
                                <img src="{{ asset('img/accountimg/profile_pics/' . \App\Models\User::getAvatar(auth()->id())) }}" 
                                     alt="User Profile" class="dropdown-image">
                                <div class="user-info">
                                    <span class="username">{{ auth()->user()->name }}</span>
                                    <span class="email">{{ \App\Models\User::getAuthEmail(auth()->id()) }}</span>
                                    <div class="dropdown-currency">
                                        <span class="points">
                                            <i class="fas fa-coins"></i> {{ __('nav.points') }}: {{ \App\Models\User::getPoints(auth()->id()) }}
                                        </span>
                                        <span class="tokens">
                                            <i class="fas fa-gem"></i> {{ __('nav.tokens') }}: {{ \App\Models\User::getTokens(auth()->id()) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('account') }}" class="dropdown-item">
                                <i class="fas fa-user-circle"></i> {{ __('nav.account_settings') }}
                            </a>
                            @if(\App\Models\User::isAdmin(auth()->id()))
                                @if (Route::has('admin.dashboard'))
                                    <a href="{{ route('admin.dashboard') }}" class="dropdown-item admin-panel">
                                        <i class="fas fa-cogs"></i> {{ __('nav.admin_panel') }}
                                    </a>
                                @endif
                            @endif
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" class="dropdown-item-form">
                                @csrf
                                <button type="submit" class="dropdown-item logout">
                                    <i class="fas fa-sign-out-alt"></i> {{ __('nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </li>
                @else
                    <li>
                        <a class="{{ request()->is('login') ? 'active' : '' }}" href="{{ route('login') }}">
                            {{ __('nav.login') }}
                        </a>
                    </li>
                    <li>
                        <a class="{{ request()->is('register') ? 'active' : '' }}" href="{{ route('register') }}">
                            {{ __('nav.register') }}
                        </a>
                    </li>
                @endauth
            </ul>

            <!-- Language Switcher -->
            <div class="lang-dropdown">
                <div class="lang-selected" id="langSelected">
                    <img src="{{ asset('languages/flags/' . app()->getLocale() . '.svg') }}" 
                         alt="{{ __('languages.' . app()->getLocale()) }}" id="flagIcon">
                    <span id="langLabel">{{ __('languages.' . app()->getLocale()) }}</span>
                </div>
                <ul class="lang-options" id="langOptions">
                    @foreach(['en', 'ru'] as $lang)
                        <li data-value="{{ $lang }}" data-flag="{{ asset('languages/flags/' . $lang . '.svg') }}">
                            <img src="{{ asset('languages/flags/' . $lang . '.svg') }}" 
                                 alt="{{ __('languages.' . $lang) }}"> 
                            {{ __('languages.' . $lang) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>
    </div>
</header>

<script>
    // Mobile menu toggle
    const toggleButton = document.getElementById('navToggle');
    const closeButton = document.getElementById('navClose');
    const nav = document.getElementById('mainNav');

    if (toggleButton && closeButton && nav) {
        toggleButton.addEventListener('click', () => {
            nav.classList.toggle('nav-open');
        });

        closeButton.addEventListener('click', () => {
            nav.classList.remove('nav-open');
        });
    }

    // Profile dropdown toggle
    const profileToggle = document.getElementById('profileToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');

    if (profileToggle && dropdownMenu) {
        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
            document.getElementById('langOptions')?.classList.remove('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.profile-dropdown')) {
                dropdownMenu.classList.remove('show');
            }
        });
    }

    // Language dropdown toggle
    const langToggle = document.getElementById('langSelected');
    const langOptions = document.getElementById('langOptions');

    if (langToggle && langOptions) {
        langToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            langOptions.classList.toggle('show');
            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
            }
        });

        // Close language dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.lang-dropdown')) {
                langOptions.classList.remove('show');
            }
        });

        // Language selection
        document.querySelectorAll('.lang-options li').forEach(option => {
            option.addEventListener('click', function (e) {
                e.stopPropagation();
                const lang = this.getAttribute('data-value');
                
                // Redirect to language switch route
                window.location.href = "/language/" + lang;
            });
        });
    }
</script>