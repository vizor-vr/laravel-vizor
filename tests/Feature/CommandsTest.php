<?php

use Illuminate\Support\Facades\File;

// ──────────────────────────── vizor:install ────────────────────────────

describe('vizor:install', function () {
    it('is registered as an artisan command', function () {
        $this->artisan('vizor:install', ['--no-interaction' => true])
            ->assertSuccessful();
    });

    it('publishes the config file', function () {
        $configPath = config_path('vizor.php');

        // Clean up if it exists from a prior test
        if (File::exists($configPath)) {
            File::delete($configPath);
        }

        $this->artisan('vizor:install', ['--no-interaction' => true])
            ->assertSuccessful();

        expect(File::exists($configPath))->toBeTrue();

        // Cleanup
        File::delete($configPath);
    });

    it('publishes the alpine.js plugin file', function () {
        $alpinePath = resource_path('js/vizor-alpine.js');

        if (File::exists($alpinePath)) {
            File::delete($alpinePath);
        }

        $this->artisan('vizor:install', ['--no-interaction' => true])
            ->assertSuccessful();

        expect(File::exists($alpinePath))->toBeTrue();

        // Cleanup
        File::delete($alpinePath);
    });

    it('overwrites existing files with --force flag', function () {
        $configPath = config_path('vizor.php');

        // Create a dummy config file
        File::ensureDirectoryExists(dirname($configPath));
        File::put($configPath, '<?php return ["dummy" => true];');

        $this->artisan('vizor:install', ['--force' => true, '--no-interaction' => true])
            ->assertSuccessful();

        $contents = File::get($configPath);
        expect($contents)->not->toContain('"dummy" => true');

        // Cleanup
        File::delete($configPath);
    });

    it('prompts for API key during install', function () {
        // Create a .env file without VIZOR_API_KEY
        $envPath = base_path('.env');
        $hadEnv = File::exists($envPath);
        $originalContents = $hadEnv ? File::get($envPath) : null;

        File::put($envPath, "APP_NAME=Test\n");

        $this->artisan('vizor:install')
            ->expectsQuestion('Enter your Vizor API key (leave blank to skip)', 'test-key-from-prompt')
            ->assertSuccessful();

        $envContents = File::get($envPath);
        expect($envContents)->toContain('VIZOR_API_KEY=test-key-from-prompt');

        // Restore original .env
        if ($hadEnv && $originalContents !== null) {
            File::put($envPath, $originalContents);
        } else {
            File::delete($envPath);
        }
    });
});

// ──────────────────────────── vizor:component ────────────────────────────

describe('vizor:component', function () {
    it('generates a Livewire class file', function () {
        $classPath = app_path('Livewire/MyVrPlayer.php');

        if (File::exists($classPath)) {
            File::delete($classPath);
        }

        $this->artisan('vizor:component', ['name' => 'my-vr-player'])
            ->assertSuccessful();

        expect(File::exists($classPath))->toBeTrue();

        $contents = File::get($classPath);
        expect($contents)->toContain('class MyVrPlayer extends Component');

        // Cleanup
        File::delete($classPath);
    });

    it('generates a Blade view file', function () {
        $viewPath = resource_path('views/livewire/my-vr-player.blade.php');

        if (File::exists($viewPath)) {
            File::delete($viewPath);
        }

        $this->artisan('vizor:component', ['name' => 'my-vr-player'])
            ->assertSuccessful();

        expect(File::exists($viewPath))->toBeTrue();

        $contents = File::get($viewPath);
        expect($contents)->toContain('vz-video');

        // Cleanup
        File::delete($viewPath);
    });

    it('rejects invalid component names', function () {
        $this->artisan('vizor:component', ['name' => '123invalid'])
            ->assertFailed();
    });

    it('rejects reserved component names', function () {
        $this->artisan('vizor:component', ['name' => 'video-player'])
            ->assertFailed();
    });

    it('overwrites existing files with --force flag', function () {
        $classPath = app_path('Livewire/ForceTest.php');
        $viewPath = resource_path('views/livewire/force-test.blade.php');

        // Create dummy files
        File::ensureDirectoryExists(dirname($classPath));
        File::put($classPath, '<?php // dummy');
        File::ensureDirectoryExists(dirname($viewPath));
        File::put($viewPath, 'dummy');

        $this->artisan('vizor:component', ['name' => 'force-test', '--force' => true])
            ->assertSuccessful();

        $contents = File::get($classPath);
        expect($contents)->toContain('class ForceTest extends Component');

        // Cleanup
        File::delete($classPath);
        File::delete($viewPath);
    });
});

