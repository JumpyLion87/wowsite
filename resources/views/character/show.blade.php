@extends('layouts.app')

@section('title', __('character.equipment_title'))

@section('content')
<div class="container">
    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
        <a href="{{ route('home') }}" class="btn btn-primary">{{ __('common.back_to_home') }}</a>
    @else
        @php
            $qualityColors = config('wow.quality_colors');
        @endphp
            <div class="character-container">
                <!-- Left Equipment Column -->
                <div class="equipment-column equipment-left">
                    @foreach([0, 1, 2, 14, 4, 3, 18, 8] as $slot)
                        @include('character.partials.equipment-slot', ['slot' => $slot, 'equippedItems' => $equippedItems, 'character' => $character])
                    @endforeach
                </div>

                <!-- Character Center -->
                <div class="character-center">
                    <div class="character-name">{{ $character->name }}</div>
                    <div class="character-details">
                        <span class="character-level">{{ __('character.level_label') }} {{ $character->level }} {{ config('wow.classes')[$character->class]['name'] ?? __('character.class_unknown') }}</span>
                        <span class="character-race">{{ config('wow.races')[$character->race]['name'] ?? __('character.race_unknown') }}</span>
                    </div>
                    
                    <div class="character-image">
                        <img src="{{ asset('assets/3dmodels/3d_default.gif') }}" alt="{{ __('character.default_character_image') }}" class="default-image">
                    </div>

                    <!-- Weapons Container -->
                    <div class="weapons-container">
                        @foreach([15, 16, 17] as $slot)
                            @include('character.partials.equipment-slot', ['slot' => $slot, 'equippedItems' => $equippedItems, 'character' => $character])
                        @endforeach
                    </div>
                </div>

                <!-- Right Equipment Column -->
                <div class="equipment-column equipment-right">
                    @foreach([9, 5, 6, 7, 10, 11, 12, 13] as $slot)
                        @include('character.partials.equipment-slot', ['slot' => $slot, 'equippedItems' => $equippedItems, 'character' => $character])
                    @endforeach
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="character-tab-nav">
                <button data-tab="stats-tab" class="active">{{ __('character.tab_stats') }}</button>
                <button data-tab="talents-tab">{{ __('character.tab_talents') }}</button>
                <button data-tab="pvp-tab">{{ __('character.tab_pvp') }}</button>
            </div>

            <!-- Tab Contents -->
            <div id="stats-tab" class="character-tab-content active">
                @if(isset($stats))
                    <div class="stats-container">
                        <!-- Base Stats -->
                        <div class="stats-category">
                            <h3>{{ __('character.stats_base') }}</h3>
                            <div class="stats-item"><span>{{ __('character.stat_health') }}</span><span>{{ number_format($stats->maxhealth) }}</span></div>
                            @if($stats->maxpower1 > 0)
                                <div class="stats-item"><span>{{ __('character.stat_mana') }}</span><span>{{ number_format($stats->maxpower1) }}</span></div>
                            @else
                                <div class="stats-item"><span>{{ __('character.stat_mana') }}</span><span>{{ __('character.stat_not_available') }}</span></div>
                            @endif
                            <!-- Add other power types based on class -->
                            <div class="stats-item"><span>{{ __('character.stat_strength') }}</span><span>{{ number_format($stats->strength) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_agility') }}</span><span>{{ number_format($stats->agility) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_stamina') }}</span><span>{{ number_format($stats->stamina) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_intellect') }}</span><span>{{ number_format($stats->intellect) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_spirit') }}</span><span>{{ number_format($stats->spirit) }}</span></div>
                        </div>

                        <!-- Defense Stats -->
                        <div class="stats-category">
                            <h3>{{ __('character.stats_defense') }}</h3>
                            <div class="stats-item"><span>{{ __('character.stat_armor') }}</span><span>{{ number_format($stats->armor) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_block') }}</span><span>{{ number_format($stats->blockPct, 2) }}%</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_dodge') }}</span><span>{{ number_format($stats->dodgePct, 2) }}%</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_parry') }}</span><span>{{ number_format($stats->parryPct, 2) }}%</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_resilience') }}</span><span>{{ number_format($stats->resilience) }}</span></div>
                        </div>

                        <!-- Melee Stats -->
                        <div class="stats-category">
                            <h3>{{ __('character.stats_melee') }}</h3>
                            <div class="stats-item"><span>{{ __('character.stat_attack_power') }}</span><span>{{ number_format($stats->attackPower) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_crit_chance') }}</span><span>{{ number_format($stats->critPct, 2) }}%</span></div>
                        </div>

                        <!-- Ranged Stats -->
                        <div class="stats-category">
                            <h3>{{ __('character.stats_ranged') }}</h3>
                            <div class="stats-item"><span>{{ __('character.stat_ranged_attack_power') }}</span><span>{{ number_format($stats->rangedAttackPower) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_ranged_crit_chance') }}</span><span>{{ number_format($stats->rangedCritPct, 2) }}%</span></div>
                        </div>

                        <!-- Resistances -->
                        <div class="stats-category">
                            <h3>{{ __('character.stats_resistances') }}</h3>
                            <div class="stats-item"><span>{{ __('character.stat_holy_resistance') }}</span><span>{{ number_format($stats->resHoly) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_fire_resistance') }}</span><span>{{ number_format($stats->resFire) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_nature_resistance') }}</span><span>{{ number_format($stats->resNature) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_frost_resistance') }}</span><span>{{ number_format($stats->resFrost) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_shadow_resistance') }}</span><span>{{ number_format($stats->resShadow) }}</span></div>
                            <div class="stats-item"><span>{{ __('character.stat_arcane_resistance') }}</span><span>{{ number_format($stats->resArcane) }}</span></div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">{{ __('character.stats_none') }}</div>
                @endif
            </div>

            <div id="talents-tab" class="character-tab-content">
                <div class="alert alert-info">{{ __('character.talents_coming_soon') }}</div>
            </div>

            <div id="pvp-tab" class="character-tab-content">
                @if($pvpTeams && count($pvpTeams) > 0)
                    @foreach($pvpTeams as $team)
                        <div class="pvp-team-item">
                            <div class="pvp-team">
                                {{ $team->name }} ({{$team->getFormattedType() }}, {{ __('character.pvp_rating') }}: {{ $team->rating }})
                            </div>
                            <div class="pvp-members">
                                <ul>
                                    @foreach($team->members as $member)
                                        <li>
                                            <a href="{{ route('character.show.guid', ['guid' => $member->guid]) }}">
                                                {{ $member->name }}
                                                <span class="member-details">
                                                    <!-- Faction, race, class icons would go here -->
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">{{ __('character.pvp_none') }}</div>
                @endif
                <div class="pvp-kills">{{ __('character.pvp_total_kills') }}: <span>{{ number_format($totalKills) }}</span></div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/character.js') }}"></script>
<script>
    // Tab functionality
    document.querySelectorAll('.character- tab-nav button').forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and content
            document.querySelectorAll('.character-tab-nav button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.character-tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.add('active');
        });
    });
</script>
@endsection
