<?php

namespace Vizor\Laravel\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class VzAnnotation extends Component
{
    public function __construct(
        public readonly ?float $lat = null,
        public readonly ?float $lon = null,
        public readonly ?string $title = null,
        public readonly ?string $icon = null,
        public readonly ?float $timeStart = null,
        public readonly ?float $timeEnd = null,
        public readonly ?int $sortOrder = null,
    ) {}

    public function render(): View
    {
        return view('vizor::components.annotation');
    }
}
