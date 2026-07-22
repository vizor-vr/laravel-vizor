<?php

describe('vizor config defaults', function () {
    /**
     * Load the raw config array from the package config file
     * so we test the shipped defaults, not the TestCase overrides.
     */
    beforeEach(function () {
        $this->rawConfig = require __DIR__.'/../../config/vizor.php';
    });

    it('has default api_url of https://api.vizor-vr.com', function () {
        expect($this->rawConfig['api_url'])->toBe('https://api.vizor-vr.com');
    });

    it('has a null cdn_url default (derived from player_version, never @latest)', function () {
        expect($this->rawConfig['cdn_url'])->toBeNull();
    });

    it('has default player_version of 0.3.0', function () {
        expect($this->rawConfig['player_version'])->toBe('0.3.0');
    });

    it('has default license_mode of saas', function () {
        expect($this->rawConfig['license_mode'])->toBe('saas');
    });

    it('has default validate_license of true', function () {
        expect($this->rawConfig['validate_license'])->toBeTrue();
    });

    it('has default license_cache_ttl as integer 3600', function () {
        expect($this->rawConfig['license_cache_ttl'])->toBe(3600);
        expect($this->rawConfig['license_cache_ttl'])->toBeInt();
    });

    it('has default broadcasting.enabled of false', function () {
        expect($this->rawConfig['broadcasting']['enabled'])->toBeFalse();
    });

    it('has default filament.enabled of false', function () {
        expect($this->rawConfig['filament']['enabled'])->toBeFalse();
    });

    it('ships no player-default or theming keys (props are the only knobs)', function () {
        // default_format / default_controls / default_muted / primary_color /
        // brand_name / brand_logo were documented but never read by any
        // rendering path — removed pre-1.0.
        expect($this->rawConfig)
            ->not->toHaveKey('default_format')
            ->not->toHaveKey('default_controls')
            ->not->toHaveKey('default_muted')
            ->not->toHaveKey('primary_color')
            ->not->toHaveKey('brand_name')
            ->not->toHaveKey('brand_logo');
    });

    it('README documents the shipped player_version default', function () {
        // Guards against the README table drifting from the config default when
        // sync-player-version.yml bumps the pin (it drifted 0.1.0 vs 0.3.0 once).
        // Self-referential on purpose: no hardcoded version to keep in sync.
        $readme = file_get_contents(__DIR__.'/../../README.md');

        expect($readme)->toContain(
            "| `player_version` | `VIZOR_PLAYER_VERSION` | `{$this->rawConfig['player_version']}`"
        );
    });

    it('has broadcasting channel_prefix of vizor', function () {
        expect($this->rawConfig['broadcasting']['channel_prefix'])->toBe('vizor');
    });
});
