<?php

namespace Vizor\Laravel;

use Vizor\Laravel\Api\ApiKeysApi;
use Vizor\Laravel\Api\BillingApi;
use Vizor\Laravel\Api\Client;
use Vizor\Laravel\Api\LicenseKeysApi;

/**
 * Manager class behind the Vizor Facade.
 * Provides access to all Vizor API resource classes.
 */
class VizorManager
{
    private ?Client $client = null;

    private ?ApiKeysApi $apiKeysApi = null;

    private ?LicenseKeysApi $licenseKeysApi = null;

    private ?BillingApi $billingApi = null;

    /**
     * Get the HTTP client instance.
     */
    public function client(): Client
    {
        if (! $this->client) {
            $this->client = new Client(
                baseUrl: config('vizor.api_url', 'https://api.vizor-vr.com'),
                apiKey: config('vizor.api_key', ''),
            );
        }

        return $this->client;
    }

    /**
     * API key validation methods.
     */
    public function apiKeys(): ApiKeysApi
    {
        return $this->apiKeysApi ??= new ApiKeysApi($this->client());
    }

    /**
     * License key validation methods.
     */
    public function licenseKeys(): LicenseKeysApi
    {
        return $this->licenseKeysApi ??= new LicenseKeysApi($this->client());
    }

    /**
     * Billing methods.
     */
    public function billing(): BillingApi
    {
        return $this->billingApi ??= new BillingApi($this->client());
    }
}
