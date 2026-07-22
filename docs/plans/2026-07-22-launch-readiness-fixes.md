# Laravel Plugin Launch-Readiness Fixes — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix the `vizor-vr/laravel-vizor` plugin's broken API integration (wrong endpoint paths that silently degrade every install to the free tier), the dead/broken secondary features, and the doc drift — then tag v0.2.2.

**Architecture:** The plugin talks to the Vizor API (`apps/api` in the `vizor-vr` monorepo) via `Vizor\Laravel\Api\Client` (Laravel HTTP client, `x-api-key` header, `->throw()` on non-2xx). The real API routes are the source of truth: `POST /api/v1/license/validate` (body `{apiKey, domain}`, both required), `POST /api/v1/license/validate-standalone` (body `{licenseKey, domain?}`), `GET /api/v1/analytics/{overview|views-over-time|top-content|engagement}`, `GET /api/v1/analytics/summary/:contentId`, `GET /api/v1/analytics/gaze/:contentId`. Both validate endpoints return a `LicenseResult` JSON: `{valid: bool, tier: string, features: {...}, ...}` with HTTP 401/403/404 on invalid (which `->throw()` converts to an exception → caught → treated as invalid).

**Tech Stack:** PHP 8.2+, Laravel 11/12, Pest, PHPStan, Pint. Repo: `C:\LocalServer\vizor-laravel`, branch `main`.

**Working agreements:** TDD — write the failing test first for every behavior change. Run `vendor/bin/pest` and `vendor/bin/phpstan` before every commit. Commit after each task.

---

### Task 1: Fix SaaS license validation endpoint (BLOCKER)

**Files:**
- Modify: `src/Api/ApiKeysApi.php` (the `validate()` method, ~line 50)
- Test: `tests/Feature/ApiEndpointPathsTest.php` (create)

The bug: `validate()` POSTs `/api/v1/api-keys/validate` — a route that does not exist — so the 404 is caught and every install reports "invalid license" → free tier + watermark for paying customers. The real endpoint is `POST /api/v1/license/validate` and it **requires** a `domain` field.

- [ ] **Step 1: Write the failing test.** Create `tests/Feature/ApiEndpointPathsTest.php`. This test fakes ONLY the correct URL — any other path hits the `Http::preventStrayRequests()` guard and fails loudly. This is the pattern for the whole file (later tasks append to it):

```php
<?php

use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\ApiKeysApi;
use Vizor\Laravel\Api\Client;

/**
 * Contract tests: every Api class must hit the REAL Vizor API paths.
 * Http::preventStrayRequests() makes any request to an unfaked (= wrong)
 * URL throw, so a regressed path fails the suite instead of passing
 * against a wildcard fake.
 */

function makeClient(): Client
{
    return new Client(baseUrl: 'https://api.vizor-vr.test', apiKey: 'test-key');
}

describe('Api endpoint paths', function () {

    it('validates SaaS API keys against POST /api/v1/license/validate with apiKey and domain', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate' => Http::response([
                'valid' => true, 'tier' => 'pro', 'features' => [],
            ], 200),
        ]);

        $result = (new ApiKeysApi(makeClient()))->validate('vz_live_abc', 'example.com');

        expect($result)->toBeTrue();
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/license/validate'
            && $request->method() === 'POST'
            && $request['apiKey'] === 'vz_live_abc'
            && $request['domain'] === 'example.com'
        );
    });

    it('returns false when the validate endpoint rejects the key (401)', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate' => Http::response([
                'valid' => false, 'tier' => 'free', 'message' => 'Invalid API key',
            ], 401),
        ]);

        expect((new ApiKeysApi(makeClient()))->validate('bad-key', 'example.com'))->toBeFalse();
    });
});
```

