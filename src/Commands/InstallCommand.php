<?php

namespace Vizor\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'vizor:install {--force : Overwrite existing files}';

    protected $description = 'Install the Vizor VR player package into your Laravel application';

    public function handle(): int
    {
        $this->info('');
        $this->info('  Vizor VR Player - Installation');
        $this->info('  ==============================');
        $this->info('');

        // ── 1. Publish config ────────────────────────────────────────────
        $this->publishConfig();

        // ── 2. Publish Alpine.js plugin ──────────────────────────────────
        $this->publishAlpinePlugin();

        // ── 3. Prompt for API key ────────────────────────────────────────
        $this->promptForApiKey();

        // ── 4. Generate test page ────────────────────────────────────────
        $this->info('');
        $this->comment('Generating test page...');
        $this->call('vizor:test-page', [
            '--force' => $this->option('force'),
        ]);

        // ── Done ─────────────────────────────────────────────────────────
        $this->info('');
        $this->info('  Vizor installed successfully!');
        $this->info('');
        $this->comment('  Next steps:');
        $this->comment('    1. Add Alpine.js plugin to your app.js:');
        $this->comment('       import vizorAlpine from \'./vizor-alpine.js\';');
        $this->comment('       Alpine.plugin(vizorAlpine);');
        $this->comment('    2. Visit /vizor-test to verify the player works');
        $this->comment('    3. Run `php artisan vizor:examples` to publish example components');
        $this->info('');

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────────────

    private function publishConfig(): void
    {
        $source = dirname(__DIR__, 2).'/config/vizor.php';
        $destination = config_path('vizor.php');

        if (File::exists($destination) && ! $this->option('force')) {
            $this->warn('  Config file already exists: config/vizor.php (use --force to overwrite)');

            return;
        }

        File::ensureDirectoryExists(dirname($destination));
        File::copy($source, $destination);
        $this->info('  Published config/vizor.php');
    }

    private function publishAlpinePlugin(): void
    {
        $source = dirname(__DIR__, 2).'/resources/js/vizor-alpine.js';
        $destination = resource_path('js/vizor-alpine.js');

        if (File::exists($destination) && ! $this->option('force')) {
            $this->warn('  Alpine plugin already exists: resources/js/vizor-alpine.js (use --force to overwrite)');

            return;
        }

        File::ensureDirectoryExists(dirname($destination));
        File::copy($source, $destination);
        $this->info('  Published resources/js/vizor-alpine.js');
    }

    private function promptForApiKey(): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $this->warn('  No .env file found -- skipping API key setup');

            return;
        }

        $envContents = File::get($envPath);

        // Check if VIZOR_API_KEY already exists in .env
        if (preg_match('/^VIZOR_API_KEY=/m', $envContents)) {
            $this->comment('  VIZOR_API_KEY already set in .env');

            return;
        }

        $apiKey = $this->ask('Enter your Vizor API key (leave blank to skip)');

        if (empty($apiKey)) {
            $this->comment('  Skipped API key -- you can add VIZOR_API_KEY to .env later');

            return;
        }

        // Append to .env
        $separator = str_ends_with(trim($envContents), "\n") ? '' : "\n";
        File::append($envPath, $separator."\n# Vizor VR Player\nVIZOR_API_KEY={$apiKey}\n");
        $this->info('  Saved VIZOR_API_KEY to .env');
    }
}
