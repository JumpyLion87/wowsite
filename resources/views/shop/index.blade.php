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
                                        data-item-category="{{ $item->category }}"
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
                                            @php
                                                $raceNames = [
                                                    1 => 'human',
                                                    2 => 'orc', 
                                                    3 => 'dwarf',
                                                    4 => 'nightelf',
                                                    5 => 'undead',
                                                    6 => 'tauren',
                                                    7 => 'gnome',
                                                    8 => 'troll',
                                                    10 => 'bloodelf',
                                                    11 => 'draenei'
                                                ];
                                                $raceName = $raceNames[$character->race] ?? 'human';
                                                $gender = $character->gender == 0 ? 'male' : 'female';
                                            @endphp
                                            <img src="{{ asset('img/accountimg/race/' . $gender . '/' . $raceName . '.png') }}" 
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
                        
                        <!-- Service Options Form (hidden by default) -->
                        <div id="serviceOptions" class="mt-3" style="display: none;">
                            <h5 class="text-warning">{{ __('shop.service_options') }}</h5>
                            
                            <!-- Name Change -->
                            <div id="nameChangeOption" class="mb-3" style="display: none;">
                                <label for="serviceName" class="form-label text-light">{{ __('shop.new_name') }}</label>
                                <input type="text" class="form-control" id="serviceName" name="service_name" 
                                       placeholder="{{ __('shop.enter_new_name') }}" maxlength="100">
                            </div>
                            
                            <!-- Race Change -->
                            <div id="raceChangeOption" class="mb-3" style="display: none;">
                                <label for="serviceRace" class="form-label text-light">{{ __('shop.new_race') }}</label>
                                <select class="form-select" id="serviceRace" name="service_race">
                                    <option value="">{{ __('shop.select_race') }}</option>
                                    <option value="1">{{ __('shop.race_human') }}</option>
                                    <option value="2">{{ __('shop.race_orc') }}</option>
                                    <option value="3">{{ __('shop.race_dwarf') }}</option>
                                    <option value="4">{{ __('shop.race_nightelf') }}</option>
                                    <option value="5">{{ __('shop.race_undead') }}</option>
                                    <option value="6">{{ __('shop.race_tauren') }}</option>
                                    <option value="7">{{ __('shop.race_gnome') }}</option>
                                    <option value="8">{{ __('shop.race_troll') }}</option>
                                    <option value="10">{{ __('shop.race_bloodelf') }}</option>
                                    <option value="11">{{ __('shop.race_draenei') }}</option>
                                </select>
                            </div>
                            
                            <!-- Gender Change -->
                            <div id="genderChangeOption" class="mb-3" style="display: none;">
                                <label for="serviceGender" class="form-label text-light">{{ __('shop.new_gender') }}</label>
                                <select class="form-select" id="serviceGender" name="service_gender">
                                    <option value="">{{ __('shop.select_gender') }}</option>
                                    <option value="0">{{ __('shop.gender_male') }}</option>
                                    <option value="1">{{ __('shop.gender_female') }}</option>
                                </select>
                            </div>
                            
                            <!-- Faction Change -->
                            <div id="factionChangeOption" class="mb-3" style="display: none;">
                                <label for="serviceFaction" class="form-label text-light">{{ __('shop.new_faction') }}</label>
                                <select class="form-select" id="serviceFaction" name="service_faction">
                                    <option value="">{{ __('shop.select_faction') }}</option>
                                    <option value="1">{{ __('shop.faction_alliance') }}</option>
                                    <option value="2">{{ __('shop.faction_horde') }}</option>
                                </select>
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

<!-- Hidden Purchase Form -->
<form id="purchaseForm" method="POST" action="{{ route('shop.buy') }}" style="display: none;">
    @csrf
    <input type="hidden" name="item_id" id="purchaseItemId">
    <input type="hidden" name="character_guid" id="purchaseCharacterGuid">
    <input type="hidden" name="service_name" id="purchaseServiceName">
    <input type="hidden" name="service_race" id="purchaseServiceRace">
    <input type="hidden" name="service_gender" id="purchaseServiceGender">
    <input type="hidden" name="service_faction" id="purchaseServiceFaction">
</form>
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

