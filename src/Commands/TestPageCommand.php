<?php

namespace Vizor\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TestPageCommand extends Command
{
    protected $signature = 'vizor:test-page {--force : Overwrite existing files}';

    protected $description = 'Create a test page with all Vizor VR player types for local development';

    public function handle(): int
    {
        $routeCreated = $this->createRouteFile();
        $viewCreated = $this->createTestView();

        if (! $routeCreated && ! $viewCreated) {
            $this->warn('No files were created. Use --force to overwrite existing files.');

            return self::FAILURE;
        }

        $this->info('');
        $this->info('  Test page created successfully!');
        $this->comment('  Visit http://your-app.test/vizor-test (local environment only)');
        $this->info('');

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────────────

    private function createRouteFile(): bool
    {
        $path = base_path('routes/vizor-test.php');

        if (File::exists($path) && ! $this->option('force')) {
            $this->warn('  Route file already exists: routes/vizor-test.php (use --force to overwrite)');

            return false;
        }

        $stub = $this->getRouteStub();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);
        $this->info('  Created routes/vizor-test.php');

        return true;
    }

    private function createTestView(): bool
    {
        $path = resource_path('views/vizor-test.blade.php');

        if (File::exists($path) && ! $this->option('force')) {
            $this->warn('  View already exists: resources/views/vizor-test.blade.php (use --force to overwrite)');

            return false;
        }

        $stub = $this->getViewStub();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);
        $this->info('  Created resources/views/vizor-test.blade.php');

        return true;
    }

    // ──────────────────────────── Stubs ────────────────────────────

    private function getRouteStub(): string
    {
        return <<<'PHP'
<?php

/**
 * Vizor VR Player - Test Routes
 *
 * These routes are only registered in the local environment.
 * Remove this file in production or delete it when no longer needed.
 */

use Illuminate\Support\Facades\Route;

if (app()->environment('local')) {
    Route::get('/vizor-test', function () {
        return view('vizor-test');
    })->name('vizor.test');
}

PHP;
    }

    private function getViewStub(): string
    {
        return <<<'BLADE'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vizor VR Player - Test Page</title>
    @vizorScripts
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            padding: 2rem;
        }
        h1 { font-size: 1.75rem; margin-bottom: 0.5rem; }
        .subtitle { color: #94a3b8; margin-bottom: 2rem; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
        }
        .card {
            background: #1e293b;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid #334155;
        }
        .card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #334155;
        }
        .card-header h2 { font-size: 1.1rem; margin-bottom: 0.25rem; }
        .card-header p { font-size: 0.8rem; color: #94a3b8; }
        .card-body { padding: 0; }
        .card-body vz-video,
        .card-body vz-img,
        .card-body vz-tour,
        .card-body vz-cinema,
        .card-body vz-live,
        .card-body vz-playlist { width: 100%; aspect-ratio: 16/9; display: block; }
        .badge {
            display: inline-block;
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            background: #f43f5e33;
            color: #fb7185;
            font-weight: 600;
        }
        .note {
            margin-top: 2rem;
            padding: 1rem 1.25rem;
            background: #1e293b;
            border-radius: 0.5rem;
            border-left: 4px solid #f43f5e;
            color: #94a3b8;
            font-size: 0.85rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <h1>Vizor VR Player</h1>
    <p class="subtitle">Test page &mdash; all player types rendered below. Replace <code>src</code> attributes with your own media URLs.</p>

    <div class="grid">
        {{-- ── Video (360 mono) ──────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h2>vz-video <span class="badge">MONO_360</span></h2>
                <p>Standard 360 video player</p>
            </div>
            <div class="card-body">
                <x-vizor-video
                    src="https://cdn.vizor-vr.com/samples/360-sample.mp4"
                    :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
                    title="Sample 360 Video"
                />
            </div>
        </div>

        {{-- ── Image (360 mono) ──────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h2>vz-img <span class="badge">MONO_360</span></h2>
                <p>360 image viewer</p>
            </div>
            <div class="card-body">
                <x-vizor-img
                    src="https://cdn.vizor-vr.com/samples/360-sample.jpg"
                    :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
                    title="Sample 360 Image"
                />
            </div>
        </div>

        {{-- ── Tour ──────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h2>vz-tour <span class="badge">Tour</span></h2>
                <p>Multi-scene virtual tour</p>
            </div>
            <div class="card-body">
                <x-vizor-tour
                    src="https://cdn.vizor-vr.com/samples/tour-sample.json"
                    title="Sample Tour"
                />
            </div>
        </div>

        {{-- ── Cinema ────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h2>vz-cinema <span class="badge">MONO_FLAT</span></h2>
                <p>Virtual cinema environment</p>
            </div>
            <div class="card-body">
                <x-vizor-cinema
                    src="https://cdn.vizor-vr.com/samples/flat-sample.mp4"
                    :format="\Vizor\Laravel\Support\FormatEnum::MONO_FLAT"
                    title="Sample Cinema"
                />
            </div>
        </div>

        {{-- ── Live ──────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h2>vz-live <span class="badge">MONO_360</span></h2>
                <p>Live 360 stream (HLS/DASH)</p>
            </div>
            <div class="card-body">
                <x-vizor-live
                    src="https://cdn.vizor-vr.com/samples/live-sample.m3u8"
                    :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
                    title="Sample Live Stream"
                />
            </div>
        </div>

        {{-- ── Playlist ──────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h2>vz-playlist <span class="badge">Playlist</span></h2>
                <p>Multi-video playlist with panel</p>
            </div>
            <div class="card-body">
                <x-vizor-playlist>
                    <x-vizor-video
                        src="https://cdn.vizor-vr.com/samples/360-sample.mp4"
                        :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
                        title="Playlist Item 1"
                    />
                    <x-vizor-video
                        src="https://cdn.vizor-vr.com/samples/flat-sample.mp4"
                        :format="\Vizor\Laravel\Support\FormatEnum::MONO_FLAT"
                        title="Playlist Item 2"
                    />
                </x-vizor-playlist>
            </div>
        </div>
    </div>

    <div class="note">
        <strong>Note:</strong> This test page is only accessible in the <code>local</code> environment.
        Replace the sample URLs above with your own media files.
        Run <code>php artisan vizor:test-page --force</code> to regenerate this page at any time.
    </div>
</body>
</html>

BLADE;
    }
}
