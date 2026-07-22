# Vizor for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vizor-vr/laravel-vizor.svg?style=flat-square)](https://packagist.org/packages/vizor-vr/laravel-vizor)
[![Laravel 11+/12+](https://img.shields.io/badge/Laravel-11%2B%20%7C%2012%2B-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://www.php.net)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen?style=flat-square)](https://github.com/vizor-vr/laravel-vizor)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

Laravel package for the [Vizor VR video player](https://vizor-vr.com). Provides 7 Blade components, 6 Livewire components with reactive state, an Alpine.js plugin, a full API facade, Filament admin integration, license validation middleware, and a Tailwind CSS preset -- everything you need to embed 360/VR/WebXR video in a Laravel application.

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Livewire 3+

## Installation

```bash
composer require vizor-vr/laravel-vizor
```

```bash
php artisan vizor:install
```

The install command publishes the config file, the Alpine.js plugin, and generates a test page at `/vizor-test`.

## Quick Start

**1. Install the package**

```bash
composer require vizor-vr/laravel-vizor
php artisan vizor:install
```

**2. Add environment variables**

```env
VIZOR_API_KEY=your-api-key
VIZOR_LICENSE_KEY=your-license-key
```

**3. Use a Blade component**

```blade
<x-vizor-video
    src="/videos/tour.mp4"
    :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
    title="My VR Video"
/>
```

Visit `/vizor-test` to verify the player renders correctly.

---

## Blade Components

All 7 components auto-include the player script via `@vizorScripts` and accept an optional `{{ $slot }}` for child elements.

| Component | Element | Use case |
|-----------|---------|----------|
| `<x-vizor-video>` | `<vz-video>` | 360/VR/flat video |
| `<x-vizor-img>` | `<vz-img>` | 360/VR images |
| `<x-vizor-tour>` | `<vz-tour>` | Multi-scene virtual tours |
| `<x-vizor-cinema>` | `<vz-cinema>` | Virtual cinema environment |
| `<x-vizor-live>` | `<vz-live>` | Live HLS/DASH streams |
| `<x-vizor-playlist>` | `<vz-playlist>` | Multi-video playlist |
| `<x-vizor-annotation>` | `<vz-annotation>` | Spatial annotations (nested inside video) |

### Video

```blade
<x-vizor-video
    src="/videos/ocean.mp4"
    :format="\Vizor\Laravel\Support\FormatEnum::STEREO_360_TB"
    title="Ocean Dive"
    poster="/images/ocean-thumb.jpg"
    :autoplay="true"
    :loop="true"
/>
```

### Image

```blade
<x-vizor-img
    src="/images/panorama.jpg"
    :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
    title="Mountain Panorama"
/>
```

### Tour

```blade
<x-vizor-tour
    src="/tours/apartment.json"
    title="Apartment Tour"
    start-probe-id="living-room"
/>
```

### Cinema

```blade
<x-vizor-cinema
    src="/videos/movie.mp4"
    :format="\Vizor\Laravel\Support\FormatEnum::MONO_FLAT"
    title="Feature Film"
/>
```

### Live Stream

```blade
<x-vizor-live
    src="https://stream.example.com/live.m3u8"
    :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
    title="Live Concert"
/>
```

### Playlist

```blade
<x-vizor-playlist :autoplay="true" :loop-playlist="true">
    <x-vizor-video
        src="/videos/clip-1.mp4"
        :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
        title="Clip 1"
    />
    <x-vizor-video
        src="/videos/clip-2.mp4"
        :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
        title="Clip 2"
    />
</x-vizor-playlist>
```

### Annotations

Nest `<x-vizor-annotation>` inside a video component to place 3D hotspots:

```blade
<x-vizor-video
    src="/videos/tour.mp4"
    :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
>
    <x-vizor-annotation
        :lat="15.5"
        :lon="-42.3"
        title="Look here"
        icon="info"
        :time-start="10.0"
        :time-end="30.0"
    />
</x-vizor-video>
```

---

## Livewire Components

Full server-side reactive state with two-way binding. All components use the `HasVizorEvents` and `HasVizorProps` traits.

| Livewire Tag | Reactive State |
|-------------|----------------|
| `<livewire:vizor-video-player>` | `currentTime`, `duration`, `volume`, `playing`, `isMuted`, `fullscreen`, `ready` |
| `<livewire:vizor-img-viewer>` | `ready` |
| `<livewire:vizor-tour-viewer>` | `ready`, `currentProbeId` |
| `<livewire:vizor-cinema-player>` | `currentTime`, `duration`, `volume`, `playing`, `isMuted`, `ready` |
| `<livewire:vizor-live-player>` | `playing`, `ready`, `volume`, `isMuted` |
| `<livewire:vizor-playlist-player>` | `currentIndex`, `currentTitle`, `totalItems`, `playing`, `ready` |

### Usage

```blade
<livewire:vizor-video-player
    src="/videos/ocean.mp4"
    :format="\Vizor\Laravel\Support\FormatEnum::MONO_360"
    title="Ocean Dive"
/>
```

### Server-Side Control

`VideoPlayer` exposes `play()`, `pause()`, `seek()`, and `setVolume()` as Livewire actions; `CinemaPlayer` exposes `play()`, `pause()`, and `seek()` (`LivePlayer` exposes `play()`/`pause()`); calling one updates the component's own state and calls `$this->dispatch('vizor-command', command: '...')`. The Livewire bridge (`vizorLivewirePlayer` in `resources/js/vizor-alpine.js`) listens for that `vizor-command` event via `Livewire.on()` and forwards it to the underlying player element:

```blade
<livewire:vizor-video-player src="/video.mp4" />

{{-- From any Livewire component or Alpine: --}}
<button x-on:click="$dispatch('vizor-command', { command: 'play' })">Play</button>
<button x-on:click="$dispatch('vizor-command', { command: 'pause' })">Pause</button>
```

Livewire shares Alpine's `$dispatch`, so the snippet above works from Alpine anywhere on the page, and equally from `$this->dispatch('vizor-command', command: 'play')` in another Livewire component's PHP. Supported commands: `play`, `pause`, `seek` (with a `time` payload), `setVolume` (with a `volume` payload), and `toggleMute`.

### Generating Custom Components

Scaffold a new Livewire component with Vizor boilerplate:

```bash
php artisan vizor:component MyCustomPlayer
```

This creates `app/Livewire/MyCustomPlayer.php` and `resources/views/livewire/my-custom-player.blade.php` with the full event handler scaffold.

---

## Alpine.js Plugin

For projects that don't use Livewire, the Alpine.js plugin provides client-side reactive bindings with no server round-trips.

### Setup

Register the plugin in your `resources/js/app.js`:

```js
import Alpine from 'alpinejs';
import vizorAlpine from './vizor-alpine.js';

Alpine.plugin(vizorAlpine);
Alpine.start();
```

### Usage

```html
<div x-data="vizorPlayer">
    <vz-video
        x-ref="player"
        src="/videos/ocean.mp4"
        format="MONO_360"
    ></vz-video>

    <button @click="togglePlay" x-text="playing ? 'Pause' : 'Play'"></button>
    <span x-text="`${Math.floor(currentTime)}s / ${Math.floor(duration)}s`"></span>
</div>
```

The `vizorPlayer` data component exposes: `ready`, `playing`, `currentTime`, `duration`, `volume`, `muted`, `fullscreen`, `loading`, `error` -- plus methods `play()`, `pause()`, `togglePlay()`, `seek(t)`, `toggleMute()`, `setVolume(v)`, `enterFullscreen()`, `exitFullscreen()`.

---

## Vizor Facade

The `Vizor` facade provides typed access to the server-callable surface of the Vizor REST API. It is backed by the `VizorManager` class and configured via `VIZOR_API_KEY` and `VIZOR_API_URL`.

> **Scope note:** the facade deliberately covers only endpoints a server can reach. Management surfaces — content CRUD, analytics dashboards, API/license key administration, billing status — require a Clerk user session (dashboard login) on the Vizor API; a server-held API key cannot authenticate against them, so this package does not expose them.

### License validation

```php
use Vizor\Laravel\Facades\Vizor;

// SaaS mode: validate the configured API key (bool)
$valid = Vizor::apiKeys()->validate($apiKey, $domain);

// Full result: ['valid' => bool, 'tier' => string]
$result = Vizor::apiKeys()->validateDetailed($apiKey, $domain);

// Standalone mode: validate a license key (phone-home revocation/plan check)
$valid  = Vizor::licenseKeys()->validate($licenseKey, $domain);
$result = Vizor::licenseKeys()->validateDetailed($licenseKey, $domain);
```

`$domain` is optional and defaults to the host parsed from `app.url`. The `ValidateVizorLicense` middleware calls these for you and caches the result (`vizor.license_cache_ttl`).

### Billing

```php
// Public plan catalog (pricing display)
$plans = Vizor::billing()->plans();
```

---

## Artisan Commands

| Command | Description |
|---------|-------------|
| `vizor:install` | Publish config, Alpine plugin, prompt for API key, generate test page |
| `vizor:component {name}` | Scaffold a Livewire component with Vizor boilerplate |
| `vizor:test-page` | Create a test page with all player types at `/vizor-test` |
| `vizor:examples` | Publish 3 example components (VideoPlayer, VideoGallery, AnalyticsDashboard) |

All commands accept `--force` to overwrite existing files.

---

## Middleware

The `vizor.license` middleware validates your license key against the Vizor API (with cache). On failure it degrades the tier to `free` (watermark, mono only) rather than blocking the request.

### Route Group

```php
Route::middleware('vizor.license')->group(function () {
    Route::get('/vr/{id}', [VrController::class, 'show']);
});
```

### Configuration

```env
VIZOR_VALIDATE_LICENSE=true
VIZOR_LICENSE_MODE=saas          # "saas" (validates API key) or "standalone" (validates license key)
VIZOR_LICENSE_CACHE_TTL=3600     # seconds to cache validation result
```

---

## Filament Integration

The package includes a Filament v3 plugin with a content resource and an embeddable player widget.

### Setup

Enable in your `.env`:

```env
VIZOR_FILAMENT=true
```

Register the plugin in your panel provider:

```php
use Vizor\Laravel\Filament\VizorPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            VizorPlugin::make(),
        ]);
}
```

This adds:
- **Content Resource** -- full CRUD table for Vizor content
- **Player Widget** -- embeddable VR player widget for dashboards

---

## Broadcasting

Optionally broadcast player events (play, pause, ended, ready, error, timeupdate) over Laravel Echo for real-time dashboards or collaborative features.

### Enable

```env
VIZOR_BROADCASTING=true
```

### Listen

```js
Echo.channel('vizor.content-id-123')
    .listen('PlayerPlay', (e) => console.log('Playing', e))
    .listen('PlayerPause', (e) => console.log('Paused', e))
    .listen('PlayerEnded', (e) => console.log('Ended', e));
```

Events broadcast: `PlayerReady`, `PlayerPlay`, `PlayerPause`, `PlayerEnded`, `PlayerError`, `PlayerTimeUpdate`.

---

## Tailwind CSS Preset

Import the preset in your `tailwind.config.js` for Vizor-specific utilities:

```js
import vizorPreset from './vendor/vizor-vr/laravel-vizor/tailwind.preset.js';

export default {
    presets: [vizorPreset],
    // ...
};
```

### Included utilities

| Class | Description |
|-------|-------------|
| `vizor-container` | 16:9 aspect ratio, full width, relative positioned |
| `vizor-container-4k` | Same as above, max 3840px, centered |
| `vizor-rounded` | 0.75rem border radius with overflow hidden |
| `vizor-shadow` | VR player drop shadow |
| `vizor-loading` | Dark pulsing loading placeholder |
| `text-vizor-primary` | Primary brand color (CSS variable `--vizor-primary`) |
| `aspect-vizor` | 16:9 aspect ratio |
| `aspect-vizor-square` | 1:1 aspect ratio |
| `max-w-vizor-1080` | Max width 1920px |
| Responsive: `vizor-sm`, `vizor-md`, `vizor-lg`, `vizor-xl` | Breakpoints at 480/768/1024/1440px |

---

## Configuration Reference

Publish the config file with `php artisan vendor:publish --tag=vizor-config`.

| Config Key | Env Variable | Default | Description |
|------------|-------------|---------|-------------|
| `api_url` | `VIZOR_API_URL` | `https://api.vizor-vr.com` | Vizor API base URL |
| `api_key` | `VIZOR_API_KEY` | `null` | Your Vizor API key |
| `license_key` | `VIZOR_LICENSE_KEY` | `null` | Standalone license key |
| `license_mode` | `VIZOR_LICENSE_MODE` | `saas` | `saas` or `standalone` |
| `cdn_url` | `VIZOR_CDN_URL` | derived from `player_version` (never `@latest`) | Player script CDN URL |
| `player_version` | `VIZOR_PLAYER_VERSION` | `0.4.0` (kept in sync by `sync-player-version.yml`) | Player version |
| `use_local_assets` | `VIZOR_USE_LOCAL_ASSETS` | `false` | Serve player JS from local assets |
| `validate_license` | `VIZOR_VALIDATE_LICENSE` | `true` | Enable license validation |
| `license_cache_ttl` | `VIZOR_LICENSE_CACHE_TTL` | `3600` | License cache duration (seconds) |
| `broadcasting.enabled` | `VIZOR_BROADCASTING` | `false` | Enable event broadcasting |
| `broadcasting.channel_prefix` | -- | `vizor` | Echo channel prefix |
| `filament.enabled` | `VIZOR_FILAMENT` | `false` | Enable Filament integration |
| `filament.navigation_group` | -- | `Vizor` | Filament nav group label |

### Publishable Assets

```bash
php artisan vendor:publish --tag=vizor-config      # config/vizor.php
php artisan vendor:publish --tag=vizor-assets       # resources/js/vizor-alpine.js
php artisan vendor:publish --tag=vizor-views        # Blade views
php artisan vendor:publish --tag=vizor-migrations   # Database migrations
```

---

## Supported Formats

The `FormatEnum` enum covers all 19 projection formats:

<details>
<summary>Full format list</summary>

| Enum Value | Label |
|-----------|-------|
| `MONO_360` | Mono 360 |
| `MONO_FLAT` | Mono Flat |
| `STEREO_180_LR` | Stereo 180 Side-by-Side |
| `STEREO_180_TB` | Stereo 180 Top-Bottom |
| `STEREO_180_LR_SPHERICAL` | VR180 Side-by-Side |
| `STEREO_180_TB_SPHERICAL` | VR180 Top-Bottom |
| `STEREO_360_LR` | Stereo 360 Side-by-Side |
| `STEREO_360_TB` | Stereo 360 Top-Bottom |
| `STEREO_FLAT_LR` | Stereo Flat Side-by-Side |
| `STEREO_FLAT_LR_SQUARE` | Stereo Flat SBS Square |
| `STEREO_FLAT_TB` | Stereo Flat Top-Bottom |
| `STEREO_FLAT_TB_SQUARE` | Stereo Flat TB Square |
| `MONO_CUBEMAP` | Mono Cubemap |
| `STEREO_CUBEMAP` | Stereo Cubemap |
| `MONO_EAC` | Mono EAC |
| `STEREO_EAC_TB` | Stereo EAC Top-Bottom |
| `MONO_FISHEYE` | Mono Fisheye |
| `STEREO_FISHEYE_LR` | Stereo Fisheye Side-by-Side |
| `CARDBOARD_PHOTO` | Cardboard Photo |

</details>

---

## Testing

The package uses [Pest](https://pestphp.com) with both unit and feature tests.

```bash
vendor/bin/pest
```

Tests cover: service provider registration, Blade components, Livewire components, Facade API client, middleware, commands, broadcasting, Filament plugin, Tailwind preset, config, enums, and traits.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## Security

Please report vulnerabilities privately per [SECURITY.md](SECURITY.md)
(security@utgnetworks.com) - do not open public issues for security problems.

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
