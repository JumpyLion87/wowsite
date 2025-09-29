<footer class="wow-footer">
    <div class="footer-container">
        <div class="footer-logo-section">
            <div class="footer-logo">
                <img src="{{ asset('img/logo.png') }}" alt="Sahtout Server Logo">
            </div>
            <p class="copyright">© {{ date('Y') }} Sahtout Server. All rights reserved.</p>
        </div>
        
        <div class="footer-links-section">            
            <ul class="footer-links">
                <li><a href="{{ route('home') }}">{{ __('nav.home') }}</a></li>
                <li><a href="{{ route('how-to-play') }}">{{ __('nav.how_to_play') }}</a></li>
                <li><a href="{{ route('news.index') }}">{{ __('nav.news') }}</a></li>
                <li><a href="{{ route('shop') }}">{{ __('nav.shop') }}</a></li>
                <li><a href="{{ route('armory') }}">{{ __('nav.armory') }}</a></li>
            </ul>
        </div>
        
        <div class="footer-social-section">            
            <div class="social-links">
                <a href="#" class="social-link">
                    <i class="fab fa-discord"></i>
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </div>
    </div>
</footer>