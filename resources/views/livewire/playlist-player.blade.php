<div
    x-data="vizorLivewirePlayer($wire)"
    x-init="init()"
    wire:ignore.self
>
    @once
        @vizorScripts
    @endonce

    <vz-playlist
        x-ref="player"
        @if($autoplay) autoplay @endif
        @if($loopPlaylist) loop-playlist @endif
        @if($panel) panel="{{ $panel }}" @endif
        @if($primaryColor) primary-color="{{ $primaryColor }}" @endif
        style="width: 100%; aspect-ratio: 16/9;"
    >
        {{ $slot }}
    </vz-playlist>
</div>
