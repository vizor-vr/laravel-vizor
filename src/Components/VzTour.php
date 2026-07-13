<?php

namespace Vizor\Laravel\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Vizor\Laravel\Support\FormatEnum;

final class VzTour extends Component
{
    public function __construct(
        public readonly ?string $src = null,
        public readonly ?FormatEnum $format = null,
        public readonly ?string $title = null,
        public readonly ?string $poster = null,
        public readonly ?string $startProbeId = null,
        public readonly ?string $apiKey = null,
        public readonly ?string $licenseKey = null,
        public readonly ?string $apiEndpoint = null,
        public readonly ?string $primaryColor = null,
        public readonly ?string $contentId = null,
        public readonly ?string $controlsBehavior = null,
        public readonly bool $hideControls = false,
    ) {}

    public function render(): View
    {
        return view('vizor::components.tour');
    }
}
