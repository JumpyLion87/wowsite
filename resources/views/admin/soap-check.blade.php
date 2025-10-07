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
            
            <div class="remote-server-info">
                <h4>{{ __('admin_soap.remote_server_setup') }}</h4>
                <div class="setup-steps">
                    <div class="step">
                        <strong>1.</strong> {{ __('admin_soap.step_1') }}
                    </div>
                    <div class="step">
                        <strong>2.</strong> {{ __('admin_soap.step_2') }}
                    </div>
                    <div class="step">
                        <strong>3.</strong> {{ __('admin_soap.step_3') }}
                    </div>
                    <div class="step">
                        <strong>4.</strong> {{ __('admin_soap.step_4') }}
                    </div>
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
            } else if (data.status === 'partial' || data.status === 'auth_error') {
                statusDiv.innerHTML = `
                    <div class="status-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>{{ __('admin_soap.connection_partial') }}</h4>
                        <p>${data.message}</p>
                        ${data.note ? `<p class="note"><strong>Примечание:</strong> ${data.note}</p>` : ''}
                        <div class="response-details">
                            <strong>{{ __('admin_soap.server_response') }}:</strong>
                            <pre>${data.response || 'Нет ответа'}</pre>
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
                        <i class="fas fa-times-circle"></i>
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

