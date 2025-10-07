@extends('layouts.app')

@section('content')
<div class="login-container">
    <div class="card login-card shadow-sm mx-auto mt-5">
        <div class="card-body">
            <h3 class="text-center mb-4 login-title">{{ __('auth.login_title') }}</h3>
            
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="username" class="form-label">{{ __('auth.username') }}</label>
                    <input type="text" name="username" id="username" 
                           class="form-control form-control-lg @error('username') is-invalid @enderror" 
                           required value="{{ old('username') }}">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('auth.password') }}</label>
                    <input type="password" name="password" id="password" 
                           class="form-control form-control-lg @error('password') is-invalid @enderror" 
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="g-recaptcha mb-3" 
                     data-sitekey="{{ config('app.recaptcha_site_key') }}">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <span class="d-flex align-items-center justify-content-center">
                        <span>{{ __('auth.login_button') }}</span>
                        <i class="bi bi-box-arrow-in-right ms-2"></i>
                    </span>
                </button>
            </form>
            <div class="text-center mt-3">
                <a href="{{ route('password.request') }}">{{ __('auth.forgot_password_title') }}</a>
            </div>
        </div>
        
        <div class="card-footer text-center py-3">
            <small class="text-muted">
                {{ __('auth.register_link') }} <a href="{{ route('register') }}">{{ __('auth.register') }}</a>
            </small>
        </div>
    </div>
</div>

@if (session('show_message'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show success message with animation
    const alert = document.querySelector('.alert-success');
    if (alert) {
        alert.style.display = 'block';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        
        // Animate in
        setTimeout(() => {
            alert.style.transition = 'all 0.5s ease';
            alert.style.opacity = '1';
            alert.style.transform = 'translateY(0)';
        }, 100);
        
        // Auto-hide after 10 seconds
        setTimeout(() => {
            alert.style.transition = 'all 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 10000);
    }
});
</script>
@endif
@endsection
