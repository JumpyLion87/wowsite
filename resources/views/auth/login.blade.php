@extends('layouts.app')

@section('content')
<div class="login-container">
    <div class="card login-card shadow-sm mx-auto mt-5">
        <div class="card-body">
            <h3 class="text-center mb-4 login-title">Login</h3>
            
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" 
                           class="form-control form-control-lg @error('username') is-invalid @enderror" 
                           required value="{{ old('username') }}">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
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
                        <span>Login</span>
                        <i class="bi bi-box-arrow-in-right ms-2"></i>
                    </span>
                </button>
            </form>
        </div>
        
        <div class="card-footer text-center py-3">
            <small class="text-muted">
                Don't have an account? <a href="{{ route('register') }}">Register</a>
            </small>
        </div>
    </div>
</div>
@endsection
