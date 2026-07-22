<?php

use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\AnalyticsApi;
use Vizor\Laravel\Api\ApiKeysApi;
use Vizor\Laravel\Api\Client;
use Vizor\Laravel\Api\LicenseKeysApi;

/**
 * Contract tests: every Api class must hit the REAL Vizor API paths.
 *
 * Http::preventStrayRequests() makes any request to an unfaked (= wrong)
 * URL throw -- but the Api classes here swallow every exception internally,
 * so a regressed path never bubbles up as a loud failure. Instead it shows
 * up as a plain assertion failure on the happy-path tests below (expected
 * valid=true / a real tier, got the "unreachable" fallback instead). That's
 * why every endpoint needs at least one happy-path test against its exact
 * URL -- a wildcard fake would hide the regression entirely.
 */
function makeClient(): Client
{
    return new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-key');
}

describe('Api endpoint paths', function () {

    it('validates SaaS API keys against POST /api/v1/license/validate with apiKey and domain', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate' => Http::response([
                'valid' => true, 'tier' => 'pro', 'features' => [],
            ], 200),
        ]);

        $result = (new ApiKeysApi(makeClient()))->validate('vz_live_abc', 'example.com');

        expect($result)->toBeTrue();
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/license/validate'
            && $request->method() === 'POST'
            && $request['apiKey'] === 'vz_live_abc'
            && $request['domain'] === 'example.com'
        );
    });

    it('returns false when the validate endpoint rejects the key (401)', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate' => Http::response([
                'valid' => false, 'tier' => 'free', 'message' => 'Invalid API key',
            ], 401),
        ]);

        expect((new ApiKeysApi(makeClient()))->validate('bad-key', 'example.com'))->toBeFalse();
    });

    it('derives the default domain from app.url when no domain is given', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate' => Http::response([
                'valid' => true, 'tier' => 'pro',
            ], 200),
        ]);

        // No $domain argument -- this is how the middleware calls it in production.
        $result = (new ApiKeysApi(makeClient()))->validate('vz_live_abc');

        expect($result)->toBeTrue();
        Http::assertSent(fn ($request) => $request['apiKey'] === 'vz_live_abc'
            && $request['domain'] === 'localhost' // Testbench's app.url defaults to http://localhost
        );
    });

    it('validateDetailed() returns the full valid/tier result', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate' => Http::response([
                'valid' => true, 'tier' => 'pro',
            ], 200),
        ]);

        $result = (new ApiKeysApi(makeClient()))->validateDetailed('vz_live_abc', 'example.com');

        expect($result)->toBe(['valid' => true, 'tier' => 'pro']);
    });

    it('validateDetailed() defaults tier to free when the response omits it', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate' => Http::response([
                'valid' => true,
            ], 200),
        ]);

        $result = (new ApiKeysApi(makeClient()))->validateDetailed('vz_live_abc', 'example.com');

        expect($result)->toBe(['valid' => true, 'tier' => 'free']);
    });

    it('validates standalone keys against POST /api/v1/license/validate-standalone', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate-standalone' => Http::response([
                'valid' => true, 'tier' => 'enterprise', 'features' => [],
            ], 200),
        ]);

        $result = (new LicenseKeysApi(makeClient()))->validate('VZR-XXXX', 'example.com');

        expect($result)->toBeTrue();
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/license/validate-standalone'
            && $request->method() === 'POST'
            && $request['licenseKey'] === 'VZR-XXXX'
            && $request['domain'] === 'example.com'
        );
    });

    it('derives the default domain from app.url when no domain is given for standalone keys', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate-standalone' => Http::response([
                'valid' => true, 'tier' => 'enterprise',
            ], 200),
        ]);

        // No $domain argument -- this is how the middleware calls it in production.
        $result = (new LicenseKeysApi(makeClient()))->validate('VZR-XXXX');

        expect($result)->toBeTrue();
        Http::assertSent(fn ($request) => $request['licenseKey'] === 'VZR-XXXX'
            && $request['domain'] === 'localhost' // Testbench's app.url defaults to http://localhost
        );
    });

    it('validateDetailed() returns the full valid/tier result for standalone keys', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate-standalone' => Http::response([
                'valid' => true, 'tier' => 'enterprise',
            ], 200),
        ]);

        $result = (new LicenseKeysApi(makeClient()))->validateDetailed('VZR-XXXX', 'example.com');

        expect($result)->toBe(['valid' => true, 'tier' => 'enterprise']);
    });

    it('validateDetailed() defaults tier to free when the response omits it for standalone keys', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate-standalone' => Http::response([
                'valid' => true,
            ], 200),
        ]);

        $result = (new LicenseKeysApi(makeClient()))->validateDetailed('VZR-XXXX', 'example.com');

        expect($result)->toBe(['valid' => true, 'tier' => 'free']);
    });

    it('hits the real analytics routes', function (string $method, array $args, string $expectedPath) {
        Http::preventStrayRequests();
        Http::fake(["https://api.vizor-vr.test{$expectedPath}*" => Http::response(['data' => []], 200)]);

        (new AnalyticsApi(makeClient()))->{$method}(...$args);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_starts_with($request->url(), "https://api.vizor-vr.test{$expectedPath}?")
        );
    })->with([
        'overview' => ['overview', [30], '/api/v1/analytics/overview'],
        'views over time' => ['viewsOverTime', [30], '/api/v1/analytics/views-over-time'],
        'top content' => ['topContent', [30, 10], '/api/v1/analytics/top-content'],
        'engagement' => ['engagement', [30], '/api/v1/analytics/engagement'],
        'content summary' => ['contentSummary', ['abc123', 30], '/api/v1/analytics/summary/abc123'],
        'gaze data' => ['gazeData', ['abc123', 30], '/api/v1/analytics/gaze/abc123'],
    ]);
});