// Buy button functionality - global scope
function initializeBuyButtons() {
    console.log('Initializing buy buttons...');
    
    const buyButtons = document.querySelectorAll('.buy-button[data-item-id]');
    const characterModal = document.getElementById('characterModal');
    const characterOptions = document.querySelectorAll('.character-option');
    const confirmPurchaseBtn = document.getElementById('confirmPurchase');
    
    console.log('Buy buttons found:', buyButtons.length);
    console.log('Character modal found:', characterModal ? 'Yes' : 'No');
    console.log('Character options found:', characterOptions.length);
    console.log('Confirm purchase button found:', confirmPurchaseBtn ? 'Yes' : 'No');
    
    let selectedItem = null;
    let selectedCharacter = null;
    
    buyButtons.forEach((button, index) => {
        console.log(`Buy button ${index}:`, button);
        button.addEventListener('click', function() {
            console.log('Buy button clicked!', this);
            selectedItem = {
                id: this.dataset.itemId,
                name: this.dataset.itemName,
                pointCost: this.dataset.pointCost,
                tokenCost: this.dataset.tokenCost
            };
            console.log('Selected item:', selectedItem);
            
            if (characterModal) {
                console.log('Opening character modal...');
                
                // Check if this is a service item
                const itemCategory = this.dataset.itemCategory;
                const isService = itemCategory === 'Service';
                
                // Show/hide service options based on item type
                const serviceOptions = document.getElementById('serviceOptions');
                const nameChangeOption = document.getElementById('nameChangeOption');
                const raceChangeOption = document.getElementById('raceChangeOption');
                const genderChangeOption = document.getElementById('genderChangeOption');
                const factionChangeOption = document.getElementById('factionChangeOption');
                
                if (isService) {
                    // Show service options
                    if (serviceOptions) serviceOptions.style.display = 'block';
                    
                    // Show specific options based on item name
                    const itemName = this.dataset.itemName.toLowerCase();
                    if (itemName.includes('name') || itemName.includes('rename')) {
                        if (nameChangeOption) nameChangeOption.style.display = 'block';
                    }
                    if (itemName.includes('race')) {
                        if (raceChangeOption) raceChangeOption.style.display = 'block';
                    }
                    if (itemName.includes('gender')) {
                        if (genderChangeOption) genderChangeOption.style.display = 'block';
                    }
                    if (itemName.includes('faction')) {
                        if (factionChangeOption) factionChangeOption.style.display = 'block';
                    }
                } else {
                    // Hide service options for non-service items
                    if (serviceOptions) serviceOptions.style.display = 'none';
                    if (nameChangeOption) nameChangeOption.style.display = 'none';
                    if (raceChangeOption) raceChangeOption.style.display = 'none';
                    if (genderChangeOption) genderChangeOption.style.display = 'none';
                    if (factionChangeOption) factionChangeOption.style.display = 'none';
                }
                
                console.log('Bootstrap available:', typeof bootstrap);
                if (typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(characterModal);
                    modal.show();
                } else {
                    console.log('Bootstrap not available, using jQuery modal');
                    $(characterModal).modal('show');
                }
            } else {
                console.log('No character modal found');
                alert('{{ __("shop.no_characters") }}');
            }
        });
    });
    
    // Character selection
    characterOptions.forEach((option, index) => {
        console.log(`Character option ${index}:`, option);
        option.addEventListener('click', function() {
            console.log('Character option clicked:', this);
            if (this.classList.contains('online')) {
                console.log('Character is online, cannot select');
                return;
            }
            
            characterOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            selectedCharacter = {
                guid: this.dataset.guid,
                name: this.dataset.name,
                level: this.dataset.level
            };
            console.log('Selected character:', selectedCharacter);
            
            if (confirmPurchaseBtn) {
                confirmPurchaseBtn.disabled = false;
                console.log('Confirm purchase button enabled');
            }
        });
    });
    
    // Confirm purchase
    if (confirmPurchaseBtn) {
        confirmPurchaseBtn.addEventListener('click', function() {
            console.log('Confirm purchase clicked!');
            console.log('Selected item:', selectedItem);
            console.log('Selected character:', selectedCharacter);
            
            if (!selectedItem || !selectedCharacter) {
                console.log('Missing item or character selection');
                return;
            }
            
            console.log('Creating purchase form...');
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("shop.buy") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const itemId = document.createElement('input');
            itemId.type = 'hidden';
            itemId.name = 'item_id';
            itemId.value = selectedItem.id;
            form.appendChild(itemId);
            
            const characterGuid = document.createElement('input');
            characterGuid.type = 'hidden';
            characterGuid.name = 'character_guid';
            characterGuid.value = selectedCharacter.guid;
            form.appendChild(characterGuid);
            
            // Add service data if available
            const serviceName = document.getElementById('serviceName');
            if (serviceName && serviceName.value) {
                const serviceNameInput = document.createElement('input');
                serviceNameInput.type = 'hidden';
                serviceNameInput.name = 'service_name';
                serviceNameInput.value = serviceName.value;
                form.appendChild(serviceNameInput);
            }
            
            const serviceRace = document.getElementById('serviceRace');
            if (serviceRace && serviceRace.value) {
                const serviceRaceInput = document.createElement('input');
                serviceRaceInput.type = 'hidden';
                serviceRaceInput.name = 'service_race';
                serviceRaceInput.value = serviceRace.value;
                form.appendChild(serviceRaceInput);
            }
            
            const serviceGender = document.getElementById('serviceGender');
            if (serviceGender && serviceGender.value) {
                const serviceGenderInput = document.createElement('input');
                serviceGenderInput.type = 'hidden';
                serviceGenderInput.name = 'service_gender';
                serviceGenderInput.value = serviceGender.value;
                form.appendChild(serviceGenderInput);
            }
            
            const serviceFaction = document.getElementById('serviceFaction');
            if (serviceFaction && serviceFaction.value) {
                const serviceFactionInput = document.createElement('input');
                serviceFactionInput.type = 'hidden';
                serviceFactionInput.name = 'service_faction';
                serviceFactionInput.value = serviceFaction.value;
                form.appendChild(serviceFactionInput);
            }
            
            console.log('Submitting form...');
            document.body.appendChild(form);
            form.submit();
        });
    } else {
        console.log('Confirm purchase button not found!');
    }
}

