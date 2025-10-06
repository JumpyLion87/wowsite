@extends('layouts.app')

@section('title', __('shop.purchase_history'))
@section('description', __('shop.purchase_history'))

@section('content')
<div class="shop-container">
    <!-- Header -->
    <div class="shop-header">
        <h1 class="shop-title">
            <i class="fas fa-history me-3"></i>
            {{ __('shop.purchase_history') }}
        </h1>
        <p class="shop-subtitle">{{ __('shop.purchase_history') }}</p>
        
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('shop.index') }}" class="btn btn-warning">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('shop.back_to_shop') }}
            </a>
        </div>
    </div>

    <!-- Purchase History -->
    <div class="purchase-history">
        @if ($purchases->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                <h3 class="text-muted">{{ __('shop.no_purchases') }}</h3>
                <a href="{{ route('shop.index') }}" class="btn btn-warning mt-3">
                    <i class="fas fa-shopping-cart me-2"></i>
                    {{ __('shop.back_to_shop') }}
                </a>
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar me-2"></i>{{ __('shop.purchase_date') }}</th>
                                    <th><i class="fas fa-box me-2"></i>{{ __('shop.item_name') }}</th>
                                    <th><i class="fas fa-coins me-2"></i>{{ __('shop.cost_paid') }}</th>
                                    <th><i class="fas fa-user me-2"></i>{{ __('shop.character_name') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchases as $purchase)
                                    <tr>
                                        <td>
                                            <span class="text-warning">
                                                {{ $purchase->purchase_date->format('d.m.Y H:i') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($purchase->shopItem && $purchase->shopItem->image)
                                                    <img src="{{ asset('img/' . $purchase->shopItem->image) }}" 
                                                         alt="{{ $purchase->shopItem->name }}" 
                                                         class="me-2" 
                                                         style="width: 32px; height: 32px; border-radius: 4px;">
                                                @endif
                                                <div>
                                                    <div class="fw-bold text-warning">
                                                        {{ $purchase->shopItem ? $purchase->shopItem->name : 'Unknown Item' }}
                                                    </div>
                                                    @if ($purchase->shopItem && $purchase->shopItem->description)
                                                        <small class="text-muted">{{ Str::limit($purchase->shopItem->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                @if ($purchase->point_cost > 0)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-coins me-1"></i>
                                                        {{ number_format($purchase->point_cost) }} {{ __('shop.points') }}
                                                    </span>
                                                @endif
                                                @if ($purchase->token_cost > 0)
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-gem me-1"></i>
                                                        {{ number_format($purchase->token_cost) }} {{ __('shop.tokens') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-info">
                                                {{ $purchase->character_name ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            @if ($purchases->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $purchases->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endsection
