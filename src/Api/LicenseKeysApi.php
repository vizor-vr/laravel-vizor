<?php

namespace Vizor\Laravel\Api;

/**
 * License key management methods.
 */
class LicenseKeysApi
{
    public function __construct(
        private readonly Client $client,
    ) {}

    /**
     * List all license keys.
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->client->get('/api/v1/license-keys')->json();
    }

    /**
     * Generate a new license key.
     *
     * @param  array<int, string>  $domains  Allowed domains
     * @return array<string, mixed>
     */
    public function generate(array $domains = [], ?string $tier = null): array
    {
        return $this->client->post('/api/v1/license-keys', array_filter([
            'domains' => $domains,
            'tier' => $tier,
        ]))->json();
    }

    /**
     * Revoke a license key.
     *
     * @return array<string, mixed>
     */
    public function revoke(string $id): array
    {
        return $this->client->delete("/api/v1/license-keys/{$id}")->json();
    }

    /**
     * Validate a license key.
     */
    public function validate(string $key): bool
    {
        try {
            $response = $this->client->post('/api/v1/license-keys/validate', ['key' => $key]);

            return $response->json('valid', false);
        } catch (\Exception) {
            return false;
        }
    }
}
