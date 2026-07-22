<?php

return [
    // ──────────────────────────── Vizor API ────────────────────────────
    'api_url' => env('VIZOR_API_URL', 'https://api.vizor-vr.com'),
    'api_key' => env('VIZOR_API_KEY'),

    // ──────────────────────────── License ────────────────────────────
    'license_key' => env('VIZOR_LICENSE_KEY'),
    'license_mode' => env('VIZOR_LICENSE_MODE', 'saas'), // 'saas' or 'standalone'

    // ──────────────────────────── Player CDN ────────────────────────────
    // Null = derived from player_version (never @latest — a player publish
    // must not silently change what production pages load).
    'cdn_url' => env('VIZOR_CDN_URL'),
    'player_version' => env('VIZOR_PLAYER_VERSION', '0.4.0'),
    'use_local_assets' => env('VIZOR_USE_LOCAL_ASSETS', false),

    // ---------------------------- Auto-inject ----------------------------
    // When true, the InjectVizorAssets middleware inserts the pinned player
    // <script> before </head> on every HTML response. Default OFF -- prefer
    // @vizorScripts on the pages that actually embed a player.
    'auto_inject' => env('VIZOR_AUTO_INJECT', false),

    // ──────────────────────────── License Validation ────────────────────────────
    'validate_license' => env('VIZOR_VALIDATE_LICENSE', true),
    'license_cache_ttl' => (int) env('VIZOR_LICENSE_CACHE_TTL', 3600),

    // Resolved at request time by ValidateVizorLicense; 'free' until validated.
    'license_tier' => 'free',

    // ──────────────────────────── Broadcasting (optional) ────────────────────────────
    'broadcasting' => [
        'enabled' => env('VIZOR_BROADCASTING', false),
        'channel_prefix' => 'vizor',
    ],

    // ──────────────────────────── Filament (optional) ────────────────────────────
    'filament' => [
        'enabled' => env('VIZOR_FILAMENT', false),
        'navigation_group' => 'Vizor',
    ],
];
