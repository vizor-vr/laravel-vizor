<?php

use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\Client;
use Vizor\Laravel\Api\LicenseKeysApi;
use Vizor\Laravel\Facades\Vizor;

describe('Vizor Facade', function () {

    // ──────────────────────────── Content API ────────────────────────────

    it('sends GET /api/v1/content on content()->list()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::content()->list();

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/content')
            && $request->hasHeader('x-api-key', 'test-api-key-123')
        );
    });

    it('sends GET /api/v1/content/{id} on content()->get()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::content()->get('abc-123');

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/content/abc-123')
        );
    });

    it('sends POST /api/v1/content on content()->create()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::content()->create('My Video', 'MONO_360', ['src' => 'video.mp4']);

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/content')
            && $request['title'] === 'My Video'
            && $request['format'] === 'MONO_360'
            && $request['src'] === 'video.mp4'
        );
    });

    it('sends PATCH /api/v1/content/{id} on content()->update()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::content()->update('abc-123', ['title' => 'Updated Title']);

        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && str_contains($request->url(), '/api/v1/content/abc-123')
            && $request['title'] === 'Updated Title'
        );
    });

    it('sends DELETE /api/v1/content/{id} on content()->delete()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::content()->delete('abc-123');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/api/v1/content/abc-123')
        );
    });

    // ──────────────────────────── Analytics API ────────────────────────────

    it('sends GET /api/v1/analytics/overview with days param on analytics()->overview()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::analytics()->overview(7);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/analytics/overview')
            && str_contains($request->url(), 'days=7')
        );
    });

    it('sends GET /api/v1/analytics/views on analytics()->viewsOverTime()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::analytics()->viewsOverTime(14);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/analytics/views')
            && str_contains($request->url(), 'days=14')
        );
    });

    it('sends GET /api/v1/analytics/top-content on analytics()->topContent()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::analytics()->topContent(30, 5);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/analytics/top-content')
            && str_contains($request->url(), 'days=30')
            && str_contains($request->url(), 'limit=5')
        );
    });

    // ──────────────────────────── API Keys ────────────────────────────

    it('sends GET /api/v1/api-keys on apiKeys()->list()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::apiKeys()->list();

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/api-keys')
        );
    });

    it('sends POST /api/v1/api-keys on apiKeys()->create()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::apiKeys()->create('Production Key', ['example.com']);

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/api-keys')
            && $request['name'] === 'Production Key'
            && $request['domains'] === ['example.com']
        );
    });

    it('sends DELETE /api/v1/api-keys/{id} on apiKeys()->revoke()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::apiKeys()->revoke('key-456');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/api/v1/api-keys/key-456')
        );
    });

    // ──────────────────────────── License Keys ────────────────────────────

    it('sends GET /api/v1/license-keys on licenseKeys()->list()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::licenseKeys()->list();

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/license-keys')
        );
    });

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

    it('sends GET /api/v1/billing/status on billing()->status()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::billing()->status();

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/billing/status')
        );
    });

    it('sends GET /api/v1/billing/plans on billing()->plans()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::billing()->plans();

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/billing/plans')
        );
    });

});
