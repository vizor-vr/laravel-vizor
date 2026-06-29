@once
    @vizorScripts
@endonce

<vz-img
    {{ $attributes->merge([]) }}
    @if($src) src="{{ $src }}" @endif
    @if($format) format="{{ $format->value }}" @endif
    @if($title) title="{{ $title }}" @endif
    @if($poster) poster="{{ $poster }}" @endif
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
    {{ $slot }}
</vz-img>
