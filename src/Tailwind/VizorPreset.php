<?php

namespace Vizor\Laravel\Tailwind;

/**
 * Tailwind CSS preset configuration for Vizor player components.
 *
 * Usage in tailwind.config.js:
 *   import vizorPreset from './vendor/vizor/laravel/tailwind.preset.js';
 *   export default { presets: [vizorPreset], ... };
 */
final class VizorPreset
{
    /**
     * Get the Tailwind preset configuration as an array.
     *
     * @return array<string, mixed>
     */
    public static function config(): array
    {
        return [
            'theme' => [
                'extend' => [
                    'colors' => [
                        'vizor-primary' => 'var(--vizor-primary, #f43f5e)',
                    ],
                    'aspectRatio' => [
                        'vizor' => '16 / 9',
                        'vizor-square' => '1 / 1',
                        'vizor-4k' => '3840 / 2160',
                    ],
                    'maxWidth' => [
                        'vizor-4k' => '3840px',
                        'vizor-2k' => '2560px',
                        'vizor-1080' => '1920px',
                    ],
                    'screens' => [
                        'vizor-sm' => '480px',
                        'vizor-md' => '768px',
                        'vizor-lg' => '1024px',
                        'vizor-xl' => '1440px',
                    ],
                ],
            ],
        ];
    }
}
