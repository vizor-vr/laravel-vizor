<?php

namespace Vizor\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Vizor\Laravel\Facades\Vizor;

/**
 * Middleware that validates the Vizor license on each request.
 *
 * Supports both SaaS (API key) and standalone (license key) validation modes.
 * On failure the tier is degraded to "free" so the player still renders
 * (with watermark / limited features) rather than breaking entirely.
 */
class ValidateVizorLicense
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('vizor.validate_license', true)) {
            return $next($request);
        }

        $isValid = Cache::remember(
            'vizor_license_valid',
            config('vizor.license_cache_ttl', 3600),
            fn () => $this->validateLicense(),
        );

        if (! $isValid) {
            config(['vizor.license_tier' => 'free']);
        }

        return $next($request);
    }

    /**
     * Run the appropriate license validation depending on the configured mode.
     */
    private function validateLicense(): bool
    {
        try {
            if (config('vizor.license_mode') === 'saas') {
                return Vizor::apiKeys()->validate(
                    config('vizor.api_key', ''),
                );
            }

            return Vizor::licenseKeys()->validate(
                config('vizor.license_key', ''),
            );
        } catch (\Throwable) {
            // Network errors, malformed responses, etc. -- degrade gracefully.
            return false;
        }
    }
}
