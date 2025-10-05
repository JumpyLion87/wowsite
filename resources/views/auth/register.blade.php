@extends('layouts.app')

@section('content')
<div class="register-container">
    <div class="card register-card shadow-sm mx-auto mt-5">
        <div class="card-body">
            <h3 class="text-center mb-4 register-title">{{ __('auth.register_title') }}</h3>
            
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="username" class="form-label">{{ __('auth.username') }}</label>
                    <input type="text" name="username" id="username" 
                           class="form-control form-control-lg @error('username') is-invalid @enderror" 
                           required minlength="3" maxlength="16" pattern="[a-zA-Z0-9]+"
                           value="{{ old('username') }}">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('auth.username_help') }}</div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('auth.email') }}</label>
                    <input type="email" name="email" id="email" 
                           class="form-control form-control-lg @error('email') is-invalid @enderror" 
                           required maxlength="64" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('auth.password') }}</label>
                    <input type="password" name="password" id="password" 
                           class="form-control form-control-lg @error('password') is-invalid @enderror" 
                           required minlength="6" maxlength="32">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('auth.password_help') }}</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">{{ __('auth.confirm_password') }}</label>
                    <input type="password" name="confirm_password" id="confirm_password" 
                           class="form-control form-control-lg @error('confirm_password') is-invalid @enderror" 
                           required minlength="6" maxlength="32">
                    @error('confirm_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="g-recaptcha mb-3" 
                     data-sitekey="{{ config('app.recaptcha_site_key') }}">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <span class="d-flex align-items-center justify-content-center">
                        <span>{{ __('auth.register_button') }}</span>
                        <i class="bi bi-person-plus ms-2"></i>
                    </span>
                </button>
            </form>
        </div>
        
        <div class="card-footer text-center py-3">
            <small class="text-muted">
                {{ __('auth.login_link_text') }} <a href="{{ route('login') }}">{{ __('auth.login') }}</a>
            </small>
        </div>
    </div>
</div>
@endsection