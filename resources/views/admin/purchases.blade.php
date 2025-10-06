@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-shopping-cart me-3"></i>
                {{ __('admin_purchases.title') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_purchases.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary me-2">
                <i class="fas fa-tachometer-alt me-2"></i>
                {{ __('admin_purchases.dashboard') }}
            </a>
        </div>
    </div>

    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_purchases.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-shopping-cart me-1"></i>
                {{ __('admin_purchases.title') }}
            </li>
        </ol>
    </nav>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list me-2"></i>
                {{ __('admin_purchases.list') }}
            </h3>
        </div>
        <div class="card-body">
            <form class="search-form" method="GET" action="{{ route('admin.purchases') }}">
                <div class="search-row">
                    <div class="search-group">
                        <input type="text" name="search" class="form-control"
                               placeholder="{{ __('admin_purchases.search_placeholder') }}"
                               value="{{ $search }}">
                    </div>
                    <div class="search-group">
                        <select name="per_page" class="form-select">
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search me-1"></i>
                            {{ __('admin_purchases.search') }}
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('admin_purchases.user') }}</th>
                            <th>{{ __('admin_purchases.item') }}</th>
                            <th>{{ __('admin_purchases.points') }}</th>
                            <th>{{ __('admin_purchases.tokens') }}</th>
                            <th>{{ __('admin_purchases.date') }}</th>
                            <th>{{ __('admin_purchases.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $p)
                            <tr>
                                <td class="id-cell">{{ $p->purchase_id }}</td>
                                <td class="name-cell">
                                    <div class="user-info">
                                        <span class="username">{{ $p->username }}</span>
                                        <small class="text-muted">{{ $p->email }}</small>
                                    </div>
                                </td>
                                <td>{{ $p->item_name }}</td>
                                <td class="points-cell">{{ number_format($p->point_cost) }}</td>
                                <td class="tokens-cell">{{ number_format($p->token_cost) }}</td>
                                <td class="date-cell">{{ \Carbon\Carbon::parse($p->purchase_date)->format('M j, Y H:i') }}</td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.purchase.details', $p->purchase_id) }}" class="btn btn-sm btn-primary" title="{{ __('admin_purchases.details') }}">
                                            <i class="fas fa-eye"></i>
                                            <span class="btn-text">{{ __('admin_purchases.details') }}</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.purchase.refund', $p->purchase_id) }}" style="display:inline" onsubmit="return confirm('{{ __('admin_purchases.refund_confirm') }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin_purchases.refund') }}">
                                                <i class="fas fa-undo"></i>
                                                <span class="btn-text">{{ __('admin_purchases.refund') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center no-data">
                                    <i class="fas fa-shopping-bag fa-2x mb-2"></i>
                                    <p>{{ __('admin_purchases.no_purchases') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchases->hasPages())
                {{ $purchases->appends(request()->query())->links('pagination.admin-pagination') }}
            @endif
        </div>
    </div>
</div>
@endsection


