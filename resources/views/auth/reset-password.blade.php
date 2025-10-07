@extends('layouts.app')

@section('content')
<div class="login-container">
    <div class="card login-card shadow-sm mx-auto mt-5">
        <div class="card-body">
            <h3 class="text-center mb-4 login-title">{{ __('auth.reset_password_title') }}</h3>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('auth.new_password') }}</label>
                    <input type="password" name="password" id="password" 
                           class="form-control form-control-lg @error('password') is-invalid @enderror" 
                           required minlength="6" maxlength="32">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">{{ __('auth.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="form-control form-control-lg" 
                           required minlength="6" maxlength="32">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <span class="d-flex align-items-center justify-content-center">
                        <span>{{ __('auth.reset_password_button') }}</span>
                        <i class="bi bi-key ms-2"></i>
                    </span>
                </button>
            </form>
        </div>
        
        <div class="card-footer text-center py-3">
            <small class="text-muted">
                <a href="{{ route('login') }}">{{ __('auth.back_to_login') }}</a>
            </small>
        </div>
    </div>
</div>
@endsection


