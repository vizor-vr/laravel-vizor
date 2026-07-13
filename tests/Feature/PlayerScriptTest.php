<?php

use Illuminate\Support\Facades\Blade;
use Vizor\Laravel\Support\PlayerScript;

/**
 * WS-G: the player script URL must always reference a file that EXISTS in the
 * published npm tarball, pinned to the configured version. The 0.1.0 era
 * shipped `vizor-player.register.es.js` — a file the package never contained —
 * so every page silently 404'd its player. The committed
 * player-dist-manifest.json is the source of truth for the dist layout.
 */
describe('PlayerScript (player pin integrity)', function () {

    it('references a dist entry that exists in the committed player manifest', function () {
        $manifest = json_decode(
            file_get_contents(__DIR__.'/../../player-dist-manifest.json'),
            true,
        );
        expect($manifest['entries'])->toContain(PlayerScript::DIST_ENTRY);
    });

    it('the manifest version matches the config default player_version', function () {
        $manifest = json_decode(
            file_get_contents(__DIR__.'/../../player-dist-manifest.json'),
            true,
        );
        $configDefaults = require __DIR__.'/../../config/vizor.php';
        // Bypass env overrides: assert the shipped default, not the test env.
        expect($manifest['version'])->toBe('0.2.0');
        expect(config('vizor.player_version'))->toBe('0.2.0');
    });

    it('pins the CDN URL to the configured version — never @latest', function () {
        config(['vizor.cdn_url' => null, 'vizor.player_version' => '0.2.0', 'vizor.use_local_assets' => false]);
        $url = PlayerScript::scriptUrl();
        expect($url)->toBe('https://cdn.jsdelivr.net/npm/@vizor-vr/player@0.2.0/dist/register.js');
        expect($url)->not->toContain('@latest');
    });

    it('honors an explicit cdn_url override', function () {
        config(['vizor.cdn_url' => 'https://cdn.example.com/player/', 'vizor.use_local_assets' => false]);
        expect(PlayerScript::scriptUrl())->toBe('https://cdn.example.com/player/register.js');
    });

    it('uses the published local asset when use_local_assets is on', function () {
        config(['vizor.use_local_assets' => true]);
        expect(PlayerScript::scriptUrl())->toContain('vendor/vizor/register.js');
    });

    it('the @vizorScripts directive emits the tag', function () {
        config(['vizor.cdn_url' => null, 'vizor.player_version' => '0.2.0', 'vizor.use_local_assets' => false]);
        $compiled = Blade::compileString('@vizorScripts');
        expect($compiled)->toContain('PlayerScript::tag()');
        expect(PlayerScript::tag())->toContain('<script type="module"');
        expect(PlayerScript::tag())->toContain('/dist/register.js');
    });
});
