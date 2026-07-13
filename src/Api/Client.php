<?php

namespace Vizor\Laravel\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * HTTP client wrapper for the Vizor API.
 */
class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly int $timeout = 30,
        private readonly int $retryTimes = 3,
        private readonly int $retryDelay = 100,
    ) {}

    /**
     * Create a configured HTTP request instance.
     */
    public function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retryDelay, fn ($e, $request) => $e instanceof ConnectionException
                || ($e instanceof RequestException && $e->response->status() === 429)
            );
    }

    /**
     * Send a GET request.
     *
     * @param  array<string, mixed>  $query
     */
    public function get(string $path, array $query = []): Response
    {
        return $this->request()->get($path, $query)->throw();
    }

    /**
     * Send a POST request.
     *
     * @param  array<string, mixed>  $data
     */
    public function post(string $path, array $data = []): Response
    {
        return $this->request()->post($path, $data)->throw();
    }

    /**
     * Send a PATCH request.
     *
     * @param  array<string, mixed>  $data
     */
    public function patch(string $path, array $data = []): Response
    {
        return $this->request()->patch($path, $data)->throw();
    }

    /**
     * Send a DELETE request.
     */
    public function delete(string $path): Response
    {
        return $this->request()->delete($path)->throw();
    }

    /**
     * Get the base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the API key (masked for display).
     */
    public function getMaskedApiKey(): string
    {
        if (strlen($this->apiKey) <= 8) {
            return str_repeat('*', strlen($this->apiKey));
        }

        return substr($this->apiKey, 0, 4).'...'.substr($this->apiKey, -4);
    }
}
