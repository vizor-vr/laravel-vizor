<?php

namespace Vizor\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExamplesCommand extends Command
{
    protected $signature = 'vizor:examples {--force : Overwrite existing files}';

    protected $description = 'Publish example Livewire components demonstrating Vizor VR player usage';

    /**
     * Map of example components: StudlyName => description.
     */
    private const EXAMPLES = [
        'VideoPlayerExample' => 'Basic video player with playback controls',
        'VideoGalleryExample' => 'Video gallery with format selector',
        'AnalyticsDashboardExample' => 'Analytics dashboard with player events',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('  Publishing Vizor example components...');
        $this->info('');

        $created = 0;

        foreach (self::EXAMPLES as $name => $description) {
            $classCreated = $this->createExampleClass($name);
            $viewCreated = $this->createExampleView($name);

            if ($classCreated || $viewCreated) {
                $created++;
            }
        }

        if ($created === 0) {
            $this->warn('No files were created. Use --force to overwrite existing files.');

            return self::FAILURE;
        }

        $this->info('');
        $this->info("  Published {$created} example component(s)!");
        $this->info('');
        $this->comment('  Usage in Blade:');
        $this->comment('    <livewire:video-player-example />');
        $this->comment('    <livewire:video-gallery-example />');
        $this->comment('    <livewire:analytics-dashboard-example />');
        $this->info('');
        $this->comment('  Remember to register these components in your AppServiceProvider or');
        $this->comment('  rely on Livewire auto-discovery (app/Livewire namespace).');
        $this->info('');

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────────────

    private function createExampleClass(string $name): bool
    {
        $path = app_path("Livewire/{$name}.php");

        if (File::exists($path) && ! $this->option('force')) {
            $this->warn("  Class already exists: app/Livewire/{$name}.php (use --force to overwrite)");

            return false;
        }

        $stub = $this->getClassStub($name);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);
        $this->info("  Created app/Livewire/{$name}.php");

        return true;
    }

    private function createExampleView(string $name): bool
    {
        $kebab = $this->toKebab($name);
        $path = resource_path("views/livewire/{$kebab}.blade.php");

        if (File::exists($path) && ! $this->option('force')) {
            $this->warn("  View already exists: resources/views/livewire/{$kebab}.blade.php (use --force to overwrite)");

            return false;
        }

        $stub = $this->getViewStub($name);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);
        $this->info("  Created resources/views/livewire/{$kebab}.blade.php");

