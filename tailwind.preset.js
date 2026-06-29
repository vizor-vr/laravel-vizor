/**
 * Vizor Tailwind CSS Preset
 *
 * Usage:
 *   import vizorPreset from './vendor/vizor/laravel/tailwind.preset.js';
 *   export default { presets: [vizorPreset], ... };
 */
export default {
    theme: {
        extend: {
            colors: {
                'vizor-primary': 'var(--vizor-primary, #f43f5e)',
            },
            aspectRatio: {
                vizor: '16 / 9',
                'vizor-square': '1 / 1',
                'vizor-4k': '3840 / 2160',
            },
            maxWidth: {
                'vizor-4k': '3840px',
                'vizor-2k': '2560px',
                'vizor-1080': '1920px',
            },
            screens: {
                'vizor-sm': '480px',
                'vizor-md': '768px',
                'vizor-lg': '1024px',
                'vizor-xl': '1440px',
            },
        },
    },
    plugins: [
        function ({ addComponents }) {
            addComponents({
                '.vizor-container': {
                    width: '100%',
                    aspectRatio: '16 / 9',
                    position: 'relative',
                    overflow: 'hidden',
                },
                '.vizor-container-4k': {
                    width: '100%',
                    maxWidth: '3840px',
                    aspectRatio: '16 / 9',
                    position: 'relative',
                    overflow: 'hidden',
                    margin: '0 auto',
                },
                '.vizor-rounded': {
                    borderRadius: '0.75rem',
                    overflow: 'hidden',
                },
                '.vizor-shadow': {
                    boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2)',
                },
                '.vizor-loading': {
                    backgroundColor: '#1a1a2e',
                    animation: 'vizor-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                },
            });
        },
        function ({ addUtilities }) {
            addUtilities({
                '@keyframes vizor-pulse': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.5' },
                },
            });
        },
    ],
};
