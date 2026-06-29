<?php

use Vizor\Laravel\Filament\Resources\ContentResource;
use Vizor\Laravel\Filament\Resources\ContentResource\Pages\CreateContent;
use Vizor\Laravel\Filament\Resources\ContentResource\Pages\EditContent;
use Vizor\Laravel\Filament\Resources\ContentResource\Pages\ListContents;
use Vizor\Laravel\Filament\VizorPlugin;
use Vizor\Laravel\Filament\Widgets\VizorPlayerWidget;

// Filament is an optional integration (composer "suggest"). When it isn't
// installed (e.g. CI without filament/filament), skip these tests rather than
// fatally erroring on the missing Filament\Contracts\Plugin interface. Add
// filament/filament to require-dev to actually run them.
beforeEach(function () {
    if (! interface_exists(\Filament\Contracts\Plugin::class)) {
        $this->markTestSkipped('Filament not installed — optional integration.');
    }
});

// ──────────────────────────── VizorPlugin ────────────────────────────

it('VizorPlugin getId returns vizor', function () {
    $plugin = VizorPlugin::make();

    expect($plugin->getId())->toBe('vizor');
});

it('VizorPlugin make returns an instance', function () {
    $plugin = VizorPlugin::make();

    expect($plugin)->toBeInstanceOf(VizorPlugin::class);
});

// ──────────────────────────── VizorPlayerWidget ────────────────────────────

it('VizorPlayerWidget has columnSpan set to full', function () {
    $widget = new VizorPlayerWidget;

    $reflection = new ReflectionProperty($widget, 'columnSpan');
    $reflection->setAccessible(true);

    expect($reflection->getValue($widget))->toBe('full');
});

it('VizorPlayerWidget has the correct view path', function () {
    $reflection = new ReflectionProperty(VizorPlayerWidget::class, 'view');
    $reflection->setAccessible(true);

    expect($reflection->getValue(new VizorPlayerWidget))->toBe('vizor::filament.widgets.player');
});

it('VizorPlayerWidget properties default to null', function () {
    $widget = new VizorPlayerWidget;

    expect($widget->contentId)->toBeNull();
    expect($widget->src)->toBeNull();
    expect($widget->format)->toBeNull();
});

// ──────────────────────────── ContentResource ────────────────────────────

it('ContentResource has correct model label', function () {
    expect(ContentResource::getModelLabel())->toBe('Content');
    expect(ContentResource::getPluralModelLabel())->toBe('Content');
});

it('ContentResource navigation group comes from config', function () {
    config(['vizor.filament.navigation_group' => 'Vizor']);

    expect(ContentResource::getNavigationGroup())->toBe('Vizor');
});

it('ContentResource navigation group respects custom config value', function () {
    config(['vizor.filament.navigation_group' => 'Media']);

    expect(ContentResource::getNavigationGroup())->toBe('Media');
});

it('ContentResource pages are configured', function () {
    $pages = ContentResource::getPages();

    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('edit');
});

// ──────────────────────────── Plugin Registration ────────────────────────────

it('VizorPlugin register method does not throw when filament disabled', function () {
    config(['vizor.filament.enabled' => false]);

    $plugin = VizorPlugin::make();

    // Create a minimal mock panel to verify register does not add resources
    // when filament is disabled. We use a simple approach: create a real
    // plugin instance and ensure calling register with a mock does not throw.
    $panel = Mockery::mock(\Filament\Panel::class);

    // When disabled, register() should return early without calling $panel->resources()
    $panel->shouldNotReceive('resources');
    $panel->shouldNotReceive('widgets');

    $plugin->register($panel);
});

it('VizorPlugin registers resources and widgets when filament is enabled', function () {
    config(['vizor.filament.enabled' => true]);

    $plugin = VizorPlugin::make();

    $panel = Mockery::mock(\Filament\Panel::class);

    // When enabled, register() should call resources() and widgets()
    $panel->shouldReceive('resources')
        ->once()
        ->with([ContentResource::class])
        ->andReturnSelf();

    $panel->shouldReceive('widgets')
        ->once()
        ->with([VizorPlayerWidget::class])
        ->andReturnSelf();

    $plugin->register($panel);
});
