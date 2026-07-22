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
     * Validate a standalone license key (phone-home revocation/plan check).
     */
    public function validate(string $key, ?string $domain = null): bool
    {
        return $this->validateDetailed($key, $domain)['valid'];
    }

    /**
     * Validate a standalone license key and return the license result:
     * whether the key is valid and which tier it grants. Invalid/unreachable
     * => valid=false, tier=free.
     *
     * @return array{valid: bool, tier: string}
     */
    public function validateDetailed(string $key, ?string $domain = null): array
    {
        $domain ??= (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost');

        try {
            $response = $this->client->post('/api/v1/license/validate-standalone', [
                'licenseKey' => $key,
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
