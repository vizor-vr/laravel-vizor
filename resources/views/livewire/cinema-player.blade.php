<div
    x-data="vizorLivewirePlayer($wire)"
    x-init="init()"
    wire:ignore.self
>
    @once
        @vizorScripts
    @endonce

    <vz-cinema
        x-ref="player"
        @if($src) src="{{ $src }}" @endif
        @if($format) format="{{ $format->value }}" @endif
        @if($title) title="{{ $title }}" @endif
        @if($poster) poster="{{ $poster }}" @endif
        @if($isMuted) muted @endif
        @if($loop) loop @endif
        @if(!$controls) hide-controls @endif
        @if($autoplay) autoplay @endif
        @if($primaryColor) primary-color="{{ $primaryColor }}" @endif
        style="width: 100%; aspect-ratio: 16/9;"
    >
        {{ $slot }}
    </vz-cinema>
</div>
