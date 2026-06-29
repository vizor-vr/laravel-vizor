<?php

use Vizor\Laravel\Support\FormatEnum;
use Vizor\Laravel\Traits\HasVizorProps;

/**
 * Minimal test stub that uses the HasVizorProps trait.
 * Mimics the public properties found on Livewire components.
 */
function createPropsStub(array $overrides = []): object
{
    return new class($overrides)
    {
        use HasVizorProps;

        public ?string $src = null;
        public ?FormatEnum $format = null;
        public ?string $title = null;
        public ?string $poster = null;
        public ?string $apiKey = null;
        public ?string $licenseKey = null;
        public ?string $apiEndpoint = null;
        public ?string $primaryColor = null;
        public ?string $contentId = null;

        public function __construct(array $overrides)
        {
            foreach ($overrides as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        // Expose protected methods for testing
        public function testResolvedApiKey(): ?string
        {
            return $this->resolvedApiKey();
        }

        public function testResolvedLicenseKey(): ?string
        {
            return $this->resolvedLicenseKey();
        }

        public function testResolvedPrimaryColor(): ?string
        {
            return $this->resolvedPrimaryColor();
        }

        public function testResolvedApiEndpoint(): ?string
        {
            return $this->resolvedApiEndpoint();
        }

        public function testVizorProps(): array
        {
            return $this->vizorProps();
        }
    };
}

describe('HasVizorProps trait', function () {
    it('resolvedApiKey() falls back to config value when prop is null', function () {
        config()->set('vizor.api_key', 'config-api-key-xyz');

        $stub = createPropsStub();

        expect($stub->testResolvedApiKey())->toBe('config-api-key-xyz');
    });

    it('resolvedApiKey() prefers instance property over config', function () {
        config()->set('vizor.api_key', 'config-api-key');

        $stub = createPropsStub(['apiKey' => 'instance-api-key']);

        expect($stub->testResolvedApiKey())->toBe('instance-api-key');
    });

    it('resolvedLicenseKey() falls back to config value when prop is null', function () {
        config()->set('vizor.license_key', 'config-license-key-abc');

        $stub = createPropsStub();

        expect($stub->testResolvedLicenseKey())->toBe('config-license-key-abc');
    });

    it('resolvedLicenseKey() prefers instance property over config', function () {
        config()->set('vizor.license_key', 'config-license-key');

        $stub = createPropsStub(['licenseKey' => 'instance-license-key']);

        expect($stub->testResolvedLicenseKey())->toBe('instance-license-key');
    });

    it('resolvedPrimaryColor() falls back to config value when prop is null', function () {
        config()->set('vizor.primary_color', '#00ff00');

        $stub = createPropsStub();

        expect($stub->testResolvedPrimaryColor())->toBe('#00ff00');
    });

    it('resolvedPrimaryColor() prefers instance property over config', function () {
        config()->set('vizor.primary_color', '#00ff00');

        $stub = createPropsStub(['primaryColor' => '#ff0000']);

        expect($stub->testResolvedPrimaryColor())->toBe('#ff0000');
    });

    it('resolvedApiEndpoint() falls back to config vizor.api_url', function () {
        config()->set('vizor.api_url', 'https://custom-api.example.com');

        $stub = createPropsStub();

        expect($stub->testResolvedApiEndpoint())->toBe('https://custom-api.example.com');
    });

    it('vizorProps() returns filtered array without null values', function () {
        config()->set('vizor.api_key', 'cfg-key');
        config()->set('vizor.license_key', 'cfg-license');
        config()->set('vizor.primary_color', '#f43f5e');

        $stub = createPropsStub([
            'src' => 'video.mp4',
            'title' => null,    // should be omitted
            'poster' => null,   // should be omitted
        ]);

        $props = $stub->testVizorProps();

        expect($props)->toHaveKey('src');
        expect($props['src'])->toBe('video.mp4');
        expect($props)->toHaveKey('apiKey');
        expect($props)->toHaveKey('licenseKey');
        expect($props)->toHaveKey('primaryColor');
        expect($props)->not->toHaveKey('title');
        expect($props)->not->toHaveKey('poster');
        expect($props)->not->toHaveKey('contentId');
    });

    it('vizorProps() includes all non-null resolved values', function () {
        config()->set('vizor.api_key', 'k1');
        config()->set('vizor.license_key', 'k2');
        config()->set('vizor.primary_color', '#aaa');

        $stub = createPropsStub([
            'src' => 'https://example.com/v.mp4',
            'format' => FormatEnum::STEREO_360_LR,
            'contentId' => 'content-123',
        ]);

        $props = $stub->testVizorProps();

        expect($props)->toHaveKey('src');
        expect($props)->toHaveKey('format');
        expect($props)->toHaveKey('apiKey');
        expect($props)->toHaveKey('licenseKey');
        expect($props)->toHaveKey('primaryColor');
        expect($props)->toHaveKey('contentId');
        expect($props['contentId'])->toBe('content-123');
        expect($props['format'])->toBe(FormatEnum::STEREO_360_LR);
    });
});
