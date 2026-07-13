<?php

namespace Vizor\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Vizor\Laravel\Commands\ComponentCommand;
use Vizor\Laravel\Commands\ExamplesCommand;
use Vizor\Laravel\Commands\InstallCommand;
use Vizor\Laravel\Commands\TestPageCommand;
use Vizor\Laravel\Components\VzAnnotation;
use Vizor\Laravel\Components\VzCaption;
use Vizor\Laravel\Components\VzCinema;
use Vizor\Laravel\Components\VzImg;
use Vizor\Laravel\Components\VzLive;
use Vizor\Laravel\Components\VzPlaylist;
use Vizor\Laravel\Components\VzTour;
use Vizor\Laravel\Components\VzVideo;
use Vizor\Laravel\Livewire\CinemaPlayer;
use Vizor\Laravel\Livewire\ImgViewer;
use Vizor\Laravel\Livewire\LivePlayer;
use Vizor\Laravel\Livewire\PlaylistPlayer;
use Vizor\Laravel\Livewire\TourViewer;
use Vizor\Laravel\Livewire\VideoPlayer;
use Vizor\Laravel\Middleware\InjectVizorAssets;
use Vizor\Laravel\Middleware\ValidateVizorLicense;

class VizorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/vizor.php', 'vizor');

        $this->app->singleton('vizor', fn () => new VizorManager);
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerBladeComponents();
        $this->registerBladeDirectives();
        $this->registerLivewireComponents();
        $this->registerCommands();
        $this->registerMiddleware();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'vizor');
    }

    // ──────────────────────────── Publishing ────────────────────────────

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/vizor.php' => config_path('vizor.php'),
        ], 'vizor-config');

        $this->publishes([
            __DIR__.'/../resources/js/vizor-alpine.js' => resource_path('js/vizor-alpine.js'),
        ], 'vizor-assets');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/vizor'),
        ], 'vizor-views');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'vizor-migrations');
    }

    // ──────────────────────────── Blade Components ────────────────────────────

    private function registerBladeComponents(): void
    {
        Blade::component('vizor-video', VzVideo::class);
        Blade::component('vizor-img', VzImg::class);
        Blade::component('vizor-tour', VzTour::class);
        Blade::component('vizor-cinema', VzCinema::class);
        Blade::component('vizor-live', VzLive::class);
        Blade::component('vizor-playlist', VzPlaylist::class);
        Blade::component('vizor-annotation', VzAnnotation::class);
        Blade::component('vizor-caption', VzCaption::class);
    }

    // ──────────────────────────── Blade Directives ────────────────────────────

    private function registerBladeDirectives(): void
    {
        // The URL logic lives in PlayerScript so tests can lock it against the
        // committed player-dist manifest (the 0.1.0 era pinned a dist file
        // that no longer existed and 404'd silently on every page).
        Blade::directive('vizorScripts', function () {
            return '<?php echo \Vizor\Laravel\Support\PlayerScript::tag(); ?>';
        });
    }

    // ──────────────────────────── Livewire Components ────────────────────────────

    private function registerLivewireComponents(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        Livewire::component('vizor-video-player', VideoPlayer::class);
        Livewire::component('vizor-img-viewer', ImgViewer::class);
        Livewire::component('vizor-tour-viewer', TourViewer::class);
        Livewire::component('vizor-cinema-player', CinemaPlayer::class);
        Livewire::component('vizor-live-player', LivePlayer::class);
        Livewire::component('vizor-playlist-player', PlaylistPlayer::class);
    }

    // ──────────────────────────── Commands ────────────────────────────

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            ComponentCommand::class,
            TestPageCommand::class,
            ExamplesCommand::class,
        ]);
    }

    // ──────────────────────────── Middleware ────────────────────────────

    private function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('vizor.license', ValidateVizorLicense::class);
        // Auto-inject the pinned player script into HTML responses (WS-G).
        // Config-gated (vizor.auto_inject, default OFF).
        $this->app['router']->aliasMiddleware('vizor.inject', InjectVizorAssets::class);
    }
}
