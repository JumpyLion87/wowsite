@extends('layouts.app')

@section('content')
<div class="login-container">
    <div class="card login-card shadow-sm mx-auto mt-5">
        <div class="card-body">
            <h3 class="text-center mb-4 login-title">{{ __('auth.forgot_password_title') }}</h3>
            
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('auth.email') }}</label>
                    <input type="email" name="email" id="email" 
                           class="form-control form-control-lg @error('email') is-invalid @enderror" 
                           required value="{{ old('email') }}" autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <span class="d-flex align-items-center justify-content-center">
                        <span>{{ __('auth.send_reset_link') }}</span>
                        <i class="bi bi-envelope ms-2"></i>
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


