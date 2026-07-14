<?php

namespace Vizor\Laravel\Support;

/**
 * Builds the <script> tag(s) that load the Vizor player (WS-G).
 *
 * The bundle entry is `dist/register.js` — the self-registering ES module the
 * npm package actually ships (see player-dist-manifest.json, which a Pest test
 * locks this constant against). The CDN URL is ALWAYS pinned to the configured
 * player_version; `@latest` is deliberately unsupported so a player publish can
 * never silently change what production pages load.
 */
final class PlayerScript
{
    /** The self-registering ES-module entry inside the player's dist/. */
    public const DIST_ENTRY = 'register.js';

    public static function cdnUrl(): string
    {
        $configured = config('vizor.cdn_url');
        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/');
        }

        $version = (string) config('vizor.player_version', '0.2.1');

        return "https://cdn.jsdelivr.net/npm/@vizor-vr/player@{$version}/dist";
    }

    public static function scriptUrl(): string
    {
        if (config('vizor.use_local_assets', false)) {
            return asset('vendor/vizor/'.self::DIST_ENTRY);
        }

        return self::cdnUrl().'/'.self::DIST_ENTRY;
    }

    public static function tag(): string
    {
        return '<script type="module" src="'.e(self::scriptUrl()).'"></script>';
    }
}
