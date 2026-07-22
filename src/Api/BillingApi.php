<?php

namespace Vizor\Laravel\Api;

/**
 * Billing API methods.
 */
class BillingApi
{
    public function __construct(
        private readonly Client $client,
    ) {}

    /**
     * List available subscription plans.
     *
     * @return array<string, mixed>
     */
    public function plans(): array
    {
        return $this->client->get('/api/v1/billing/plans')->json();
    }
}