// ──────────────────────────── vizor:test-page ────────────────────────────

describe('vizor:test-page', function () {
    it('creates a route file', function () {
        $routePath = base_path('routes/vizor-test.php');

        if (File::exists($routePath)) {
            File::delete($routePath);
        }

        $this->artisan('vizor:test-page')
            ->assertSuccessful();

        expect(File::exists($routePath))->toBeTrue();

        $contents = File::get($routePath);
        expect($contents)->toContain('vizor-test');
        expect($contents)->toContain('Route::get');

        // Cleanup
        File::delete($routePath);
    });

    it('creates a view file', function () {
        $viewPath = resource_path('views/vizor-test.blade.php');

        if (File::exists($viewPath)) {
            File::delete($viewPath);
        }

        $this->artisan('vizor:test-page')
            ->assertSuccessful();

        expect(File::exists($viewPath))->toBeTrue();

        $contents = File::get($viewPath);
        expect($contents)->toContain('Vizor VR Player');

        // Cleanup
        File::delete($viewPath);
    });
});

// ──────────────────────────── vizor:examples ────────────────────────────

describe('vizor:examples', function () {
    it('publishes 3 example component classes', function () {
        $examples = [
            'VideoPlayerExample',
            'VideoGalleryExample',
            'AnalyticsDashboardExample',
        ];

        foreach ($examples as $name) {
            $path = app_path("Livewire/{$name}.php");
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $this->artisan('vizor:examples')
            ->assertSuccessful();

        foreach ($examples as $name) {
            $path = app_path("Livewire/{$name}.php");
            expect(File::exists($path))->toBeTrue("Expected {$name}.php to exist");
        }

        // Cleanup
        foreach ($examples as $name) {
            File::delete(app_path("Livewire/{$name}.php"));
        }
    });

    it('publishes corresponding blade views', function () {
        $views = [
            'video-player-example',
            'video-gallery-example',
            'analytics-dashboard-example',
        ];

        foreach ($views as $name) {
            $path = resource_path("views/livewire/{$name}.blade.php");
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $this->artisan('vizor:examples')
            ->assertSuccessful();

        foreach ($views as $name) {
            $path = resource_path("views/livewire/{$name}.blade.php");
            expect(File::exists($path))->toBeTrue("Expected {$name}.blade.php to exist");
        }

        // Cleanup
        foreach ($views as $name) {
            File::delete(resource_path("views/livewire/{$name}.blade.php"));
        }
    });

    it('overwrites existing files with --force flag', function () {
        $path = app_path('Livewire/VideoPlayerExample.php');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, '<?php // old dummy');

        $this->artisan('vizor:examples', ['--force' => true])
            ->assertSuccessful();

        $contents = File::get($path);
        expect($contents)->toContain('class VideoPlayerExample extends Component');

        // Cleanup
        $examples = ['VideoPlayerExample', 'VideoGalleryExample', 'AnalyticsDashboardExample'];
        foreach ($examples as $name) {
            $classPath = app_path("Livewire/{$name}.php");
            if (File::exists($classPath)) {
                File::delete($classPath);
            }
        }
        $views = ['video-player-example', 'video-gallery-example', 'analytics-dashboard-example'];
        foreach ($views as $name) {
            $viewPath = resource_path("views/livewire/{$name}.blade.php");
            if (File::exists($viewPath)) {
                File::delete($viewPath);
            }
        }
    });
});
