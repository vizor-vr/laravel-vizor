<?php

use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\Client;
use Vizor\Laravel\Api\LicenseKeysApi;
use Vizor\Laravel\Facades\Vizor;

describe('Vizor Facade', function () {

    // ──────────────────────────── License Keys ────────────────────────────

    it('returns true from licenseKeys()->validate() when API confirms valid', function () {
        Http::fake(['*' => Http::response(['valid' => true], 200)]);

        // Use a fresh Client + API instance to avoid singleton cache issues
        $client = new Client(
            baseUrl: config('vizor.api_url'),
            apiKey: config('vizor.api_key'),
        );
        $api = new LicenseKeysApi($client);
        $result = $api->validate('test-key');

        expect($result)->toBeTrue();

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/license/validate-standalone')
            && $request['licenseKey'] === 'test-key'
        );
    });

    it('returns false from licenseKeys()->validate() when API says invalid', function () {
        Http::fake(['*' => Http::response(['valid' => false], 200)]);

        $client = new Client(
            baseUrl: config('vizor.api_url'),
            apiKey: config('vizor.api_key'),
        );
        $api = new LicenseKeysApi($client);
        $result = $api->validate('bad-key');

        expect($result)->toBeFalse();
    });

    // ──────────────────────────── Billing ────────────────────────────

    it('sends GET /api/v1/billing/plans with the x-api-key header on billing()->plans()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::billing()->plans();

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/billing/plans')
            && $request->hasHeader('x-api-key', 'test-api-key-123')
        );
    });

});
