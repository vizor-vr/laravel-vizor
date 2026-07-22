<?php

use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\ApiKeysApi;
use Vizor\Laravel\Api\BillingApi;
use Vizor\Laravel\Api\Client;
use Vizor\Laravel\Api\LicenseKeysApi;

/**
 * Contract tests: every Api class must hit the REAL Vizor API paths.
 *
 * Http::preventStrayRequests() makes any request to an unfaked (= wrong)
 * URL throw. The license validation methods (validate() / validateDetailed()
 * on ApiKeysApi and LicenseKeysApi) swallow exceptions internally, so a
 * regressed path there never bubbles up as a loud failure -- instead it
 * shows up as a plain assertion failure on the happy-path tests below
 * (expected valid=true / a real tier, got the "unreachable" fallback
 * instead). BillingApi does not catch exceptions, so a regressed path there
 * throws loudly instead. Either way, every endpoint gets at least one
 * happy-path test against its exact URL -- a wildcard fake would hide the
 * regression entirely.
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

});

describe('Billing Api endpoint paths', function () {

    it('hits the real billing routes', function (string $method, string $expectedPath) {
        Http::preventStrayRequests();
        Http::fake([
            "https://api.vizor-vr.test{$expectedPath}" => Http::response(['data' => []], 200),
        ]);

        $result = (new BillingApi(makeClient()))->{$method}();

        expect($result)->toBe(['data' => []]);
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === "https://api.vizor-vr.test{$expectedPath}"
        );
    })->with([
        'status' => ['status', '/api/v1/billing/status'],
        'plans' => ['plans', '/api/v1/billing/plans'],
    ]);
});

describe('ApiKeys and LicenseKeys management endpoint paths', function () {

    it('lists API keys via GET /api/v1/api-keys', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/api-keys' => Http::response(['data' => []], 200),
        ]);

        $result = (new ApiKeysApi(makeClient()))->list();

        expect($result)->toBe(['data' => []]);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/api-keys'
            && $request->method() === 'GET'
        );
    });

    it('creates an API key via POST /api/v1/api-keys', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/api-keys' => Http::response(['id' => 'key_1', 'rawKey' => 'vz_abc'], 201),
        ]);

        $result = (new ApiKeysApi(makeClient()))->create('My Key', ['example.com']);

        expect($result)->toBe(['id' => 'key_1', 'rawKey' => 'vz_abc']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/api-keys'
            && $request->method() === 'POST'
            && $request['name'] === 'My Key'
            && $request['domains'] === ['example.com']
        );
    });

    it('revokes an API key via DELETE /api/v1/api-keys/{id}', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/api-keys/key_1' => Http::response(['id' => 'key_1', 'active' => false], 200),
        ]);

        $result = (new ApiKeysApi(makeClient()))->revoke('key_1');

        expect($result)->toBe(['id' => 'key_1', 'active' => false]);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/api-keys/key_1'
            && $request->method() === 'DELETE'
        );
    });

    it('lists license keys via GET /api/v1/license-keys', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license-keys' => Http::response(['data' => []], 200),
        ]);

        $result = (new LicenseKeysApi(makeClient()))->list();

        expect($result)->toBe(['data' => []]);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/license-keys'
            && $request->method() === 'GET'
        );
    });

    it('generates a license key via POST /api/v1/license-keys', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license-keys' => Http::response(['id' => 'lic_1', 'tier' => 'pro', 'rawKey' => 'VZR-XXXX'], 200),
        ]);

        $result = (new LicenseKeysApi(makeClient()))->generate(['example.com'], 'pro');

        expect($result)->toBe(['id' => 'lic_1', 'tier' => 'pro', 'rawKey' => 'VZR-XXXX']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/license-keys'
            && $request->method() === 'POST'
            && $request['domains'] === ['example.com']
            && $request['tier'] === 'pro'
        );
    });

    it('revokes a license key via DELETE /api/v1/license-keys/{id}', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license-keys/lic_1' => Http::response(['success' => true], 200),
        ]);

        $result = (new LicenseKeysApi(makeClient()))->revoke('lic_1');

        expect($result)->toBe(['success' => true]);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/license-keys/lic_1'
            && $request->method() === 'DELETE'
        );
    });
});
