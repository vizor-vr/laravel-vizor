<?php

namespace Vizor\Laravel\Components;

use Illuminate\View\Component;

final class VzPlaylist extends Component
{
    public function __construct(
        public readonly bool $autoplay = false,
        public readonly bool $loopPlaylist = false,
        public readonly ?string $panel = null,
        public readonly ?string $apiKey = null,
        public readonly ?string $licenseKey = null,
        public readonly ?string $primaryColor = null,
    ) {}

    public function render()
    {
        return view('vizor::components.playlist');
    }
}