        return true;
    }

    private function toKebab(string $studly): string
    {
        return \Illuminate\Support\Str::kebab($studly);
    }

    // ──────────────────────────── Class Stubs ────────────────────────────

    private function getClassStub(string $name): string
    {
        return match ($name) {
            'VideoPlayerExample' => $this->videoPlayerExampleClass(),
            'VideoGalleryExample' => $this->videoGalleryExampleClass(),
            'AnalyticsDashboardExample' => $this->analyticsDashboardExampleClass(),
            default => '',
        };
    }

    private function videoPlayerExampleClass(): string
    {
        return <<<'PHP'
<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Example: Basic Vizor VR video player with playback controls.
 *
 * Usage: <livewire:video-player-example />
 */
class VideoPlayerExample extends Component
{
    // ──────────────────────── Player Props ────────────────────────
    public string $src = 'https://cdn.vizor-vr.com/samples/360-sample.mp4';
    public string $format = 'MONO_360';
    public string $title = 'Example 360 Video';

    // ──────────────────────── Reactive State ────────────────────────
    public bool $ready = false;
    public bool $playing = false;
    public float $currentTime = 0;
    public float $duration = 0;
    public float $volume = 1;
    public bool $muted = false;

    // ──────────────────────── Commands ────────────────────────

    public function play(): void
    {
        $this->playing = true;
        $this->dispatch('vizor-command', command: 'play');
    }

    public function pause(): void
    {
        $this->playing = false;
        $this->dispatch('vizor-command', command: 'pause');
    }

    public function togglePlay(): void
    {
        $this->playing ? $this->pause() : $this->play();
    }

    public function seek(float $time): void
    {
        $this->currentTime = $time;
        $this->dispatch('vizor-command', command: 'seek', time: $time);
    }

    // ──────────────────────── Event Handlers ────────────────────────

    public function onReady(): void
    {
        $this->ready = true;
    }

    public function onPlay(): void
    {
        $this->playing = true;
    }

    public function onPause(): void
    {
        $this->playing = false;
    }

    public function onTimeUpdate(float $time, float $dur): void
    {
        $this->currentTime = $time;
        $this->duration = $dur;
    }

    public function onEnded(): void
    {
        $this->playing = false;
    }

    public function onVolumeChange(float $vol, bool $muted): void
    {
        $this->volume = $vol;
        $this->muted = $muted;
    }

    public function onError(string $code, string $message): void
    {
        logger()->error("Vizor player error [{$code}]: {$message}");
    }

    public function render()
    {
        return view('livewire.video-player-example');
    }
}

PHP;
    }

    private function videoGalleryExampleClass(): string
    {
        return <<<'PHP'
<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Example: Video gallery with selectable formats.
 *
 * Usage: <livewire:video-gallery-example />
 */
class VideoGalleryExample extends Component
{
    public string $activeSrc = '';
    public string $activeFormat = 'MONO_360';
    public string $activeTitle = '';
    public bool $playing = false;

    /**
     * Gallery items -- replace with your own videos.
     *
     * @var array<int, array{src: string, format: string, title: string, poster: string|null}>
     */
    public array $items = [
        [
            'src' => 'https://cdn.vizor-vr.com/samples/360-sample.mp4',
            'format' => 'MONO_360',
            'title' => '360 Panorama',
            'poster' => null,
        ],
        [
            'src' => 'https://cdn.vizor-vr.com/samples/flat-sample.mp4',
            'format' => 'MONO_FLAT',
            'title' => 'Flat Video',
            'poster' => null,
        ],
        [
            'src' => 'https://cdn.vizor-vr.com/samples/180-sample.mp4',
            'format' => 'STEREO_180_LR',
            'title' => 'VR180 Stereo',
            'poster' => null,
        ],
    ];

    public function mount(): void
    {
        if (count($this->items) > 0) {
            $this->selectItem(0);
        }
    }

    public function selectItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $item = $this->items[$index];
        $this->activeSrc = $item['src'];
        $this->activeFormat = $item['format'];
        $this->activeTitle = $item['title'];
        $this->playing = false;
    }

    public function onPlay(): void
    {
        $this->playing = true;
    }

    public function onPause(): void
    {
        $this->playing = false;
    }

    public function onEnded(): void
    {
        $this->playing = false;
    }

    public function onError(string $code, string $message): void
    {
        logger()->error("Vizor gallery error [{$code}]: {$message}");
    }

    public function render()
    {
        return view('livewire.video-gallery-example');
    }
}

