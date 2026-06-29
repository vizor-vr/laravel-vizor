<?php

namespace Vizor\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Vizor\Laravel\Api\Client client()
 * @method static \Vizor\Laravel\Api\ContentApi content()
 * @method static \Vizor\Laravel\Api\AnalyticsApi analytics()
 * @method static \Vizor\Laravel\Api\ApiKeysApi apiKeys()
 * @method static \Vizor\Laravel\Api\LicenseKeysApi licenseKeys()
 * @method static \Vizor\Laravel\Api\BillingApi billing()
 *
 * @see \Vizor\Laravel\VizorManager
 */
class Vizor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vizor';
    }
}
