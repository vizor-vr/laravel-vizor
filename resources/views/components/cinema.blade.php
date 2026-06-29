@once
    @vizorScripts
@endonce

<vz-cinema
    {{ $attributes->merge([]) }}
    @if($src) src="{{ $src }}" @endif
    @if($format) format="{{ $format->value }}" @endif
    @if($title) title="{{ $title }}" @endif
    @if($poster) poster="{{ $poster }}" @endif
    @if($author) author="{{ $author }}" @endif
    @if($muted) muted @endif
    @if($loop) loop @endif
    @if(!$controls) hide-controls @endif
    @if($autoplay) autoplay @endif
    @if($preload) preload="{{ $preload }}" @endif
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
            <source src="{{ $source['src'] }}" @if(isset($source['type'])) type="{{ $source['type'] }}" @endif @if(isset($source['quality'])) data-quality="{{ $source['quality'] }}" @endif />
        @endforeach
    @endif
    {{ $slot }}
</vz-cinema>