// Initialize buy buttons when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing buy buttons...');
    initializeBuyButtons();
});
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
    
    console.log('Buy buttons found:', buyButtons.length);
    console.log('Character modal found:', characterModal ? 'Yes' : 'No');
    console.log('Character options found:', characterOptions.length);
    console.log('Confirm purchase button found:', confirmPurchaseBtn ? 'Yes' : 'No');
    
    let selectedItem = null;
    let selectedCharacter = null;
    
    buyButtons.forEach((button, index) => {
        console.log(`Buy button ${index}:`, button);
        button.addEventListener('click', function() {
            console.log('Buy button clicked!', this);
            selectedItem = {
                id: this.dataset.itemId,
                name: this.dataset.itemName,
                pointCost: this.dataset.pointCost,
                tokenCost: this.dataset.tokenCost
            };
            console.log('Selected item:', selectedItem);
            
            if (characterModal) {
                console.log('Opening character modal...');
                const modal = new bootstrap.Modal(characterModal);
                modal.show();
            } else {
                console.log('No character modal found');
                // No characters available
                alert('{{ __("shop.no_characters") }}');
            }
        });
    });
    
    // Character selection
    characterOptions.forEach((option, index) => {
        console.log(`Character option ${index}:`, option);
        option.addEventListener('click', function() {
            console.log('Character option clicked:', this);
            if (this.classList.contains('online')) {
                console.log('Character is online, cannot select');
                return;
            }
            
            characterOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            selectedCharacter = {
                guid: this.dataset.guid,
                name: this.dataset.name,
                level: this.dataset.level
            };
            console.log('Selected character:', selectedCharacter);
            
            confirmPurchaseBtn.disabled = false;
            console.log('Confirm purchase button enabled');
        });
    });
    
    // Confirm purchase
    if (confirmPurchaseBtn) {
        confirmPurchaseBtn.addEventListener('click', function() {
            console.log('Confirm purchase clicked!');
            console.log('Selected item:', selectedItem);
            console.log('Selected character:', selectedCharacter);
            
            if (!selectedItem || !selectedCharacter) {
                console.log('Missing item or character selection');
                return;
            }
            
            console.log('Creating purchase form...');
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
            
            console.log('Submitting form...');
            document.body.appendChild(form);
            form.submit();
        });
    } else {
        console.log('Confirm purchase button not found!');
    }
    
    // Reset form when modal is hidden
    const characterModal = document.getElementById('characterModal');
    if (characterModal) {
        characterModal.addEventListener('hidden.bs.modal', function() {
            console.log('Modal hidden, resetting form...');
            
            // Reset service options
            const serviceOptions = document.getElementById('serviceOptions');
            if (serviceOptions) serviceOptions.style.display = 'none';
            
            // Reset all service fields
            const nameChangeOption = document.getElementById('nameChangeOption');
            const raceChangeOption = document.getElementById('raceChangeOption');
            const genderChangeOption = document.getElementById('genderChangeOption');
            const factionChangeOption = document.getElementById('factionChangeOption');
            
            if (nameChangeOption) nameChangeOption.style.display = 'none';
            if (raceChangeOption) raceChangeOption.style.display = 'none';
            if (genderChangeOption) genderChangeOption.style.display = 'none';
            if (factionChangeOption) factionChangeOption.style.display = 'none';
            
            // Clear form values
            const serviceName = document.getElementById('serviceName');
            const serviceRace = document.getElementById('serviceRace');
            const serviceGender = document.getElementById('serviceGender');
            const serviceFaction = document.getElementById('serviceFaction');
            
            if (serviceName) serviceName.value = '';
            if (serviceRace) serviceRace.value = '';
            if (serviceGender) serviceGender.value = '';
            if (serviceFaction) serviceFaction.value = '';
            
            // Reset character selection
            const characterOptions = document.querySelectorAll('.character-option');
            characterOptions.forEach(option => {
                option.classList.remove('selected');
            });
            
            // Disable confirm button
            const confirmPurchaseBtn = document.getElementById('confirmPurchase');
            if (confirmPurchaseBtn) {
                confirmPurchaseBtn.disabled = true;
            }
        });
    }
});
</script>
@endsection
