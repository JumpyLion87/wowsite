@extends('layouts.app')

@section('title', __('shop.title'))
@section('description', __('shop.description'))

@section('content')
<div class="shop-container">
    <!-- Header -->
    <div class="shop-header">
        <h1 class="shop-title">{{ __('shop.title') }}</h1>
        <p class="shop-subtitle">{{ __('shop.description') }}</p>
        
        @auth
            <div class="user-balance">
                <div class="balance-item points">
                    <i class="fas fa-coins balance-icon"></i>
                    <span class="balance-label">{{ __('shop.points') }}:</span>
                    <span class="balance-value">{{ number_format($userBalance['points']) }}</span>
                </div>
                <div class="balance-item tokens">
                    <i class="fas fa-gem balance-icon"></i>
                    <span class="balance-label">{{ __('shop.tokens') }}:</span>
                    <span class="balance-value">{{ number_format($userBalance['tokens']) }}</span>
                </div>
            </div>
        @else
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ __('shop.login_required') }}
            </div>
        @endauth
    </div>

    <!-- Status Messages -->
    @if (session('success'))
        <div class="status-message success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="status-message error">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Category Navigation -->
    <nav class="shop-nav">
        @foreach ($categories as $category)
            <button type="button" 
                    class="category-button {{ $selectedCategory === $category ? 'active' : '' }}" 
                    data-category="{{ $category }}"
                    onclick="filterByCategory('{{ $category }}')">
                @if ($category === 'Service')
                    <i class="fas fa-tools"></i>
                @elseif ($category === 'Mount')
                    <i class="fas fa-horse"></i>
                @elseif ($category === 'Pet')
                    <i class="fas fa-paw"></i>
                @elseif ($category === 'Gold')
                    <i class="fas fa-coins"></i>
                @elseif ($category === 'Stuff')
                    <i class="fas fa-box"></i>
                @else
                    <i class="fas fa-th-large"></i>
                @endif
                {{ __('shop.category_' . strtolower($category)) }}
            </button>
        @endforeach
    </nav>

    <!-- Items Grid -->
    @if (empty($items))
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h3 class="text-muted">{{ __('shop.no_items') }}</h3>
        </div>
    @else
        <div class="shop-items" id="shop-items">
            @foreach ($items as $category => $categoryItems)
                @foreach ($categoryItems as $item)
                    <div class="shop-item {{ !$item->isInStock() ? 'out-of-stock' : '' }}" 
                         data-category="{{ $item->category }}"
                         data-item-id="{{ $item->item_id }}">
                        
                        @if ($item->stock !== null)
                            <div class="item-stock">
                                {{ $item->stock > 0 ? $item->stock : __('shop.out_of_stock') }}
                            </div>
                        @endif

                        <div class="item-header">
                            @if ($item->image)
                                <img src="{{ asset('img/' . $item->image) }}" 
                                     alt="{{ $item->name }}" 
                                     class="item-image">
                            @else
                                <div class="item-image d-flex align-items-center justify-content-center bg-dark">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                            
                            <h3 class="item-name">{{ $item->name }}</h3>
                            
                            @if ($item->description)
                                <p class="item-description">{{ $item->description }}</p>
                            @endif
                        </div>

                        <div class="item-cost">
                            @if ($item->point_cost > 0)
                                <div class="cost-item points">
                                    <i class="fas fa-coins"></i>
                                    <span>{{ number_format($item->point_cost) }}</span>
                                </div>
                            @endif
                            
                            @if ($item->token_cost > 0)
                                <div class="cost-item tokens">
                                    <i class="fas fa-gem"></i>
                                    <span>{{ number_format($item->token_cost) }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($item->level_boost)
                            <div class="item-requirements">
                                <div class="requirement-item">
                                    <i class="fas fa-level-up-alt requirement-icon"></i>
                                    <span>{{ __('shop.level_boost') }}: {{ $item->level_boost }}</span>
                                </div>
                            </div>
                        @endif

                        @auth
                            @if ($item->isInStock())
                                <button class="buy-button" 
                                        data-item-id="{{ $item->item_id }}"
                                        data-point-cost="{{ $item->point_cost }}"
                                        data-token-cost="{{ $item->token_cost }}"
                                        data-item-name="{{ $item->name }}">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    {{ __('shop.buy_now') }}
                                </button>
                            @else
                                <button class="buy-button" disabled>
                                    <i class="fas fa-times me-2"></i>
                                    {{ __('shop.out_of_stock') }}
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="buy-button">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                {{ __('shop.login_required') }}
                            </a>
                        @endauth
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif
</div>

<!-- Character Selection Modal -->
@auth
    @if (!empty($characters))
        <div class="modal fade" id="characterModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark border-warning">
                    <div class="modal-header border-warning">
                        <h5 class="modal-title text-warning">
                            <i class="fas fa-user me-2"></i>
                            {{ __('shop.select_character') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="character-selection">
                            <h4>{{ __('shop.character_must_be_offline') }}</h4>
                            <div class="character-list">
                                @foreach ($characters as $character)
                                    <div class="character-option {{ $character->online ? 'online' : '' }}" 
                                         data-guid="{{ $character->guid }}"
                                         data-name="{{ $character->name }}"
                                         data-level="{{ $character->level }}">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('assets/images/races/' . $character->race . '_' . $character->gender . '.jpg') }}" 
                                                 alt="{{ $character->name }}" 
                                                 class="me-2" 
                                                 style="width: 24px; height: 24px; border-radius: 50%;">
                                            <div>
                                                <div class="fw-bold">{{ $character->name }}</div>
                                                <small class="text-muted">Level {{ $character->level }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-warning" id="confirmPurchase" disabled>
                                <i class="fas fa-check me-2"></i>
                                {{ __('shop.confirm_purchase') }}
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">
                                {{ __('shop.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth
@endsection

<script>
console.log('JavaScript is loading...');

// Simple filter function - global scope
function filterByCategory(category) {
    console.log('Filtering by category:', category);
    
    const shopItems = document.querySelectorAll('.shop-item');
    console.log('Found items:', shopItems.length);
    
    shopItems.forEach((item, index) => {
        const itemCategory = item.dataset.category;
        console.log(`Item ${index}: category = ${itemCategory}`);
        
        if (category === 'All' || itemCategory === category) {
            item.style.display = 'block';
            console.log(`Showing item ${index}`);
        } else {
            item.style.display = 'none';
            console.log(`Hiding item ${index}`);
        }
    });
    
    // Update active button
    const categoryButtons = document.querySelectorAll('.category-button');
    categoryButtons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

// Test function availability
console.log('filterByCategory function available:', typeof filterByCategory);
</script>

@section('scripts')
<script>

document.addEventListener('DOMContentLoaded', function() {
    console.log('Shop page loaded, initializing filters...');
    
    // Category filtering with JavaScript
    const categoryButtons = document.querySelectorAll('.category-button');
    const shopItems = document.querySelectorAll('.shop-item');
    
    console.log('Found category buttons:', categoryButtons.length);
    console.log('Found shop items:', shopItems.length);
    
    // Test if elements exist
    if (categoryButtons.length === 0) {
        console.error('No category buttons found!');
        return;
    }
    
    if (shopItems.length === 0) {
        console.error('No shop items found!');
        return;
    }
    
    // Debug: log all categories
    shopItems.forEach((item, index) => {
        console.log(`Item ${index}: category = ${item.dataset.category}`);
    });
    
    categoryButtons.forEach((button, index) => {
        console.log(`Button ${index}: category = ${button.dataset.category}`);
        
        // Test click event
        button.addEventListener('click', function(e) {
            console.log('Button clicked!', this);
            e.preventDefault();
            e.stopPropagation();
            
            const category = this.dataset.category;
            console.log('Category clicked:', category);
            console.log('Total items to filter:', shopItems.length);
            
            // Update active button
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            let visibleCount = 0;
            let hiddenCount = 0;
            
            // Filter items - simple version
            shopItems.forEach((item, index) => {
                const itemCategory = item.dataset.category;
                console.log(`Item ${index}: category = ${itemCategory}, matches = ${category === 'All' || itemCategory === category}`);
                
                if (category === 'All' || itemCategory === category) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                    hiddenCount++;
                }
            });
            
            console.log(`Filter result: ${visibleCount} visible, ${hiddenCount} hidden`);
        });
    });
    
    // Buy button functionality
    const buyButtons = document.querySelectorAll('.buy-button[data-item-id]');
    const characterModal = document.getElementById('characterModal');
    const characterOptions = document.querySelectorAll('.character-option');
    const confirmPurchaseBtn = document.getElementById('confirmPurchase');
    
    let selectedItem = null;
    let selectedCharacter = null;
    
    buyButtons.forEach(button => {
        button.addEventListener('click', function() {
            selectedItem = {
                id: this.dataset.itemId,
                name: this.dataset.itemName,
                pointCost: this.dataset.pointCost,
                tokenCost: this.dataset.tokenCost
            };
            
            if (characterModal) {
                const modal = new bootstrap.Modal(characterModal);
                modal.show();
            } else {
                // No characters available
                alert('{{ __("shop.no_characters") }}');
            }
        });
    });
    
    // Character selection
    characterOptions.forEach(option => {
        option.addEventListener('click', function() {
            if (this.classList.contains('online')) return;
            
            characterOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            selectedCharacter = {
                guid: this.dataset.guid,
                name: this.dataset.name,
                level: this.dataset.level
            };
            
            confirmPurchaseBtn.disabled = false;
        });
    });
    
    // Confirm purchase
    if (confirmPurchaseBtn) {
        confirmPurchaseBtn.addEventListener('click', function() {
            if (!selectedItem || !selectedCharacter) return;
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("shop.buy") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add item ID
            const itemId = document.createElement('input');
            itemId.type = 'hidden';
            itemId.name = 'item_id';
            itemId.value = selectedItem.id;
            form.appendChild(itemId);
            
            // Add character GUID
            const characterGuid = document.createElement('input');
            characterGuid.type = 'hidden';
            characterGuid.name = 'character_guid';
            characterGuid.value = selectedCharacter.guid;
            form.appendChild(characterGuid);
            
            document.body.appendChild(form);
            form.submit();
        });
    }
});
</script>
@endsection
