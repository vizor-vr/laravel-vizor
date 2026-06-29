<?php

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\Client;

describe('Api Client', function () {

    // ──────────────────────────── Headers ────────────────────────────

    it('sends x-api-key header on every request', function () {
        Http::fake(['*' => Http::response(['data' => 'test'], 200)]);

        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $client->get('/api/v1/content');

        Http::assertSent(fn ($request) => $request->hasHeader('x-api-key', 'test-api-key-123'));
    });

    it('uses the base URL from constructor', function () {
        Http::fake(['*' => Http::response(['data' => 'test'], 200)]);

        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $client->get('/api/v1/content');

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.vizor-vr.test'));
    });

    it('sets Accept: application/json header', function () {
        Http::fake(['*' => Http::response(['data' => 'test'], 200)]);

        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $client->get('/api/v1/content');

        Http::assertSent(fn ($request) => $request->hasHeader('Accept', 'application/json'));
    });

    // ──────────────────────────── HTTP Methods ────────────────────────────

    it('sends GET request with query params via get()', function () {
        Http::fake(['*' => Http::response(['data' => 'test'], 200)]);

        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $client->get('/api/v1/content', ['limit' => 10, 'offset' => 0]);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), 'limit=10')
            && str_contains($request->url(), 'offset=0')
        );
    });

    it('sends POST request with JSON body via post()', function () {
        Http::fake(['*' => Http::response(['data' => 'test'], 200)]);

        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $client->post('/api/v1/content', ['title' => 'New Video', 'format' => 'MONO_360']);

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request['title'] === 'New Video'
            && $request['format'] === 'MONO_360'
        );
    });

    it('sends PATCH request with JSON body via patch()', function () {
        Http::fake(['*' => Http::response(['data' => 'test'], 200)]);

        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $client->patch('/api/v1/content/abc', ['title' => 'Updated']);

        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && $request['title'] === 'Updated'
        );
    });

    it('sends DELETE request via delete()', function () {
        Http::fake(['*' => Http::response(['data' => 'test'], 200)]);

        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $client->delete('/api/v1/content/abc');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/api/v1/content/abc')
        );
    });

    // ──────────────────────────── Error Handling ────────────────────────────

    it('throws RequestException on 4xx errors', function () {
        Http::fake(['*' => Http::response(['error' => 'Not found'], 404)]);

        $client = new Client(
            baseUrl: 'https://api.vizor-vr.test',
            apiKey: 'test-api-key-123',
            retryTimes: 0,
        );

        $client->get('/api/v1/content/nonexistent');
    })->throws(RequestException::class);

    it('throws RequestException on 5xx errors', function () {
        Http::fake(['*' => Http::response(['error' => 'Server error'], 500)]);

        $client = new Client(
            baseUrl: 'https://api.vizor-vr.test',
            apiKey: 'test-api-key-123',
            retryTimes: 0,
        );

        $client->get('/api/v1/failing');
    })->throws(RequestException::class);

    // ──────────────────────────── Utility Methods ────────────────────────────

    it('masks the API key correctly via getMaskedApiKey()', function () {
        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $masked = $client->getMaskedApiKey();

        expect($masked)->toBe('test...-123');
        expect($masked)->not->toBe('test-api-key-123');
        expect($masked)->toContain('...');
    });

    it('fully masks short API keys', function () {
        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'short');
        $masked = $client->getMaskedApiKey();

        expect($masked)->toBe('*****');
    });

    it('returns the base URL via getBaseUrl()', function () {
        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');

        expect($client->getBaseUrl())->toBe('https://api.vizor-vr.test');
    });

    it('handles empty response body gracefully', function () {
        Http::fake(['*' => Http::response('', 200)]);

        $client = new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-api-key-123');
        $response = $client->get('/api/v1/health');

        expect($response->status())->toBe(200);
        expect($response->body())->toBe('');
    });

});
