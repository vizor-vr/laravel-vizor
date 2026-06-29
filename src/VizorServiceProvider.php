<?php

namespace Vizor\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Vizor\Laravel\Commands\ComponentCommand;
use Vizor\Laravel\Commands\ExamplesCommand;
use Vizor\Laravel\Commands\InstallCommand;
use Vizor\Laravel\Commands\TestPageCommand;
use Vizor\Laravel\Components\VzAnnotation;
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
    }

    // ──────────────────────────── Blade Directives ────────────────────────────

    private function registerBladeDirectives(): void
    {
        Blade::directive('vizorScripts', function () {
            $cdnUrl = config('vizor.cdn_url', 'https://cdn.jsdelivr.net/npm/@vizor-vr/player@latest/dist');
            $useLocal = config('vizor.use_local_assets', false);

            if ($useLocal) {
                return '<?php echo \'<script type="module" src="\' . asset(\'js/vizor-alpine.js\') . \'"></script>\'; ?>'
                    . '<?php echo \'<script type="module" src="\' . asset(\'vendor/vizor/vizor-player.register.es.js\') . \'"></script>\'; ?>';
            }

            return '<?php echo \'<script type="module" src="'.$cdnUrl.'/vizor-player.register.es.js"></script>\'; ?>';
        });
    }

    // ──────────────────────────── Livewire Components ────────────────────────────

    private function registerLivewireComponents(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        \Livewire\Livewire::component('vizor-video-player', VideoPlayer::class);
        \Livewire\Livewire::component('vizor-img-viewer', ImgViewer::class);
        \Livewire\Livewire::component('vizor-tour-viewer', TourViewer::class);
        \Livewire\Livewire::component('vizor-cinema-player', CinemaPlayer::class);
        \Livewire\Livewire::component('vizor-live-player', LivePlayer::class);
        \Livewire\Livewire::component('vizor-playlist-player', PlaylistPlayer::class);
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
        $this->app['router']->aliasMiddleware('vizor.license', \Vizor\Laravel\Middleware\ValidateVizorLicense::class);
    }
}
