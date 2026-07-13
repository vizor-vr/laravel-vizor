<track
    kind="subtitles"
    src="{{ $src }}"
    srclang="{{ $srclang }}"
    @if($label) label="{{ $label }}" @endif
    @if($default) default @endif
    {{ $attributes }}
/>
