@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Заголовок -->
    <div class="admin-header">
        <div class="admin-title-section">
            <h1 class="admin-title">
                <i class="fas fa-edit me-3"></i>
                {{ __('admin_shop_item_edit.title') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_shop_item_edit.subtitle') }}</p>
        </div>
        <div class="admin-actions">
            <a href="{{ route('admin.shop-items') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_shop_item_edit.back_to_items') }}
            </a>
        </div>
    </div>

    <!-- Хлебные крошки -->
    <nav class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    {{ __('admin_shop_item_edit.dashboard') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.shop-items') }}">
                    <i class="fas fa-store me-1"></i>
                    {{ __('admin_shop_item_edit.shop_items') }}
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-edit me-1"></i>
                {{ __('admin_shop_item_edit.edit_item') }} #{{ $item->item_id }}
            </li>
        </ol>
    </nav>

    <!-- Форма редактирования товара -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit me-2"></i>
                {{ __('admin_shop_item_edit.edit_item') }}: {{ $item->name }}
            </h3>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('admin.shop-item.update', $item->item_id) }}" class="item-form">
                @csrf
                @method('PUT')
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag me-1"></i>
                            {{ __('admin_shop_item_edit.name') }} *
                        </label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="{{ old('name', $item->name) }}" required maxlength="100">
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">
                            <i class="fas fa-folder me-1"></i>
                            {{ __('admin_shop_item_edit.category') }} *
                        </label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">{{ __('admin_shop_item_edit.select_category') }}</option>
                            <option value="Service" {{ old('category', $item->category) == 'Service' ? 'selected' : '' }}>Service</option>
                            <option value="Mount" {{ old('category', $item->category) == 'Mount' ? 'selected' : '' }}>Mount</option>
                            <option value="Pet" {{ old('category', $item->category) == 'Pet' ? 'selected' : '' }}>Pet</option>
                            <option value="Item" {{ old('category', $item->category) == 'Item' ? 'selected' : '' }}>Item</option>
                            <option value="Gold" {{ old('category', $item->category) == 'Gold' ? 'selected' : '' }}>Gold</option>
                        </select>
                        @error('category')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left me-1"></i>
                        {{ __('admin_shop_item_edit.description') }}
                    </label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="point_cost" class="form-label">
                            <i class="fas fa-coins me-1"></i>
                            {{ __('admin_shop_item_edit.point_cost') }} *
                        </label>
                        <input type="number" id="point_cost" name="point_cost" class="form-control" 
                               value="{{ old('point_cost', $item->point_cost) }}" min="0" required>
                        @error('point_cost')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="token_cost" class="form-label">
                            <i class="fas fa-gem me-1"></i>
                            {{ __('admin_shop_item_edit.token_cost') }} *
                        </label>
                        <input type="number" id="token_cost" name="token_cost" class="form-control" 
                               value="{{ old('token_cost', $item->token_cost) }}" min="0" required>
                        @error('token_cost')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="stock" class="form-label">
                            <i class="fas fa-boxes me-1"></i>
                            {{ __('admin_shop_item_edit.stock') }} *
                        </label>
                        <input type="number" id="stock" name="stock" class="form-control" 
                               value="{{ old('stock', $item->stock) }}" min="0" required>
                        @error('stock')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="entry" class="form-label">
                            <i class="fas fa-key me-1"></i>
                            {{ __('admin_shop_item_edit.entry') }}
                        </label>
                        <input type="number" id="entry" name="entry" class="form-control" 
                               value="{{ old('entry', $item->entry) }}" min="0">
                        <small class="form-text">{{ __('admin_shop_item_edit.entry_help') }}</small>
                        @error('entry')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="gold_amount" class="form-label">
                            <i class="fas fa-coins me-1"></i>
                            {{ __('admin_shop_item_edit.gold_amount') }}
                        </label>
                        <input type="number" id="gold_amount" name="gold_amount" class="form-control" 
                               value="{{ old('gold_amount', $item->gold_amount) }}" min="0">
                        <small class="form-text">{{ __('admin_shop_item_edit.gold_amount_help') }}</small>
                        @error('gold_amount')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="level_boost" class="form-label">
                            <i class="fas fa-level-up-alt me-1"></i>
                            {{ __('admin_shop_item_edit.level_boost') }}
                        </label>
                        <input type="number" id="level_boost" name="level_boost" class="form-control" 
                               value="{{ old('level_boost', $item->level_boost) }}" min="1" max="80">
                        <small class="form-text">{{ __('admin_shop_item_edit.level_boost_help') }}</small>
                        @error('level_boost')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">
                        <i class="fas fa-image me-1"></i>
                        {{ __('admin_shop_item_edit.image') }}
                    </label>
                    <input type="text" id="image" name="image" class="form-control" 
                           value="{{ old('image', $item->image) }}" maxlength="255">
                    <small class="form-text">{{ __('admin_shop_item_edit.image_help') }}</small>
                    @error('image')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>
                        {{ __('admin_shop_item_edit.update_item') }}
                    </button>
                    <a href="{{ route('admin.shop-items') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        {{ __('admin_shop_item_edit.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

