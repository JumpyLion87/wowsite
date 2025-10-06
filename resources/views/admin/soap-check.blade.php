@extends('layouts.app')

@section('title', __('admin_soap.title'))

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-plug me-3"></i>
                {{ __('admin_soap.title') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_soap.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_soap.back_to_dashboard') }}
            </a>
        </div>
    </div>

    <div class="soap-status-container">
        <div class="soap-status-card">
            <div class="soap-status-header">
                <h3>{{ __('admin_soap.connection_status') }}</h3>
                <button class="btn btn-primary" onclick="checkSoapConnection()">
                    <i class="fas fa-sync-alt me-2"></i>
                    {{ __('admin_soap.check_connection') }}
                </button>
            </div>
            
            <div id="soap-status" class="soap-status-content">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    {{ __('admin_soap.checking') }}
                </div>
            </div>
        </div>

        <div class="soap-config-card">
            <h3>{{ __('admin_soap.configuration') }}</h3>
            <div class="config-info">
                <div class="config-item">
                    <label>{{ __('admin_soap.soap_url') }}:</label>
                    <span class="config-value">{{ config('wow.soap_url', 'http://localhost:7878') }}</span>
                </div>
                <div class="config-item">
                    <label>{{ __('admin_soap.soap_username') }}:</label>
                    <span class="config-value">{{ config('wow.soap_username', 'admin') }}</span>
                </div>
                <div class="config-item">
                    <label>{{ __('admin_soap.soap_password') }}:</label>
                    <span class="config-value">{{ str_repeat('*', strlen(config('wow.soap_password', 'admin'))) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkSoapConnection() {
    const statusDiv = document.getElementById('soap-status');
    statusDiv.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> {{ __("admin_soap.checking") }}</div>';
    
    fetch('{{ route("admin.soap.check") }}')
        .then(response => response.json())
        .then(data => {
                if (data.status === 'success') {
                statusDiv.innerHTML = `
                    <div class="status-success">
                        <i class="fas fa-check-circle"></i>
                        <h4>{{ __('admin_soap.connection_success') }}</h4>
                        <p>${data.message}</p>
                        <div class="response-details">
                            <strong>{{ __('admin_soap.server_response') }}:</strong>
                            <pre>${JSON.stringify(data.response, null, 2)}</pre>
                        </div>
                        ${data.diagnostics ? `
                        <div class="response-details">
                            <strong>{{ __('admin_soap.diagnostics') }}:</strong>
                            <pre>${JSON.stringify(data.diagnostics, null, 2)}</pre>
                        </div>
                        ` : ''}
                    </div>
                `;
            } else {
                statusDiv.innerHTML = `
                    <div class="status-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>{{ __('admin_soap.connection_error') }}</h4>
                        <p>${data.message}</p>
                        ${data.diagnostics ? `
                        <div class="response-details">
                            <strong>{{ __('admin_soap.diagnostics') }}:</strong>
                            <pre>${JSON.stringify(data.diagnostics, null, 2)}</pre>
                        </div>
                        ` : ''}
                    </div>
                `;
            }
        })
        .catch(error => {
            statusDiv.innerHTML = `
                <div class="status-error">
                    <i class="fas fa-times-circle"></i>
                    <h4>{{ __('admin_soap.connection_failed') }}</h4>
                    <p>${error.message}</p>
                </div>
            `;
        });
}

// Автоматическая проверка при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    checkSoapConnection();
});
</script>
@endsection

@push('styles')
<style>
.soap-status-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.soap-status-card,
.soap-config-card {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    border: 1px solid #4a5f7a;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.soap-status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #4a5f7a;
}

.soap-status-header h3 {
    color: #ecf0f1;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.soap-status-content {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-spinner {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #bdc3c7;
    font-size: 1.1rem;
}

.status-success {
    text-align: center;
    color: #27ae60;
}

.status-success i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.status-error {
    text-align: center;
    color: #e74c3c;
}

.status-error i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.response-details {
    margin-top: 1rem;
    text-align: left;
    background: rgba(0, 0, 0, 0.3);
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #4a5f7a;
}

.response-details pre {
    color: #ecf0f1;
    font-size: 0.9rem;
    margin: 0;
    white-space: pre-wrap;
    word-break: break-all;
}

.config-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.config-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 6px;
    border: 1px solid #4a5f7a;
}

.config-item label {
    color: #bdc3c7;
    font-weight: 500;
    margin: 0;
}

.config-value {
    color: #ecf0f1;
    font-family: 'Courier New', monospace;
    background: rgba(0, 0, 0, 0.3);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    border: 1px solid #4a5f7a;
}

@media (max-width: 768px) {
    .soap-status-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .soap-status-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .config-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>
@endpush
