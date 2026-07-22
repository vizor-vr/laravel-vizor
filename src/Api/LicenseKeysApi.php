<?php

namespace Vizor\Laravel\Api;

/**
 * License key validation methods.
 */
class LicenseKeysApi
{
    public function __construct(
        private readonly Client $client,
    ) {}

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
