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
     * Get current billing/subscription status.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        return $this->client->get('/api/v1/billing/status')->json();
    }

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
