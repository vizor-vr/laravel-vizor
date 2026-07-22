<?php

use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\ApiKeysApi;
use Vizor\Laravel\Api\BillingApi;
use Vizor\Laravel\Api\Client;
use Vizor\Laravel\Api\LicenseKeysApi;

/**
 * Guards against a latent TypeError: Laravel's HTTP client `Response::json()`
 * returns null when the body is empty or not JSON. `BillingApi::plans()`
 * declares `: array` and returns `->json(...)` unguarded, so an empty
 * 200/204 body throws `TypeError: Return value must be of type array, null
 * returned` instead of yielding a graceful empty result.
 *
 * Boundary: the `?? []` guard only covers a null decode (empty/non-JSON
 * body); a 2xx body containing a bare JSON scalar (e.g. `true` or `"ok"`)
 * is out of contract and would still TypeError, but every real Vizor
 * endpoint returns a JSON object, so that case isn't exercised here.
 *
 * Scope: as of the API-surface prune (#8), `AnalyticsApi`, `ContentApi`,
 * and the `list()`/`create()`/`revoke()`/`generate()`/`status()` management
 * methods on ApiKeysApi/LicenseKeysApi/BillingApi are gone. `plans()` is the
 * only surviving method that returns a raw unguarded ->json(). validate()/
 * validateDetailed() on ApiKeysApi and LicenseKeysApi build their arrays
 * explicitly via ->json($key, $default) and were already safe -- confirmed
 * below rather than assumed.
 *
 * Named differently from `makeClient()` in ApiEndpointPathsTest.php to avoid
 * redeclaring a global function -- Pest requires every test file into the
 * same PHP process, so two top-level `function makeClient()` declarations
 * would fatal with "Cannot redeclare function".
 */
function makeHardeningClient(): Client
{
    return new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-key');
}

describe('Api methods harden against empty response bodies', function () {

    it('BillingApi::plans() returns [] instead of throwing when the API responds with an empty body', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/billing/plans' => Http::response('', 200),
        ]);

        $result = (new BillingApi(makeHardeningClient()))->plans();

        expect($result)->toBe([]);
    });

});

describe('validateDetailed() already tolerates empty response bodies', function () {

    it('ApiKeysApi::validateDetailed() falls back to valid=false/tier=free on an empty body', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate' => Http::response('', 200),
        ]);

        $result = (new ApiKeysApi(makeHardeningClient()))->validateDetailed('vz_live_abc', 'example.com');

        expect($result)->toBe(['valid' => false, 'tier' => 'free']);
    });

    it('LicenseKeysApi::validateDetailed() falls back to valid=false/tier=free on an empty body', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate-standalone' => Http::response('', 200),
        ]);

        $result = (new LicenseKeysApi(makeHardeningClient()))->validateDetailed('VZR-XXXX', 'example.com');

        expect($result)->toBe(['valid' => false, 'tier' => 'free']);
    });
});
