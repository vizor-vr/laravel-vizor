# Changelog

All notable changes to `vizor-vr/laravel-vizor` will be documented in this file.

## [Unreleased]

## 0.3.1 - 2026-07-22

<!-- PROVISIONAL version number: this branch was rebased on top of the v0.3.0
     player-sync release, so the next release must be > 0.3.0. The final
     number here is the release owner's call — a one-line change if they
     decide differently. Entry contents below are otherwise final. -->

### Fixed
- SaaS license validation (`ApiKeysApi::validate()`) POSTed to `/api/v1/api-keys/validate`, a route that doesn't exist, so every request 404'd, was swallowed by the catch block, and every paying customer silently got the free tier + watermark. It now hits `POST /api/v1/license/validate` (with the required `domain` field).
- Standalone license validation (`LicenseKeysApi::validate()`) POSTed to `/api/v1/license-keys/validate`, which also doesn't exist. It now hits `POST /api/v1/license/validate-standalone`.
- `AnalyticsApi::viewsOverTime()`, `contentSummary()`, and `gazeData()` called made-up routes instead of the real API paths. They now call `/api/v1/analytics/views-over-time`, `/api/v1/analytics/summary/{id}`, and `/api/v1/analytics/gaze/{id}` respectively.
- `ValidateVizorLicense` middleware now exposes the validated tier via `config('vizor.license_tier')` on success as well as failure (previously the config was only ever set — to `free` — on the failure path, so a valid license never surfaced its real tier).
- `VideoPlayer::onTimeUpdate()` broadcast `PlayerTimeUpdate` with no payload, so every `player.timeupdate` event over the wire reported `0`/`0` regardless of actual playback position. It now broadcasts the real `currentTime` and `duration`.
- The Livewire `video-player` view never rendered the `content-id`, `api-key`, `license-key`, or `api-endpoint` attributes onto the underlying `<vz-video>` element, breaking API-driven embeds (analytics, license gating) configured through the Livewire component. All four now render when set.

### Removed
- **`Vizor\Laravel\Middleware\VizorCors`**, shipped since v0.1.0 but never registered as a route middleware alias, documented, or otherwise wired up. It set `Access-Control-Allow-Origin: *` with `X-Api-Key` in `Access-Control-Allow-Headers` — a wildcard-origin CORS footgun with no reason to exist unused. If you manually registered this middleware yourself, migrate to Laravel's built-in `HandleCors` via `config/cors.php`.

### Changed
- The license-status cache key changed from `vizor_license_valid` (bool) to `vizor_license_status` (array shape, `{valid, tier}`), needed to carry the tier fix above. No action needed — old cache entries simply expire within their TTL and are never read by the new code.

## 0.3.0 - 2026-07-19

### Changed
- Player version synced to `0.3.0` (`player_version` config default, `player-dist-manifest.json`, and `PlayerScript`'s runtime fallback), applied manually with the exact `sed` rules from `sync-player-version.yml` — the workflow itself could not run automatically because the publishing repo has no `LARAVEL_DISPATCH_TOKEN` set, and this repo does not allow Actions to open PRs. Dist layout unchanged (still `dist/vizor.js` and `dist/register.js`).

## 0.2.1 - 2026-07-14

### Changed
- Player version synced to `0.2.1` (`player_version` config default, `player-dist-manifest.json`, and the `PlayerScript` pin), and `sync-player-version.yml` extended to also update `ConfigTest`'s shipped-default assertion and `PlayerScript`'s runtime fallback — previously only `config/vizor.php`, the dist manifest, and `PlayerScriptTest` were patched, so those two lagged behind after a version bump.
- CI: PHPStan and Pint are now blocking (previously advisory); added a manual `pint-fix` workflow for contributors without a local PHP toolchain.

### Docs
- Security disclosure contact aligned to `security@utgnetworks.com`; added a Security section to the README linking the org-wide policy.
- Corrected the package name referenced in this changelog to `vizor-vr/laravel-vizor`.

## 0.2.0 - 2026-07-13

### Fixed
- **Player pin was broken**: the CDN URL referenced `@latest/dist/vizor-player.register.es.js` — a file the npm package never shipped — so `@vizorScripts` 404'd on every page. The script tag now pins `@vizor-vr/player@<player_version>/dist/register.js`, single-sourced in `Vizor\Laravel\Support\PlayerScript` and locked by a test against the committed `player-dist-manifest.json`.
- `sync-player-version.yml` sed patterns matched a config format that no longer existed; they now update the config default, the dist manifest, and the pin test together.

### Added
- `<x-vizor-caption>` Blade component rendering an HTML5 `<track kind="subtitles">` (the player consumes track children; there is no vz-caption custom element).
- `InjectVizorAssets` middleware (`vizor.inject` alias): opt-in auto-injection of the pinned player script before `</head>` (`vizor.auto_inject`, default off).
- `SECURITY.md`.

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
