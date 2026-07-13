<?php

namespace Vizor\Laravel\Tests;

use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Vizor\Laravel\Facades\Vizor;
use Vizor\Laravel\VizorServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            VizorServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Vizor' => Vizor::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

        $app['config']->set('vizor.api_url', 'https://api.vizor-vr.test');
        $app['config']->set('vizor.api_key', 'test-api-key-123');
        $app['config']->set('vizor.license_key', 'test-license-key-456');
        $app['config']->set('vizor.license_mode', 'saas');
        $app['config']->set('vizor.validate_license', false);
        $app['config']->set('vizor.broadcasting.enabled', false);
        $app['config']->set('vizor.filament.enabled', false);
    }
}
