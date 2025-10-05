@extends('layouts.app')

@section('content')
<div class="activation-container">
    <div class="card activation-card shadow-sm mx-auto mt-5">
        <div class="card-body">
            <h3 class="text-center mb-4 activation-title">{{ __('auth.activation_title') }}</h3>
            
            @if (isset($error))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (isset($success))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ $success }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        {{ __('auth.login') }}
                    </a>
                </div>
            @endif

            @if (isset($token) && !isset($error) && !isset($success))
                <div class="activation-info">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">{{ __('auth.activating') }}</span>
                        </div>
                        <p>{{ __('auth.activation_processing') }}</p>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="card-footer text-center py-3">
            <small class="text-muted">
                {{ __('auth.activation_help') }}
            </small>
        </div>
    </div>
</div>
@endsection
