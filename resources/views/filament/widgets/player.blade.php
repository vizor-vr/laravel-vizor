<x-filament-widgets::widget>
    <x-filament::section>
        @if($this->src)
            <div class="w-full aspect-video rounded-lg overflow-hidden">
                <vz-video
                    src="{{ $this->src }}"
                    @if($this->format) format="{{ $this->format }}" @endif
                    @if($this->contentId) content-id="{{ $this->contentId }}" @endif
                    @if(config('vizor.api_key')) api-key="{{ config('vizor.api_key') }}" @endif
                    @if(config('vizor.license_key')) license-key="{{ config('vizor.license_key') }}" @endif
                    controls
                    style="width: 100%; height: 100%;"
                ></vz-video>
            </div>
        @else
            <div class="flex items-center justify-center w-full aspect-video rounded-lg bg-gray-100 dark:bg-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No video source configured. Set the <code>src</code> property to display a player.
                </p>
            </div>
        @endif
    </x-filament::section>

    @once
        @push('scripts')
            @vizorScripts
        @endpush
    @endonce
</x-filament-widgets::widget>