- [ ] **Step 2: Run it to confirm it fails.** `vendor/bin/pest tests/Feature/ApiEndpointPathsTest.php` — expect FAIL (stray request to `/api/v1/api-keys/validate`, and `validate()` doesn't accept a domain argument yet).

- [ ] **Step 3: Fix `ApiKeysApi::validate()`.** Replace the method with:

```php
    /**
     * Validate an API key against the Vizor license endpoint.
     *
     * The API requires the requesting domain (it enforces per-key domain
     * allowlists server-side). Defaults to the app's own host.
     */
    public function validate(string $key, ?string $domain = null): bool
    {
        return (bool) ($this->validateDetailed($key, $domain)['valid'] ?? false);
    }

    /**
     * Validate an API key and return the full license result
     * (valid, tier, features, ...). Invalid/unreachable => valid=false, tier=free.
     *
     * @return array{valid: bool, tier: string}
     */
    public function validateDetailed(string $key, ?string $domain = null): array
    {
        $domain ??= (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost');

        try {
            $response = $this->client->post('/api/v1/license/validate', [
                'apiKey' => $key,
                'domain' => $domain,
            ]);

            return [
                'valid' => (bool) $response->json('valid', false),
                'tier' => (string) $response->json('tier', 'free'),
            ];
        } catch (\Exception) {
            return ['valid' => false, 'tier' => 'free'];
        }
    }
```

- [ ] **Step 4: Run the test — expect PASS.** Also run the full suite: `vendor/bin/pest` (the old wildcard-fake tests in `ApiClientTest.php` still pass because they only fake `*`).

- [ ] **Step 5: Commit.**

```bash
git add src/Api/ApiKeysApi.php tests/Feature/ApiEndpointPathsTest.php
git commit -m "fix: SaaS license validation hit a non-existent endpoint, silently degrading every install to free tier"
```

---

### Task 2: Fix standalone license validation endpoint (BLOCKER)

**Files:**
- Modify: `src/Api/LicenseKeysApi.php` (the `validate()` method, ~line 50)
- Test: `tests/Feature/ApiEndpointPathsTest.php` (append)

Same bug as Task 1: posts `/api/v1/license-keys/validate` (nonexistent); real route is `POST /api/v1/license/validate-standalone` with body `{licenseKey, domain?}`.

- [ ] **Step 1: Append the failing tests** to the `describe` block in `tests/Feature/ApiEndpointPathsTest.php`:

```php
    it('validates standalone keys against POST /api/v1/license/validate-standalone', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/license/validate-standalone' => Http::response([
                'valid' => true, 'tier' => 'enterprise', 'features' => [],
            ], 200),
        ]);

        $result = (new \Vizor\Laravel\Api\LicenseKeysApi(makeClient()))->validate('VZR-XXXX', 'example.com');

        expect($result)->toBeTrue();
        Http::assertSent(fn ($request) => $request->url() === 'https://api.vizor-vr.test/api/v1/license/validate-standalone'
            && $request['licenseKey'] === 'VZR-XXXX'
            && $request['domain'] === 'example.com'
        );
    });
```

- [ ] **Step 2: Run — expect FAIL** (stray request).

- [ ] **Step 3: Fix `LicenseKeysApi::validate()`** — same shape as Task 1:

```php
    /**
     * Validate a standalone license key (phone-home revocation/plan check).
     */
    public function validate(string $key, ?string $domain = null): bool
    {
        return (bool) ($this->validateDetailed($key, $domain)['valid'] ?? false);
    }

    /**
     * @return array{valid: bool, tier: string}
     */
    public function validateDetailed(string $key, ?string $domain = null): array
    {
        $domain ??= (string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost');

        try {
            $response = $this->client->post('/api/v1/license/validate-standalone', [
                'licenseKey' => $key,
                'domain' => $domain,
            ]);

            return [
                'valid' => (bool) $response->json('valid', false),
                'tier' => (string) $response->json('tier', 'free'),
            ];
        } catch (\Exception) {
            return ['valid' => false, 'tier' => 'free'];
        }
    }
```

- [ ] **Step 4: Run tests — expect PASS.**
- [ ] **Step 5: Commit.** `git commit -am "fix: standalone license validation endpoint path"`

---

### Task 3: Fix the three broken analytics paths (HIGH)

**Files:**
- Modify: `src/Api/AnalyticsApi.php` (`viewsOverTime()`, `contentSummary()`, `gazeData()`)
- Test: `tests/Feature/ApiEndpointPathsTest.php` (append)

Real routes (from `apps/api/src/modules/analytics/routes.ts`): `/views-over-time`, `/summary/:contentId`, `/gaze/:contentId`. (`overview`, `top-content`, `engagement` are already correct — cover them in the same test so they never regress.)

- [ ] **Step 1: Append failing tests:**

```php
    it('hits the real analytics routes', function (string $method, array $args, string $expectedPath) {
        Http::preventStrayRequests();
        Http::fake(["https://api.vizor-vr.test{$expectedPath}*" => Http::response(['data' => []], 200)]);

        (new \Vizor\Laravel\Api\AnalyticsApi(makeClient()))->{$method}(...$args);

        Http::assertSent(fn ($request) => str_starts_with(
            $request->url(), "https://api.vizor-vr.test{$expectedPath}"
        ));
    })->with([
        'overview'       => ['overview', [30], '/api/v1/analytics/overview'],
        'views over time' => ['viewsOverTime', [30], '/api/v1/analytics/views-over-time'],
        'top content'    => ['topContent', [30, 10], '/api/v1/analytics/top-content'],
        'engagement'     => ['engagement', [30], '/api/v1/analytics/engagement'],
        'content summary' => ['contentSummary', ['abc123', 30], '/api/v1/analytics/summary/abc123'],
        'gaze data'      => ['gazeData', ['abc123', 30], '/api/v1/analytics/gaze/abc123'],
    ]);
```

- [ ] **Step 2: Run — expect 3 of the 6 cases FAIL** (views, content summary, gaze).

- [ ] **Step 3: Fix the three methods** in `src/Api/AnalyticsApi.php` (only the URL strings change):

```php
    public function viewsOverTime(int $days = 30): array
    {
        return $this->client->get('/api/v1/analytics/views-over-time', ['days' => $days])->json();
    }

    public function contentSummary(string $contentId, int $days = 30): array
    {
        return $this->client->get("/api/v1/analytics/summary/{$contentId}", ['days' => $days])->json();
    }

    public function gazeData(string $contentId, int $days = 30): array
    {
        return $this->client->get("/api/v1/analytics/gaze/{$contentId}", ['days' => $days])->json();
    }
```

- [ ] **Step 4: Run tests — all 6 cases PASS.**
- [ ] **Step 5: Commit.** `git commit -am "fix: three analytics methods called routes that do not exist"`

---

### Task 4: Middleware sets `license_tier` on success too (MEDIUM)

**Files:**
- Modify: `src/Middleware/ValidateVizorLicense.php`
- Modify: `config/vizor.php` (add `license_tier` default)
- Test: `tests/Feature/LicenseMiddlewareTest.php` (append to the existing middleware test file; if none exists, create it)

Today the middleware sets `config(['vizor.license_tier' => 'free'])` only on failure — on success consumers read `null`. Cache the full validation detail (valid + tier) and always set the tier.

- [ ] **Step 1: Write the failing test:**

```php
<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('sets vizor.license_tier to the validated tier on success', function () {
    config(['vizor.validate_license' => true, 'vizor.license_mode' => 'saas', 'vizor.api_key' => 'k']);
    Cache::flush();
    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => true, 'tier' => 'pro'], 200),
    ]);

    $middleware = new \Vizor\Laravel\Middleware\ValidateVizorLicense();
    $middleware->handle(request(), fn ($r) => response('ok'));

    expect(config('vizor.license_tier'))->toBe('pro');
});

it('sets vizor.license_tier to free on failure', function () {
    config(['vizor.validate_license' => true, 'vizor.license_mode' => 'saas', 'vizor.api_key' => 'k']);
    Cache::flush();
    Http::fake([
        '*/api/v1/license/validate' => Http::response(['valid' => false, 'tier' => 'free'], 401),
    ]);

    $middleware = new \Vizor\Laravel\Middleware\ValidateVizorLicense();
    $middleware->handle(request(), fn ($r) => response('ok'));

    expect(config('vizor.license_tier'))->toBe('free');
});
```

- [ ] **Step 2: Run — expect the success case to FAIL** (`null !== 'pro'`).

- [ ] **Step 3: Rewrite the middleware's handle/validate:**

```php
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('vizor.validate_license', true)) {
            return $next($request);
        }

        /** @var array{valid: bool, tier: string} $status */
        $status = Cache::remember(
            'vizor_license_status',
            config('vizor.license_cache_ttl', 3600),
            fn () => $this->validateLicense(),
        );

        config(['vizor.license_tier' => $status['valid'] ? $status['tier'] : 'free']);

        return $next($request);
    }

    /**
     * @return array{valid: bool, tier: string}
     */
    private function validateLicense(): array
    {
        try {
            if (config('vizor.license_mode') === 'saas') {
                return Vizor::apiKeys()->validateDetailed(config('vizor.api_key', ''));
            }

            return Vizor::licenseKeys()->validateDetailed(config('vizor.license_key', ''));
        } catch (\Throwable) {
            return ['valid' => false, 'tier' => 'free'];
        }
    }
```

Note the cache key changes from `vizor_license_valid` to `vizor_license_status` (the cached shape changed — a stale bool must not be read as the new array). Update any existing tests referencing the old key.

- [ ] **Step 4: Add the config default** in `config/vizor.php`, in the License Validation section:

```php
    // Resolved at request time by ValidateVizorLicense; 'free' until validated.
    'license_tier' => 'free',
```

- [ ] **Step 5: Run full suite + PHPStan — PASS.**
- [ ] **Step 6: Commit.** `git commit -am "fix: middleware now exposes the validated license tier, not only the failure case"`

---

### Task 5: Fix timeupdate broadcast payload (MEDIUM)

**Files:**
- Modify: `src/Livewire/VideoPlayer.php` (`onTimeUpdate()`, ~line 86)
- Test: `tests/Feature/BroadcastingTest.php` (append; create if absent)

`onTimeUpdate` never passes the time/duration through, so `PlayerTimeUpdate` always broadcasts `0/0`.

- [ ] **Step 1: Failing test:**

```php
<?php

use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Vizor\Laravel\Events\PlayerTimeUpdate;

it('broadcasts the actual currentTime and duration on timeupdate', function () {
    config(['vizor.broadcasting.enabled' => true]);
    Event::fake([PlayerTimeUpdate::class]);

    Livewire::test(\Vizor\Laravel\Livewire\VideoPlayer::class, ['src' => '/v.mp4'])
        ->call('onTimeUpdate', 12.5, 60.0);

    Event::assertDispatched(PlayerTimeUpdate::class, fn (PlayerTimeUpdate $e) => $e->currentTime === 12.5 && $e->duration === 60.0);
});
```

- [ ] **Step 2: Run — FAIL** (0.0 !== 12.5).

- [ ] **Step 3: Fix:**

```php
    public function onTimeUpdate(float $time, float $dur): void
    {
        $this->currentTime = $time;
        $this->duration = $dur;
        $this->broadcastIfEnabled('player.timeupdate', [
            'currentTime' => $time,
            'duration' => $dur,
        ]);
    }
```

- [ ] **Step 4: Run — PASS.**
- [ ] **Step 5: Commit.** `git commit -am "fix: timeupdate broadcast always emitted 0/0"`

---

### Task 6: Livewire player emits content-id / api-key / api-endpoint / license-key (MEDIUM)

**Files:**
- Modify: `resources/views/livewire/video-player.blade.php`
- Test: `tests/Feature/LivewireComponentsTest.php` (append; create if absent)

The component already has `$contentId`, `$apiKey`, `$licenseKey`, `$apiEndpoint` props but the Blade view never renders them — so an API-driven (`content-id`) Livewire player can't hydrate from the delivery API.

- [ ] **Step 1: Failing test:**

```php
it('renders content-id and api credentials on the vz-video element', function () {
    Livewire::test(\Vizor\Laravel\Livewire\VideoPlayer::class, [
        'contentId' => 'cnt_123',
        'apiKey' => 'vz_live_k',
        'apiEndpoint' => 'https://api.vizor-vr.com',
    ])
        ->assertSeeHtml('content-id="cnt_123"')
        ->assertSeeHtml('api-key="vz_live_k"')
        ->assertSeeHtml('api-endpoint="https://api.vizor-vr.com"');
});
```

- [ ] **Step 2: Run — FAIL.**

- [ ] **Step 3: Add the attributes** to the `<vz-video>` tag in the Blade view (after the `@if($primaryColor)` line, same style — all escaped `{{ }}`):

```blade
        @if($contentId) content-id="{{ $contentId }}" @endif
        @if($apiKey) api-key="{{ $apiKey }}" @endif
        @if($licenseKey) license-key="{{ $licenseKey }}" @endif
        @if($apiEndpoint) api-endpoint="{{ $apiEndpoint }}" @endif
```

- [ ] **Step 4: Run — PASS.**
- [ ] **Step 5: Commit.** `git commit -am "fix: Livewire player never emitted content-id/api-key, breaking API-driven embeds"`

---

### Task 7: Remove dead `VizorCors` middleware (MEDIUM)

**Files:**
- Delete: `src/Middleware/VizorCors.php`
- Check/modify: any references (`grep -rn "VizorCors" src/ tests/ README.md`)

It is never aliased or registered, and if ever wired it would set `Access-Control-Allow-Origin: *`. Dead + dangerous = delete (pre-1.0, unshipped surface).

- [ ] **Step 1:** `grep -rn "VizorCors" .` (excluding vendor). Remove the class file and every reference (tests, README mentions).
- [ ] **Step 2:** `vendor/bin/pest && vendor/bin/phpstan` — PASS.
- [ ] **Step 3: Commit.** `git commit -am "chore: remove dead VizorCors middleware (never registered, wildcard-origin footgun)"`

---

### Task 8: Docs — README + CHANGELOG + composer metadata (LOW)

**Files:**
- Modify: `README.md` (config table ~line 434; Livewire example ~lines 190–200)
- Modify: `CHANGELOG.md`
- Modify: `composer.json`

- [ ] **Step 1: README config table:** change the `player_version` default cell from `0.1.0` to `0.2.1` and add a note: "kept in sync by `sync-player-version.yml`".

- [ ] **Step 2: README Livewire "Server-Side Control" example:** `wire:ref` / `$refs.player.play()` is not Livewire syntax. Replace the example with the real event-driven pattern the component supports (it listens for `vizor-command` dispatches):

```blade
<livewire:vizor-video-player src="/video.mp4" />

{{-- From any Livewire component or Alpine: --}}
<button x-on:click="$dispatch('vizor-command', { command: 'play' })">Play</button>
<button x-on:click="$dispatch('vizor-command', { command: 'pause' })">Pause</button>
```

(Verify against `resources/js/` Alpine plugin — the `vizorLivewirePlayer` component listens for `vizor-command`; if the event name differs, use the actual one.)

- [ ] **Step 3: CHANGELOG:** restructure so the preamble ("All notable changes…" + `[Unreleased]`) is at the TOP, then entries newest-first. Add a `## 0.2.1 - 2026-07-16` entry (player pin sync to 0.2.1 — check `git log v0.2.0..v0.2.1 --oneline` for the actual contents) and a new `## 0.2.2 - 2026-07-22` entry summarizing Tasks 1–7 (license endpoints, analytics paths, tier exposure, timeupdate payload, Livewire content-id, VizorCors removal).

- [ ] **Step 4: composer.json:** add:

```json
    "type": "library",
    "authors": [{ "name": "UTG Networks" }],
    "support": { "issues": "https://github.com/<org>/vizor-laravel/issues" },
```

(Fill `<org>` from `git remote get-url origin`.)

- [ ] **Step 5:** `composer validate` — expect "valid". Run full suite once more.
- [ ] **Step 6: Commit.** `git commit -am "docs: fix README drift, restructure CHANGELOG, composer metadata"`

---

### Task 9: Release v0.2.2

- [ ] **Step 1:** Full gate: `vendor/bin/pint --test && vendor/bin/phpstan && vendor/bin/pest` — all PASS.
- [ ] **Step 2:** Push `main`, wait for GitHub Actions `tests.yml` green (`gh run watch`).
- [ ] **Step 3:** Tag and push: `git tag v0.2.2 && git push origin v0.2.2`.
- [ ] **Step 4:** If the package is on Packagist, confirm the new version appears (auto-hook) — otherwise this is a [PAUL] provisioning step.

---

## Self-review checklist (run after implementation)

- [ ] `grep -rn "api-keys/validate\|license-keys/validate\|analytics/views'\|analytics/content" src/` returns nothing.
- [ ] `Http::preventStrayRequests()` contract tests exist for every `Api/*.php` public method that builds a URL (ContentApi/BillingApi paths were verified correct on 2026-07-22 — Fastify registers prefix routes with `prefixTrailingSlash: 'both'`, so `/api/v1/content` without trailing slash is fine; add them to the contract test anyway for regression cover).
- [ ] CHANGELOG top-of-file preamble, 0.2.1 and 0.2.2 entries present.
