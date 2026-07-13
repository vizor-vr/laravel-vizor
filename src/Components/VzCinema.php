<?php

namespace Vizor\Laravel\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Vizor\Laravel\Support\FormatEnum;

final class VzCinema extends Component
{
    public function __construct(
        public readonly ?string $src = null,
        public readonly ?FormatEnum $format = null,
        public readonly ?string $title = null,
        public readonly ?string $poster = null,
        public readonly ?string $author = null,
        public readonly bool $muted = false,
        public readonly bool $loop = false,
        public readonly bool $controls = true,
        public readonly bool $autoplay = false,
        public readonly ?string $preload = null,
        public readonly ?string $apiKey = null,
        public readonly ?string $licenseKey = null,
        public readonly ?string $apiEndpoint = null,
        public readonly ?string $primaryColor = null,
        public readonly ?string $contentId = null,
        public readonly ?string $controlsBehavior = null,
        public readonly bool $hideControls = false,
        public readonly ?array $sources = null,
    ) {}

    public function render(): View
    {
        return view('vizor::components.cinema');
    }
}
