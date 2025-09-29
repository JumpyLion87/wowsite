@extends('layouts.app')

@section('content')

<div class="container how-to-play">
    <h1>{{ __('how_to_play.how_to_play_title') }}</h1>
    <div class="steps">

        <!-- Step 1: Create an Account -->
        <div class="step">
            <div class="step-content">
                <div class="step-text">
                    <h2>{{ __('how_to_play.step_1_title') }}</h2>
                    <p>{!! __('how_to_play.step_1_desc') !!}</p>
                    <a class="btn" href="{{ route('register') }}">{{ __('how_to_play.create_account') }}</a>
                </div>
                <img src="{{ asset('img/howtoplay/down_register.jpg') }}" alt="{{ __('how_to_play.create_account_alt') }}">
            </div>
        </div>

        <!-- Step 2: Download the Game -->
        <div class="step">
            <div class="step-content">
                <div class="step-text">
                    <h2>{{ __('how_to_play.step_2_title') }}</h2>
                    <p>{!! __('how_to_play.step_2_desc') !!}</p>
                    <div class="download-options">
                        <a class="btn btn-primary" href="{{ route('download') }}">{{ __('how_to_play.direct_download') }}</a>
                        <a class="btn btn-secondary" href="{{ route('download') }}">{{ __('how_to_play.torrent_download') }}</a>
                    </div>
                    <p><small>{{ __('how_to_play.download_note') }}</small></p>
                </div>
                <img src="{{ asset('img/howtoplay/down_download.png') }}" alt="{{ __('how_to_play.download_game_alt') }}">
            </div>
        </div>

        <!-- Step 3: Set the Realmlist -->
        <div class="step">
            <div class="step-content">
                <div class="step-text">
                    <h2>{{ __('how_to_play.step_3_title') }}</h2>
                    <p>{!! __('how_to_play.step_3_desc_1') !!}</p>
                    <p>{!! __('how_to_play.step_3_desc_2') !!}</p>
                    <pre id="realmlistText" style="cursor: pointer;" onclick="copyRealmlist()">set realmlist {{ $realmlist }}</pre>
                    <p>{!! __('how_to_play.step_3_desc_3') !!}</p>
                </div>
                <img id="down_img_realm" src="{{ asset('img/howtoplay/down_realmlist.png') }}" alt="{{ __('how_to_play.edit_realmlist_alt') }}">
            </div>
        </div>

        <!-- Step 4: Launch the Game -->
        <div class="step">
            <div class="step-content">
                <div class="step-text">
                    <h2>{{ __('how_to_play.step_4_title') }}</h2>
                    <p>{!! __('how_to_play.step_4_desc_1') !!}</p>
                    <p>{!! __('how_to_play.step_4_desc_2') !!}</p>
                </div>
                <img src="{{ asset('img/howtoplay/down_wow.png') }}" alt="{{ __('how_to_play.launch_wow_alt') }}">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyRealmlist() {
    const realmlistText = document.getElementById('realmlistText');
    const textToCopy = realmlistText.textContent;
    
    navigator.clipboard.writeText(textToCopy).then(() => {
        // Временно меняем текст для обратной связи
        const originalText = realmlistText.textContent;
        realmlistText.textContent = '{{ __("how_to_play.copied_success") }}';
        
        // Возвращаем оригинальный текст через 2 секунды
        setTimeout(() => {
            realmlistText.textContent = originalText;
        }, 2000);
    }).catch(err => {
        console.error('Ошибка при копировании текста: ', err);
    });
}
</script>
@endsection