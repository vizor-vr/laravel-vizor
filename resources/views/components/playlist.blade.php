@once
    @vizorScripts
@endonce

<vz-playlist
    {{ $attributes->merge([]) }}
    @if($autoplay) autoplay @endif
    @if($loopPlaylist) loop-playlist @endif
    @if($panel) panel="{{ $panel }}" @endif
    @if($apiKey) api-key="{{ $apiKey }}" @endif
    @if($licenseKey) license-key="{{ $licenseKey }}" @endif
    @if($primaryColor) primary-color="{{ $primaryColor }}" @endif
    x-data="vizorPlayer"
    x-ref="player"
>
    {{ $slot }}
</vz-playlist>
