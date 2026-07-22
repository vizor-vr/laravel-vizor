<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Vizor\Laravel\Components\VzAnnotation;
use Vizor\Laravel\Components\VzCinema;
use Vizor\Laravel\Components\VzImg;
use Vizor\Laravel\Components\VzLive;
use Vizor\Laravel\Components\VzPlaylist;
use Vizor\Laravel\Components\VzTour;
use Vizor\Laravel\Components\VzVideo;
use Vizor\Laravel\Facades\Vizor;
use Vizor\Laravel\Middleware\ValidateVizorLicense;
use Vizor\Laravel\VizorManager;

describe('VizorServiceProvider', function () {

    // ──────────────────────────── Config ────────────────────────────

    it('merges package config into the application config', function () {
        expect(config('vizor'))->toBeArray();
        expect(config('vizor.api_url'))->toBe('https://api.vizor-vr.test');
        expect(config('vizor.player_version'))->not->toBeNull();
    });

    // ──────────────────────────── Singleton ────────────────────────────

    it('registers the vizor singleton in the container', function () {
        expect(app()->bound('vizor'))->toBeTrue();

        $instance = app('vizor');

        expect($instance)->toBeInstanceOf(VizorManager::class);
    });

    it('returns the same instance on repeated resolution (singleton)', function () {
        $a = app('vizor');
        $b = app('vizor');

        expect($a)->toBe($b);
    });

    // ──────────────────────────── Facade ────────────────────────────

    it('resolves the Vizor facade accessor to VizorManager', function () {
        $resolved = Vizor::getFacadeRoot();

        expect($resolved)->toBeInstanceOf(VizorManager::class);
    });

    // ──────────────────────────── Blade Components ────────────────────────────

    it('registers all 7 Blade components', function () {
        $aliases = Blade::getClassComponentAliases();

        expect($aliases)->toHaveKey('vizor-video');
        expect($aliases)->toHaveKey('vizor-img');
        expect($aliases)->toHaveKey('vizor-tour');
        expect($aliases)->toHaveKey('vizor-cinema');
        expect($aliases)->toHaveKey('vizor-live');
        expect($aliases)->toHaveKey('vizor-playlist');
        expect($aliases)->toHaveKey('vizor-annotation');
    });

    it('maps Blade component aliases to the correct classes', function () {
        $aliases = Blade::getClassComponentAliases();

        expect($aliases['vizor-video'])->toBe(VzVideo::class);
        expect($aliases['vizor-img'])->toBe(VzImg::class);
        expect($aliases['vizor-tour'])->toBe(VzTour::class);
        expect($aliases['vizor-cinema'])->toBe(VzCinema::class);
        expect($aliases['vizor-live'])->toBe(VzLive::class);
        expect($aliases['vizor-playlist'])->toBe(VzPlaylist::class);
        expect($aliases['vizor-annotation'])->toBe(VzAnnotation::class);
    });

    // ──────────────────────────── Blade Directives ────────────────────────────

    it('registers the @vizorScripts Blade directive', function () {
        $directives = Blade::getCustomDirectives();

        expect($directives)->toHaveKey('vizorScripts');
    });

    it('outputs a pinned script tag from @vizorScripts directive', function () {
        config(['vizor.cdn_url' => null, 'vizor.use_local_assets' => false]);
        $rendered = Blade::render('@vizorScripts');

        expect($rendered)->toContain('<script');
        expect($rendered)->toContain('/dist/register.js');
        expect($rendered)->toContain('type="module"');
        expect($rendered)->not->toContain('@latest');
    });

    // ──────────────────────────── Views ────────────────────────────

    it('loads views under the vizor namespace', function () {
        $viewFinder = app('view')->getFinder();
        $hints = $viewFinder->getHints();

        expect($hints)->toHaveKey('vizor');
        expect($hints['vizor'])->toBeArray();
    });

    // ──────────────────────────── Middleware ────────────────────────────

    it('registers the vizor.license middleware alias', function () {
        $router = app('router');

        $middleware = $router->getMiddleware();

        expect($middleware)->toHaveKey('vizor.license');
        expect($middleware['vizor.license'])->toBe(ValidateVizorLicense::class);
    });

    // ──────────────────────────── Commands ────────────────────────────

    it('registers artisan commands when running in console', function () {
        $commands = Artisan::all();

        expect($commands)->toHaveKey('vizor:install');
        expect($commands)->toHaveKey('vizor:component');
        expect($commands)->toHaveKey('vizor:test-page');
        expect($commands)->toHaveKey('vizor:examples');
    });

    // ──────────────────────────── Publishing ────────────────────────────

    it('has publishable groups for config, assets, and views', function () {
        $groups = ServiceProvider::$publishGroups;

        expect($groups)->toHaveKey('vizor-config');
        expect($groups)->toHaveKey('vizor-assets');
        expect($groups)->toHaveKey('vizor-views');
    });

    it('publishes the vizor config file from the correct source', function () {
        $groups = ServiceProvider::$publishGroups;

        $configPaths = $groups['vizor-config'];
        $sources = array_keys($configPaths);
        $destinations = array_values($configPaths);

        // Source should end with config/vizor.php
        expect($sources[0])->toContain('config');
        expect($sources[0])->toContain('vizor.php');

        // Destination should be the config path
        expect($destinations[0])->toBe(config_path('vizor.php'));
    });

});
