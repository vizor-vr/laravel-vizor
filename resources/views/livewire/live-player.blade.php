<div
    x-data="vizorLivewirePlayer($wire)"
    x-init="init()"
    wire:ignore.self
>
    @once
        @vizorScripts
    @endonce

    <vz-live
        x-ref="player"
        @if($src) src="{{ $src }}" @endif
        @if($format) format="{{ $format->value }}" @endif
        @if($title) title="{{ $title }}" @endif
        @if($poster) poster="{{ $poster }}" @endif
        @if(!$controls) hide-controls @endif
        @if($primaryColor) primary-color="{{ $primaryColor }}" @endif
        style="width: 100%; aspect-ratio: 16/9;"
    >
        {{ $slot }}
    </vz-live>
</div>
