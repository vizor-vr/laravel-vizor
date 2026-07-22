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
     * Validate an API key against the Vizor license endpoint.
     *
     * The API requires the requesting domain (it enforces per-key domain
     * allowlists server-side). Defaults to the app's own host.
     */
    public function validate(string $key, ?string $domain = null): bool
    {
        return $this->validateDetailed($key, $domain)['valid'];
    }

    /**
     * Validate an API key and return the full license result
     * (valid, tier, features, ...). Invalid/unreachable => valid=false, tier=free.
     *
     * @return array{valid: bool, tier: string}
     */
    public function validateDetailed(string $key, ?string $domain = null): array
    {
        $domain ??= (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost');

        try {
            $response = $this->client->post('/api/v1/license/validate', [
                'apiKey' => $key,
                'domain' => $domain,
            ]);

            return [
                'valid' => (bool) $response->json('valid', false),
                'tier' => (string) $response->json('tier', 'free'),
            ];
        } catch (\Exception) {
            return ['valid' => false, 'tier' => 'free'];
        }
    }
}
