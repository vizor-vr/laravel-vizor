<vz-annotation
    {{ $attributes->merge([]) }}
    @if($lat !== null) lat="{{ $lat }}" @endif
    @if($lon !== null) lon="{{ $lon }}" @endif
    @if($title) title="{{ $title }}" @endif
    @if($icon) icon="{{ $icon }}" @endif
    @if($timeStart !== null) time-start="{{ $timeStart }}" @endif
    @if($timeEnd !== null) time-end="{{ $timeEnd }}" @endif
    @if($sortOrder !== null) sort-order="{{ $sortOrder }}" @endif
>
    {{ $slot }}
</vz-annotation>
