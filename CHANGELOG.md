# Changelog

## 0.2.0 - 2026-07-13

### Fixed
- **Player pin was broken**: the CDN URL referenced `@latest/dist/vizor-player.register.es.js` — a file the npm package never shipped — so `@vizorScripts` 404'd on every page. The script tag now pins `@vizor-vr/player@<player_version>/dist/register.js`, single-sourced in `Vizor\Laravel\Support\PlayerScript` and locked by a test against the committed `player-dist-manifest.json`.
- `sync-player-version.yml` sed patterns matched a config format that no longer existed; they now update the config default, the dist manifest, and the pin test together.

### Added
- `<x-vizor-caption>` Blade component rendering an HTML5 `<track kind="subtitles">` (the player consumes track children; there is no vz-caption custom element).
- `InjectVizorAssets` middleware (`vizor.inject` alias): opt-in auto-injection of the pinned player script before `</head>` (`vizor.auto_inject`, default off).
- `SECURITY.md`.

All notable changes to `vizor/laravel` will be documented in this file.

## [Unreleased]

## [0.1.0] - 2025-01-01

### Added
- Initial release
- Blade components for all 7 player elements (video, img, tour, cinema, live, playlist, annotation)
- Livewire components with full reactive binding (6 components)
- Alpine.js plugin with vizorPlayer and vizorLivewirePlayer data components
- Vizor API Facade (Content, Analytics, API Keys, License Keys, Billing)
- Artisan commands: vizor:install, vizor:component, vizor:test-page, vizor:examples
- Server-side license validation middleware with caching
- Filament admin panel integration (plugin, widget, content resource)
- Optional Laravel Echo broadcasting support
- Tailwind CSS preset with player utilities
- Publishable example components
- Comprehensive Pest test suite
