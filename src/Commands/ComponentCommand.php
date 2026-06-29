<?php

namespace Vizor\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ComponentCommand extends Command
{
    protected $signature = 'vizor:component {name : The component name} {--force : Overwrite existing files}';

    protected $description = 'Generate a Livewire component with Vizor VR player boilerplate';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! $this->validateName($name)) {
            return self::FAILURE;
        }

        $studlyName = Str::studly($name);
        $kebabName = Str::kebab($name);

        $classCreated = $this->createComponentClass($studlyName);
        $viewCreated = $this->createComponentView($kebabName, $studlyName);

        if (! $classCreated && ! $viewCreated) {
            $this->warn('No files were created. Use --force to overwrite existing files.');

            return self::FAILURE;
        }

        $this->info('');
        $this->info("  Vizor component \"{$studlyName}\" created successfully!");
        $this->info('');
        $this->comment("  Usage in Blade:");
        $this->comment("    <livewire:{$kebabName} src=\"/path/to/video.mp4\" format=\"MONO_360\" />");
        $this->info('');

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────────────

    private function validateName(string $name): bool
    {
        if (empty(trim($name))) {
            $this->error('Component name cannot be empty.');

            return false;
        }

        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $name)) {
            $this->error('Component name must start with a letter and contain only letters, numbers, hyphens, and underscores.');

            return false;
        }

        // Reject reserved names that collide with built-in Vizor Livewire components
        $reserved = [
            'VideoPlayer', 'ImgViewer', 'TourViewer',
            'CinemaPlayer', 'LivePlayer', 'PlaylistPlayer',
        ];

        if (in_array(Str::studly($name), $reserved, true)) {
            $this->error("The name \"{$name}\" conflicts with a built-in Vizor Livewire component.");

            return false;
        }

        return true;
    }

    private function createComponentClass(string $studlyName): bool
    {
        $path = app_path("Livewire/{$studlyName}.php");

        if (File::exists($path) && ! $this->option('force')) {
            $this->warn("  Component class already exists: app/Livewire/{$studlyName}.php (use --force to overwrite)");

            return false;
        }

        $stub = $this->getClassStub($studlyName);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);
        $this->info("  Created app/Livewire/{$studlyName}.php");

        return true;
    }

    private function createComponentView(string $kebabName, string $studlyName): bool
    {
        $path = resource_path("views/livewire/{$kebabName}.blade.php");

        if (File::exists($path) && ! $this->option('force')) {
            $this->warn("  View already exists: resources/views/livewire/{$kebabName}.blade.php (use --force to overwrite)");

            return false;
        }

        $stub = $this->getViewStub($studlyName);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);
        $this->info("  Created resources/views/livewire/{$kebabName}.blade.php");

        return true;
    }

    // ──────────────────────────── Stubs ────────────────────────────

    private function getClassStub(string $studlyName): string
    {
        return <<<PHP
<?php

namespace App\Livewire;

use Livewire\Component;

class {$studlyName} extends Component
{
    // ──────────────────────── Player Props ────────────────────────
    public ?string \$src = null;
    public string \$format = 'MONO_360';
    public ?string \$title = null;
    public ?string \$poster = null;
    public bool \$controls = true;
    public bool \$autoplay = false;
    public bool \$loop = false;
    public bool \$muted = false;

    // ──────────────────────── Reactive State ────────────────────────
    public bool \$ready = false;
    public bool \$playing = false;
    public float \$currentTime = 0;
    public float \$duration = 0;
    public float \$volume = 1;

    // ──────────────────────── Player Commands ────────────────────────

    public function play(): void
    {
        \$this->playing = true;
        \$this->dispatch('vizor-command', command: 'play');
    }

    public function pause(): void
    {
        \$this->playing = false;
        \$this->dispatch('vizor-command', command: 'pause');
    }

    public function seek(float \$time): void
    {
        \$this->currentTime = \$time;
        \$this->dispatch('vizor-command', command: 'seek', time: \$time);
    }

    // ──────────────────────── Event Handlers ────────────────────────

    public function onReady(): void
    {
        \$this->ready = true;
    }

    public function onPlay(): void
    {
        \$this->playing = true;
    }

    public function onPause(): void
    {
        \$this->playing = false;
    }

    public function onTimeUpdate(float \$time, float \$dur): void
    {
        \$this->currentTime = \$time;
        \$this->duration = \$dur;
    }

    public function onEnded(): void
    {
        \$this->playing = false;
    }

    public function onError(string \$code, string \$message): void
    {
        logger()->error("Vizor player error [{\$code}]: {\$message}");
    }

    public function render()
    {
        return view('livewire.{$this->toViewName($studlyName)}');
    }
}

PHP;
    }

    private function getViewStub(string $studlyName): string
    {
        return <<<'BLADE'
<div
    x-data="vizorLivewirePlayer($wire)"
    x-init="init()"
    wire:ignore.self
>
    @once
        @vizorScripts
    @endonce

    <vz-video
        x-ref="player"
        @if($src) src="{{ $src }}" @endif
        @if($format) format="{{ $format }}" @endif
        @if($title) title="{{ $title }}" @endif
        @if($poster) poster="{{ $poster }}" @endif
        @if($muted) muted @endif
        @if($loop) loop @endif
        @if(!$controls) hide-controls @endif
        @if($autoplay) autoplay @endif
        api-key="{{ config('vizor.api_key') }}"
        style="width: 100%; aspect-ratio: 16/9;"
    >
        {{ $slot }}
    </vz-video>
</div>

BLADE;
    }

    private function toViewName(string $studlyName): string
    {
        return Str::kebab($studlyName);
    }
}
