<div
    x-data="vizorLivewirePlayer($wire)"
    x-init="init()"
    wire:ignore.self
>
    @once
        @vizorScripts
    @endonce

    <vz-tour
        x-ref="player"
        @if($src) src="{{ $src }}" @endif
        @if($format) format="{{ $format->value }}" @endif
        @if($title) title="{{ $title }}" @endif
        @if($poster) poster="{{ $poster }}" @endif
        @if($startProbeId) start-probe-id="{{ $startProbeId }}" @endif
        @if($primaryColor) primary-color="{{ $primaryColor }}" @endif
        style="width: 100%; aspect-ratio: 16/9;"
    >
        {{ $slot }}
    </vz-tour>
</div>