PHP;
    }

    private function analyticsDashboardExampleClass(): string
    {
        return <<<'PHP'
<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Example: Analytics dashboard tracking player events.
 *
 * Usage: <livewire:analytics-dashboard-example />
 */
class AnalyticsDashboardExample extends Component
{
    // ──────────────────────── Player Props ────────────────────────
    public string $src = 'https://cdn.vizor-vr.com/samples/360-sample.mp4';
    public string $format = 'MONO_360';

    // ──────────────────────── Reactive State ────────────────────────
    public bool $ready = false;
    public bool $playing = false;
    public float $currentTime = 0;
    public float $duration = 0;

    // ──────────────────────── Analytics ────────────────────────
    public int $playCount = 0;
    public int $pauseCount = 0;
    public int $seekCount = 0;
    public float $totalWatchTime = 0;
    public ?string $lastError = null;

    /** @var array<int, array{event: string, time: float, timestamp: string}> */
    public array $eventLog = [];

    // ──────────────────────── Event Handlers ────────────────────────

    public function onReady(): void
    {
        $this->ready = true;
        $this->logEvent('ready');
    }

    public function onPlay(): void
    {
        $this->playing = true;
        $this->playCount++;
        $this->logEvent('play');
    }

    public function onPause(): void
    {
        $this->playing = false;
        $this->pauseCount++;
        $this->logEvent('pause');
    }

    public function onTimeUpdate(float $time, float $dur): void
    {
        $elapsed = $time - $this->currentTime;
        if ($elapsed > 0 && $elapsed < 2) {
            $this->totalWatchTime += $elapsed;
        }
        $this->currentTime = $time;
        $this->duration = $dur;
    }

    public function onEnded(): void
    {
        $this->playing = false;
        $this->logEvent('ended');
    }

    public function onError(string $code, string $message): void
    {
        $this->lastError = "[{$code}] {$message}";
        $this->logEvent("error: {$code}");
    }

    public function clearLog(): void
    {
        $this->eventLog = [];
        $this->playCount = 0;
        $this->pauseCount = 0;
        $this->seekCount = 0;
        $this->totalWatchTime = 0;
        $this->lastError = null;
    }

    // ──────────────────────── Helpers ────────────────────────

    private function logEvent(string $event): void
    {
        $this->eventLog[] = [
            'event' => $event,
            'time' => round($this->currentTime, 2),
            'timestamp' => now()->format('H:i:s'),
        ];

        // Keep only the last 50 events
        if (count($this->eventLog) > 50) {
            $this->eventLog = array_slice($this->eventLog, -50);
        }
    }

    public function render()
    {
        return view('livewire.analytics-dashboard-example');
    }
}

PHP;
    }

    // ──────────────────────────── View Stubs ────────────────────────────

    private function getViewStub(string $name): string
    {
        return match ($name) {
            'VideoPlayerExample' => $this->videoPlayerExampleView(),
            'VideoGalleryExample' => $this->videoGalleryExampleView(),
            'AnalyticsDashboardExample' => $this->analyticsDashboardExampleView(),
            default => '',
        };
    }

    private function videoPlayerExampleView(): string
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

    <div style="max-width: 960px; margin: 0 auto;">
        {{-- Player --}}
        <vz-video
            x-ref="player"
            src="{{ $src }}"
            format="{{ $format }}"
            title="{{ $title }}"
            api-key="{{ config('vizor.api_key') }}"
            style="width: 100%; aspect-ratio: 16/9;"
        ></vz-video>

        {{-- Custom controls --}}
        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: #1e293b; border-radius: 0 0 0.5rem 0.5rem;">
            <button wire:click="togglePlay" style="padding: 0.4rem 1rem; border-radius: 0.375rem; border: none; background: #f43f5e; color: white; cursor: pointer; font-size: 0.85rem;">
                {{ $playing ? 'Pause' : 'Play' }}
            </button>

            <span style="font-size: 0.8rem; color: #94a3b8; font-family: monospace;">
                {{ gmdate('i:s', (int) $currentTime) }} / {{ gmdate('i:s', (int) $duration) }}
            </span>

            <span style="margin-left: auto; font-size: 0.75rem; color: {{ $ready ? '#4ade80' : '#f97316' }};">
                {{ $ready ? 'Ready' : 'Loading...' }}
            </span>
        </div>
    </div>
</div>

