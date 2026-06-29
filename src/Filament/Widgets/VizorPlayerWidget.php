<?php

namespace Vizor\Laravel\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Embeddable Vizor VR player widget for Filament dashboards.
 */
class VizorPlayerWidget extends Widget
{
    protected static string $view = 'vizor::filament.widgets.player';

    protected int|string|array $columnSpan = 'full';

    public ?string $contentId = null;

    public ?string $src = null;

    public ?string $format = null;
}
