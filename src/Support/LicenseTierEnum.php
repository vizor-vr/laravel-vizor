<?php

namespace Vizor\Laravel\Support;

/**
 * License tiers for the Vizor player.
 */
enum LicenseTierEnum: string
{
    case FREE = 'free';
    case STARTER = 'starter';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Free',
            self::STARTER => 'Starter',
            self::PRO => 'Pro',
            self::ENTERPRISE => 'Enterprise',
        };
    }

    /**
     * Features available at this tier.
     *
     * @return array<string, bool>
     */
    public function features(): array
    {
        return match ($this) {
            self::FREE => [
                'watermark' => true,
                'mono_only' => true,
                'analytics' => false,
                'custom_branding' => false,
                'api_access' => false,
                'collaborative' => false,
                'annotations' => false,
                'webxr' => false,
            ],
            self::STARTER => [
                'watermark' => false,
                'mono_only' => false,
                'analytics' => true,
                'custom_branding' => false,
                'api_access' => true,
                'collaborative' => false,
                'annotations' => true,
                'webxr' => true,
            ],
            self::PRO => [
                'watermark' => false,
                'mono_only' => false,
                'analytics' => true,
                'custom_branding' => true,
                'api_access' => true,
                'collaborative' => true,
                'annotations' => true,
                'webxr' => true,
            ],
            self::ENTERPRISE => [
                'watermark' => false,
                'mono_only' => false,
                'analytics' => true,
                'custom_branding' => true,
                'api_access' => true,
                'collaborative' => true,
                'annotations' => true,
                'webxr' => true,
            ],
        };
    }
}
