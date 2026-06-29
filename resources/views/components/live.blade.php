@once
    @vizorScripts
@endonce

<vz-live
    {{ $attributes->merge([]) }}
    @if($src) src="{{ $src }}" @endif
    @if($format) format="{{ $format->value }}" @endif
    @if($title) title="{{ $title }}" @endif
    @if($poster) poster="{{ $poster }}" @endif
    @if($muted) muted @endif
    @if(!$controls) hide-controls @endif
    @if($apiKey) api-key="{{ $apiKey }}" @endif
    @if($licenseKey) license-key="{{ $licenseKey }}" @endif
    @if($apiEndpoint) api-endpoint="{{ $apiEndpoint }}" @endif
    @if($primaryColor) primary-color="{{ $primaryColor }}" @endif
    @if($contentId) content-id="{{ $contentId }}" @endif
    @if($controlsBehavior) controls-behavior="{{ $controlsBehavior }}" @endif
    @if($hideControls) hide-controls @endif
    x-data="vizorPlayer"
    x-ref="player"
>
    @if($sources)
        @foreach($sources as $source)
            <source src="{{ $source['src'] }}" @if(isset($source['type'])) type="{{ $source['type'] }}" @endif />
        @endforeach
    @endif
    {{ $slot }}
</vz-live>
