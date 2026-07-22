<?php

use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\ApiKeysApi;
use Vizor\Laravel\Api\Client;

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
});
