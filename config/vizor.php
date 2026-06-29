<?php

return [
    // ──────────────────────────── Vizor API ────────────────────────────
    'api_url' => env('VIZOR_API_URL', 'https://api.vizor-vr.com'),
    'api_key' => env('VIZOR_API_KEY'),

    // ──────────────────────────── License ────────────────────────────
    'license_key' => env('VIZOR_LICENSE_KEY'),
    'license_mode' => env('VIZOR_LICENSE_MODE', 'saas'), // 'saas' or 'standalone'

    // ──────────────────────────── Player CDN ────────────────────────────
    'cdn_url' => env('VIZOR_CDN_URL', 'https://cdn.jsdelivr.net/npm/@vizor-vr/player@latest/dist'),
    'player_version' => env('VIZOR_PLAYER_VERSION', '0.1.0'),
    'use_local_assets' => env('VIZOR_USE_LOCAL_ASSETS', false),

    // ──────────────────────────── Defaults ────────────────────────────
    'default_format' => 'MONO_360',
    'default_controls' => true,
    'default_muted' => false,

    // ──────────────────────────── Theming ────────────────────────────
    'primary_color' => env('VIZOR_PRIMARY_COLOR', '#f43f5e'),
    'brand_name' => env('VIZOR_BRAND_NAME'),
    'brand_logo' => env('VIZOR_BRAND_LOGO'),

    // ──────────────────────────── License Validation ────────────────────────────
    'validate_license' => env('VIZOR_VALIDATE_LICENSE', true),
    'license_cache_ttl' => (int) env('VIZOR_LICENSE_CACHE_TTL', 3600),

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