BLADE;
    }

    private function videoGalleryExampleView(): string
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

    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem;">
            {{-- Player --}}
            <div>
                <vz-video
                    x-ref="player"
                    src="{{ $activeSrc }}"
                    format="{{ $activeFormat }}"
                    title="{{ $activeTitle }}"
                    api-key="{{ config('vizor.api_key') }}"
                    style="width: 100%; aspect-ratio: 16/9; border-radius: 0.5rem; overflow: hidden;"
                ></vz-video>

                <h3 style="margin-top: 0.75rem; font-size: 1.1rem; color: #e2e8f0;">
                    {{ $activeTitle }}
                </h3>
                <p style="font-size: 0.8rem; color: #94a3b8;">
                    Format: {{ $activeFormat }}
                </p>
            </div>

            {{-- Sidebar gallery --}}
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <h4 style="font-size: 0.9rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">
                    Gallery
                </h4>
                @foreach($items as $index => $item)
                    <button
                        wire:click="selectItem({{ $index }})"
                        style="
                            display: block; width: 100%; text-align: left; padding: 0.75rem;
                            border-radius: 0.5rem; border: 2px solid {{ $activeSrc === $item['src'] ? '#f43f5e' : '#334155' }};
                            background: {{ $activeSrc === $item['src'] ? '#f43f5e22' : '#1e293b' }};
                            color: #e2e8f0; cursor: pointer; transition: border-color 0.2s;
                        "
                    >
                        <div style="font-size: 0.9rem; font-weight: 600;">{{ $item['title'] }}</div>
                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.2rem;">{{ $item['format'] }}</div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

BLADE;
    }

    private function analyticsDashboardExampleView(): string
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

    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem;">
            {{-- Player --}}
            <div>
                <vz-video
                    x-ref="player"
                    src="{{ $src }}"
                    format="{{ $format }}"
                    api-key="{{ config('vizor.api_key') }}"
                    style="width: 100%; aspect-ratio: 16/9; border-radius: 0.5rem; overflow: hidden;"
                ></vz-video>
            </div>

            {{-- Analytics panel --}}
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <h4 style="font-size: 0.9rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">
                    Analytics
                </h4>

                {{-- Stats cards --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div style="background: #1e293b; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #334155;">
                        <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Plays</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #4ade80;">{{ $playCount }}</div>
                    </div>
                    <div style="background: #1e293b; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #334155;">
                        <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Pauses</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #f97316;">{{ $pauseCount }}</div>
                    </div>
                    <div style="background: #1e293b; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #334155;">
                        <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Watch Time</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #38bdf8;">{{ gmdate('i:s', (int) $totalWatchTime) }}</div>
                    </div>
                    <div style="background: #1e293b; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #334155;">
                        <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Status</div>
                        <div style="font-size: 1rem; font-weight: 600; color: {{ $ready ? '#4ade80' : '#f97316' }};">
                            {{ $ready ? ($playing ? 'Playing' : 'Paused') : 'Loading' }}
                        </div>
                    </div>
                </div>

                @if($lastError)
                    <div style="background: #7f1d1d44; border: 1px solid #ef4444; padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.8rem; color: #fca5a5;">
                        {{ $lastError }}
                    </div>
                @endif

                {{-- Event log --}}
                <div style="flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.8rem; color: #94a3b8;">Event Log</span>
                        <button
                            wire:click="clearLog"
                            style="font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 0.25rem; border: 1px solid #475569; background: transparent; color: #94a3b8; cursor: pointer;"
                        >
                            Clear
                        </button>
                    </div>
                    <div style="flex: 1; overflow-y: auto; max-height: 240px; background: #0f172a; border-radius: 0.375rem; padding: 0.5rem; font-family: monospace; font-size: 0.75rem; line-height: 1.8;">
                        @forelse(array_reverse($eventLog) as $entry)
                            <div style="color: #94a3b8;">
                                <span style="color: #64748b;">{{ $entry['timestamp'] }}</span>
                                <span style="color: #38bdf8;">{{ $entry['event'] }}</span>
                                <span style="color: #64748b;">@ {{ $entry['time'] }}s</span>
                            </div>
                        @empty
                            <div style="color: #475569;">No events yet. Press play to start.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

BLADE;
    }
}
