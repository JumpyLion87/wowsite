@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-receipt me-3"></i>
                {{ __('admin_purchase_details.title') }} #{{ $purchase->purchase_id }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_purchase_details.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.purchases') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_purchase_details.back_to_list') }}
            </a>
        </div>
    </div>

    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_purchase_details.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.purchases') }}">
                    <i class="fas fa-shopping-cart me-1"></i>
                    {{ __('admin_purchase_details.purchases') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-receipt me-1"></i>
                #{{ $purchase->purchase_id }}
            </li>
        </ol>
    </nav>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle me-2"></i>
                {{ __('admin_purchase_details.info') }}
            </h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>{{ __('admin_purchase_details.user') }}:</label>
                    <span class="info-value">{{ $purchase->username }} ({{ $purchase->email }})</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_purchase_details.item') }}:</label>
                    <span class="info-value">{{ $purchase->item_name }} ({{ $purchase->category }})</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_purchase_details.points') }}:</label>
                    <span class="info-value">{{ number_format($purchase->point_cost) }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_purchase_details.tokens') }}:</label>
                    <span class="info-value">{{ number_format($purchase->token_cost) }}</span>
                </div>
                <div class="info-item">
                    <label>{{ __('admin_purchase_details.date') }}:</label>
                    <span class="info-value">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M j, Y H:i') }}</span>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 1.5rem;">
                <form method="POST" action="{{ route('admin.purchase.refund', $purchase->purchase_id) }}" onsubmit="return confirm('{{ __('admin_purchase_details.refund_confirm') }}')">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo me-2"></i>
                        {{ __('admin_purchase_details.refund') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


