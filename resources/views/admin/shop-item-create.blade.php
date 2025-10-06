@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Заголовок -->
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-plus me-3"></i>
                {{ __('admin_shop_item_create.title') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_shop_item_create.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.shop-items') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_shop_item_create.back_to_items') }}
            </a>
        </div>
    </div>

    <!-- Хлебные крошки -->
    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_shop_item_create.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.shop-items') }}">
                    <i class="fas fa-store me-1"></i>
                    {{ __('admin_shop_item_create.shop_items') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-plus me-1"></i>
                {{ __('admin_shop_item_create.create_item') }}
            </li>
        </ol>
    </nav>

    <!-- Форма создания товара -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus me-2"></i>
                {{ __('admin_shop_item_create.create_new_item') }}
            </h3>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('admin.shop-item.store') }}" class="item-form">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag me-1"></i>
                            {{ __('admin_shop_item_create.name') }} *
                        </label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="{{ old('name') }}" required maxlength="100">
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">
                            <i class="fas fa-folder me-1"></i>
                            {{ __('admin_shop_item_create.category') }} *
                        </label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">{{ __('admin_shop_item_create.select_category') }}</option>
                            <option value="Service" {{ old('category') == 'Service' ? 'selected' : '' }}>Service</option>
                            <option value="Mount" {{ old('category') == 'Mount' ? 'selected' : '' }}>Mount</option>
                            <option value="Pet" {{ old('category') == 'Pet' ? 'selected' : '' }}>Pet</option>
                            <option value="Item" {{ old('category') == 'Item' ? 'selected' : '' }}>Item</option>
                            <option value="Gold" {{ old('category') == 'Gold' ? 'selected' : '' }}>Gold</option>
                        </select>
                        @error('category')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left me-1"></i>
                        {{ __('admin_shop_item_create.description') }}
                    </label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="point_cost" class="form-label">
                            <i class="fas fa-coins me-1"></i>
                            {{ __('admin_shop_item_create.point_cost') }} *
                        </label>
                        <input type="number" id="point_cost" name="point_cost" class="form-control" 
                               value="{{ old('point_cost', 0) }}" min="0" required>
                        @error('point_cost')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="token_cost" class="form-label">
                            <i class="fas fa-gem me-1"></i>
                            {{ __('admin_shop_item_create.token_cost') }} *
                        </label>
                        <input type="number" id="token_cost" name="token_cost" class="form-control" 
                               value="{{ old('token_cost', 0) }}" min="0" required>
                        @error('token_cost')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="stock" class="form-label">
                            <i class="fas fa-boxes me-1"></i>
                            {{ __('admin_shop_item_create.stock') }} *
                        </label>
                        <input type="number" id="stock" name="stock" class="form-control" 
                               value="{{ old('stock', 0) }}" min="0" required>
                        @error('stock')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="entry" class="form-label">
                            <i class="fas fa-key me-1"></i>
                            {{ __('admin_shop_item_create.entry') }}
                        </label>
                        <input type="number" id="entry" name="entry" class="form-control" 
                               value="{{ old('entry') }}" min="0">
                        <small class="form-text">{{ __('admin_shop_item_create.entry_help') }}</small>
                        @error('entry')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="gold_amount" class="form-label">
                            <i class="fas fa-coins me-1"></i>
                            {{ __('admin_shop_item_create.gold_amount') }}
                        </label>
                        <input type="number" id="gold_amount" name="gold_amount" class="form-control" 
                               value="{{ old('gold_amount', 0) }}" min="0">
                        <small class="form-text">{{ __('admin_shop_item_create.gold_amount_help') }}</small>
                        @error('gold_amount')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="level_boost" class="form-label">
                            <i class="fas fa-level-up-alt me-1"></i>
                            {{ __('admin_shop_item_create.level_boost') }}
                        </label>
                        <input type="number" id="level_boost" name="level_boost" class="form-control" 
                               value="{{ old('level_boost') }}" min="1" max="80">
                        <small class="form-text">{{ __('admin_shop_item_create.level_boost_help') }}</small>
                        @error('level_boost')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">
                        <i class="fas fa-image me-1"></i>
                        {{ __('admin_shop_item_create.image') }}
                    </label>
                    <input type="text" id="image" name="image" class="form-control" 
                           value="{{ old('image') }}" maxlength="255">
                    <small class="form-text">{{ __('admin_shop_item_create.image_help') }}</small>
                    @error('image')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>
                        {{ __('admin_shop_item_create.create_item') }}
                    </button>
                    <a href="{{ route('admin.shop-items') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        {{ __('admin_shop_item_create.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.item-form {
    max-width: 800px;
    margin: 0 auto;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #ecf0f1;
}

.form-control, .form-select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ecf0f1;
    padding: 0.75rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: #ffd700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
    color: #fff;
}

.form-control::placeholder {
    color: rgba(236, 240, 241, 0.6);
}

.form-text {
    color: #bdc3c7;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.form-error {
    color: #e74c3c;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
@endpush
