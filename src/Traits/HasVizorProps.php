<?php

namespace Vizor\Laravel\Traits;

use Vizor\Laravel\Support\AttributeBuilder;

/**
 * Shared prop handling for Vizor Blade and Livewire components.
 * Provides config-fallback resolution for API key, license key, and primary color.
 */
trait HasVizorProps
{
    /**
     * Resolve the API key from the prop or fall back to config.
     */
    protected function resolvedApiKey(): ?string
    {
        return $this->apiKey ?? config('vizor.api_key');
    }

    /**
     * Resolve the license key from the prop or fall back to config.
     */
    protected function resolvedLicenseKey(): ?string
    {
        return $this->licenseKey ?? config('vizor.license_key');
    }

    /**
     * Resolve the primary color from the prop or fall back to config.
     */
    protected function resolvedPrimaryColor(): ?string
    {
        return $this->primaryColor ?? config('vizor.primary_color');
    }

    /**
     * Resolve the API endpoint from the prop or fall back to config.
     */
    protected function resolvedApiEndpoint(): ?string
    {
        return $this->apiEndpoint ?? config('vizor.api_url');
    }

    /**
     * Get the props as an attribute map for rendering.
     *
     * @return array<string, mixed>
     */
    protected function vizorProps(): array
    {
        return array_filter([
            'src' => $this->src ?? null,
            'format' => $this->format ?? null,
            'title' => $this->title ?? null,
            'poster' => $this->poster ?? null,
            'apiKey' => $this->resolvedApiKey(),
            'licenseKey' => $this->resolvedLicenseKey(),
            'primaryColor' => $this->resolvedPrimaryColor(),
            'contentId' => $this->contentId ?? null,
        ], fn ($v) => $v !== null);
    }

    /**
     * Build the props into an HTML attribute string.
     */
    protected function buildVizorAttributes(): string
    {
        return AttributeBuilder::build($this->vizorProps());
    }
}
