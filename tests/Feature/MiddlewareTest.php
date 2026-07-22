<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Middleware\ValidateVizorLicense;

// ──────────────────────────── Bypass / Passthrough ────────────────────────────

it('passes through when validation is disabled', function () {
    config(['vizor.validate_license' => false]);

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $response = $middleware->handle($request, fn ($r) => new Response('ok'));

    expect($response->getContent())->toBe('ok');
});

it('always calls next middleware regardless of validation outcome', function () {
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'saas',
        'vizor.api_key' => 'bad-key',
    ]);

    // API returns invalid
    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => false], 200),
    ]);

    Cache::forget('vizor_license_status');

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $response = $middleware->handle($request, fn ($r) => new Response('next-called'));

    expect($response->getContent())->toBe('next-called');
});

// ──────────────────────────── SaaS Mode ────────────────────────────

it('passes through in saas mode with valid API key', function () {
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'saas',
        'vizor.api_key' => 'valid-saas-key',
    ]);

    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => true, 'tier' => 'pro'], 200),
    ]);

    Cache::forget('vizor_license_status');

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $response = $middleware->handle($request, fn ($r) => new Response('ok'));

    expect($response->getContent())->toBe('ok');
    expect(config('vizor.license_tier'))->toBe('pro');
});

it('degrades to free tier in saas mode with invalid API key', function () {
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'saas',
        'vizor.api_key' => 'invalid-key',
    ]);

    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => false], 200),
    ]);

    Cache::forget('vizor_license_status');

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $middleware->handle($request, fn ($r) => new Response('ok'));

    expect(config('vizor.license_tier'))->toBe('free');
});

// ──────────────────────────── Standalone Mode ────────────────────────────

it('validates license key in standalone mode', function () {
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'standalone',
        'vizor.license_key' => 'valid-standalone-key',
    ]);

    Http::fake([
        '*/api/v1/license/validate-standalone' => Http::response(['valid' => true, 'tier' => 'pro'], 200),
    ]);

    Cache::forget('vizor_license_status');

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $response = $middleware->handle($request, fn ($r) => new Response('ok'));

    expect($response->getContent())->toBe('ok');
    expect(config('vizor.license_tier'))->toBe('pro');
});

it('degrades to free tier in standalone mode with invalid license key', function () {
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'standalone',
        'vizor.license_key' => 'bad-license',
    ]);

    Http::fake([
        '*/api/v1/license/validate-standalone' => Http::response(['valid' => false], 200),
    ]);

    Cache::forget('vizor_license_status');

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $middleware->handle($request, fn ($r) => new Response('ok'));

    expect(config('vizor.license_tier'))->toBe('free');
});

// ──────────────────────────── Caching ────────────────────────────

it('caches the validation result', function () {
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'saas',
        'vizor.api_key' => 'cached-key',
        'vizor.license_cache_ttl' => 3600,
    ]);

    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => true, 'tier' => 'pro'], 200),
    ]);

    Cache::forget('vizor_license_status');

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');

    // First call -- should hit the API and cache the result
    $middleware->handle($request, fn ($r) => new Response('ok'));

    expect(Cache::has('vizor_license_status'))->toBeTrue();
    expect(Cache::get('vizor_license_status'))->toBe(['valid' => true, 'tier' => 'pro']);
});

it('uses cached result and does not call API again', function () {
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'saas',
        'vizor.api_key' => 'cached-key',
        'vizor.license_cache_ttl' => 3600,
    ]);

    // Pre-populate cache with a valid result
    Cache::put('vizor_license_status', ['valid' => true, 'tier' => 'pro'], 3600);

    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => true, 'tier' => 'pro'], 200),
    ]);

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $middleware->handle($request, fn ($r) => new Response('ok'));

    // No HTTP calls should have been made because cache was pre-populated
    Http::assertNothingSent();
});

it('does not read a stale bool cached under the old key name', function () {
    // The cache key changed from vizor_license_valid to vizor_license_status
    // because the cached shape changed bool -> array{valid, tier}. A value
    // left behind under the old key must never be read as the new array --
    // this seeds the old key with a stale `true` and confirms the middleware
    // ignores it and validates fresh under the new key instead.
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'saas',
        'vizor.api_key' => 'k',
    ]);

    Cache::put('vizor_license_valid', true, 3600);

    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => false], 200),
    ]);

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $middleware->handle($request, fn ($r) => new Response('ok'));

    expect(config('vizor.license_tier'))->toBe('free');
});

// ──────────────────────────── Error Handling ────────────────────────────

it('degrades gracefully when validation throws an exception', function () {
    config([
        'vizor.validate_license' => true,
        'vizor.license_mode' => 'saas',
        'vizor.api_key' => 'error-key',
    ]);

    // Simulate a network error
    Http::fake([
        '*/api/v1/license/validate' => Http::response(null, 500),
    ]);

    Cache::forget('vizor_license_status');

    $middleware = new ValidateVizorLicense;
    $request = Request::create('/test');
    $response = $middleware->handle($request, fn ($r) => new Response('ok'));

    // Should still call next middleware but degrade to free tier
    expect($response->getContent())->toBe('ok');
    expect(config('vizor.license_tier'))->toBe('free');
});

// ──────────────────────────── Middleware Registration ────────────────────────────

it('is registered under the vizor.license alias', function () {
    $router = app('router');
    $middleware = $router->getMiddleware();

    expect($middleware)->toHaveKey('vizor.license');
    expect($middleware['vizor.license'])->toBe(ValidateVizorLicense::class);
});

// ──────────────────────────── License Tier ────────────────────────────

it('sets vizor.license_tier to the validated tier on success', function () {
    config(['vizor.validate_license' => true, 'vizor.license_mode' => 'saas', 'vizor.api_key' => 'k']);
    Cache::flush();
    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => true, 'tier' => 'pro'], 200),
    ]);

    $middleware = new ValidateVizorLicense;
    $middleware->handle(request(), fn ($r) => response('ok'));

    expect(config('vizor.license_tier'))->toBe('pro');
});

it('sets vizor.license_tier to free on failure', function () {
    config(['vizor.validate_license' => true, 'vizor.license_mode' => 'saas', 'vizor.api_key' => 'k']);
    Cache::flush();
    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => false, 'tier' => 'free'], 401),
    ]);

    $middleware = new ValidateVizorLicense;
    $middleware->handle(request(), fn ($r) => response('ok'));

    expect(config('vizor.license_tier'))->toBe('free');
});
