<?php

namespace Vizor\Laravel\Api;

/**
 * API Keys management methods.
 */
class ApiKeysApi
{
    public function __construct(
        private readonly Client $client,
    ) {}

    /**
     * List all API keys.
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->client->get('/api/v1/api-keys')->json();
    }

    /**
     * Create a new API key.
     *
     * @param  array<int, string>  $domains  Allowed domains
     * @return array<string, mixed>
     */
    public function create(string $name, array $domains = []): array
    {
        return $this->client->post('/api/v1/api-keys', [
            'name' => $name,
            'domains' => $domains,
        ])->json();
    }

    /**
     * Revoke an API key.
     *
     * @return array<string, mixed>
     */
    public function revoke(string $id): array
    {
        return $this->client->delete("/api/v1/api-keys/{$id}")->json();
    }

    /**
     * Validate an API key.
     */
    public function validate(string $key): bool
    {
        try {
            $response = $this->client->post('/api/v1/api-keys/validate', ['key' => $key]);

            return $response->json('valid', false);
        } catch (\Exception) {
            return false;
        }
    }
}
