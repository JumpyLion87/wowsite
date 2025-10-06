@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Заголовок -->
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-store me-3"></i>
                {{ __('admin_shop_items.title') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_shop_items.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary me-2">
                <i class="fas fa-tachometer-alt me-2"></i>
                {{ __('admin_shop_items.dashboard') }}
            </a>
            <a href="{{ route('admin.shop-item.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>
                {{ __('admin_shop_items.create_item') }}
            </a>
        </div>
    </div>

    <!-- Хлебные крошки -->
    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_shop_items.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-store me-1"></i>
                {{ __('admin_shop_items.title') }}
            </li>
        </ol>
    </nav>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['total_items'] }}</h3>
                <p class="stat-label">{{ __('admin_shop_items.total_items') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['total_categories'] }}</h3>
                <p class="stat-label">{{ __('admin_shop_items.total_categories') }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $stats['total_purchases'] }}</h3>
                <p class="stat-label">{{ __('admin_shop_items.total_purchases') }}</p>
            </div>
        </div>
        
        
    </div>

    <!-- Основной контент -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list me-2"></i>
                {{ __('admin_shop_items.items_list') }}
            </h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-refresh" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Форма поиска и фильтрации -->
            <form class="search-form" method="GET" action="{{ route('admin.shop-items') }}">
                <div class="search-row">
                    <div class="search-group">
                        <input type="text" name="search_name" class="form-control" 
                               placeholder="{{ __('admin_shop_items.search_name') }}" 
                               value="{{ $searchName }}">
                    </div>
                    <div class="search-group">
                        <select name="category_filter" class="form-select">
                            <option value="">{{ __('admin_shop_items.all_categories') }}</option>
                            <option value="Service" {{ $categoryFilter == 'Service' ? 'selected' : '' }}>Service</option>
                            <option value="Mount" {{ $categoryFilter == 'Mount' ? 'selected' : '' }}>Mount</option>
                            <option value="Pet" {{ $categoryFilter == 'Pet' ? 'selected' : '' }}>Pet</option>
                            <option value="Item" {{ $categoryFilter == 'Item' ? 'selected' : '' }}>Item</option>
                            <option value="Gold" {{ $categoryFilter == 'Gold' ? 'selected' : '' }}>Gold</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <select name="per_page" class="form-select">
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20 {{ __('admin_shop_items.per_page') }}</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 {{ __('admin_shop_items.per_page') }}</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 {{ __('admin_shop_items.per_page') }}</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search me-1"></i>
                            {{ __('admin_shop_items.search') }}
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Таблица товаров -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('admin_shop_items.table_id') }}</th>
                            <th>{{ __('admin_shop_items.table_name') }}</th>
                            <th>{{ __('admin_shop_items.table_category') }}</th>
                            <th>{{ __('admin_shop_items.table_points') }}</th>
                            <th>{{ __('admin_shop_items.table_tokens') }}</th>
                            <th>{{ __('admin_shop_items.table_stock') }}</th>
                            <th>{{ __('admin_shop_items.table_updated') }}</th>
                            <th>{{ __('admin_shop_items.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="id-cell">{{ $item->item_id }}</td>
                                <td class="name-cell">
                                    <div class="item-info">
                                        <span class="item-name">{{ $item->name }}</span>
                                        @if($item->description)
                                            <small class="item-description">{{ Str::limit($item->description, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="category-cell">
                                    <span class="category-badge category-{{ strtolower($item->category) }}">
                                        {{ $item->category }}
                                    </span>
                                </td>
                                <td class="points-cell">{{ number_format($item->point_cost) }}</td>
                                <td class="tokens-cell">{{ number_format($item->token_cost) }}</td>
                                <td class="stock-cell">
                                    <span class="stock-badge {{ $item->stock > 0 ? 'stock-available' : 'stock-out' }}">
                                        {{ $item->stock > 0 ? $item->stock : __('admin_shop_items.out_of_stock') }}
                                    </span>
                                </td>
                                <td class="date-cell">
                                    {{ $item->last_updated ? \Carbon\Carbon::parse($item->last_updated)->format('M j, Y H:i') : __('admin_shop_items.never') }}
                                </td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.shop-item.edit', $item->item_id) }}" 
                                           class="btn btn-sm btn-primary" title="{{ __('admin_shop_items.edit') }}">
                                            <i class="fas fa-edit"></i>
                                            <span class="btn-text">{{ __('admin_shop_items.edit') }}</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.shop-item.delete', $item->item_id) }}" 
                                              style="display: inline;" 
                                              onsubmit="return confirm('{{ __('admin_shop_items.delete_confirm') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin_shop_items.delete') }}">
                                                <i class="fas fa-trash"></i>
                                                <span class="btn-text">{{ __('admin_shop_items.delete') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center no-data">
                                    <i class="fas fa-box fa-2x mb-2"></i>
                                    <p>{{ __('admin_shop_items.no_items_found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            @if($items->hasPages())
                {{ $items->appends(request()->query())->links('pagination.admin-pagination') }}
            @endif
        </div>
    </div>
</div>
@endsection

