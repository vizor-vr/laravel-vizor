<?php

namespace Vizor\Laravel\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Vizor\Laravel\Filament\Resources\ContentResource;
use Vizor\Laravel\Filament\Widgets\VizorPlayerWidget;

/**
 * Filament plugin that registers the Vizor content resource and player widget.
 *
 * Enable via config('vizor.filament.enabled') = true.
 */
class VizorPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'vizor';
    }

    public function register(Panel $panel): void
    {
        if (! config('vizor.filament.enabled', false)) {
            return;
        }

        $panel
            ->resources([
                ContentResource::class,
            ])
            ->widgets([
                VizorPlayerWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
