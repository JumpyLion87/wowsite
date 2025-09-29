@php
    $slotDefs = config('wow.slots.definitions');
    $slotLabels = config('wow.slots.labels');
    $defaultIcons = config('wow.slots.default_icons');
    $qualityColors = config('wow.quality_colors');
    
    $item = $equippedItems[$slot] ?? null;
    $slotName = $slotLabels[$slot] ?? 'Unknown Slot';
    $slotType = $slotDefs[$slot] ?? 'unknown';
@endphp

    <div class="slot {{ $item ? 'has-item' : '' }} {{ in_array($slot, [15, 16, 17]) ? 'weapon-slot' : '' }}" data-slot="{{ $slot }}" data-slot-type="{{ $slotType }}" @if($item) data-item="{{ json_encode($item) }}" @endif>
    <div class="slot-icon">
        @if($item)
            @php
                $icon = !empty($item->icon) ? $item->icon : ($defaultIcons[$slot] ?? 'inv_misc_questionmark');
                $iconSrc = !empty($item->icon) ? "https://wow.zamimg.com/images/wow/icons/large/$icon.jpg" : "/img/characterarmor/$icon";
            @endphp
            <img src="{{ $iconSrc }}" alt="{{ $slotName }}" loading="lazy">
        @else
            <img src="/img/characterarmor/{{ $defaultIcons[$slot] ?? 'inv_misc_questionmark' }}" alt="{{ $slotName }}" loading="lazy">
        @endif
    </div>
    <div class="slot-info">
        <div class="slot-name">{{ __("character.label_$slotType") }}</div>
        @if($item)
            <div class="slot-item" style="color: {{ isset($item->Quality) ? ($qualityColors[$item->Quality] ?? '#ffffff') : '#ffffff' }} !important;">
                {{ $item->name }}
            </div>
        @else
            <div class="empty-slot">{{ __('character.slot_empty') }}</div>
        @endif
    </div>
</div>
