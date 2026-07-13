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

    it('has default player_version of 0.2.0', function () {
        expect($this->rawConfig['player_version'])->toBe('0.2.0');
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

    it('has default primary_color of #f43f5e', function () {
        expect($this->rawConfig['primary_color'])->toBe('#f43f5e');
    });

    it('has default default_format of MONO_360', function () {
        expect($this->rawConfig['default_format'])->toBe('MONO_360');
    });

    it('has default default_controls of true', function () {
        expect($this->rawConfig['default_controls'])->toBeTrue();
    });

    it('has default default_muted of false', function () {
        expect($this->rawConfig['default_muted'])->toBeFalse();
    });

    it('has broadcasting channel_prefix of vizor', function () {
        expect($this->rawConfig['broadcasting']['channel_prefix'])->toBe('vizor');
    });
});
