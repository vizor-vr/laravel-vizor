# Prune Server-Unreachable API Surface Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove every plugin Api method that can never succeed in production because its API route requires a Clerk Bearer session while the plugin's `Client` sends only `x-api-key`, leaving exactly the provably working surface: `ApiKeysApi::validate/validateDetailed`, `LicenseKeysApi::validate/validateDetailed`, `BillingApi::plans`.

**Architecture:** Audit (2026-07-22, cross-referenced against `C:\LocalServer\vizor-vr\apps\api`) proved 18 of 21 methods hit `requireAuth`-gated routes → guaranteed 401; the 3 survivors hit middleware-free routes. Pruning = delete `ContentApi`/`AnalyticsApi` wholesale, cut management methods from `ApiKeysApi`/`LicenseKeysApi`, cut `BillingApi::status`, then ripple through `VizorManager`, the `Vizor` facade docblock, both test files, README, CHANGELOG. `ValidateVizorLicense` middleware only uses `validateDetailed` — untouched. No other production code references the pruned surface (verified by grep over src/resources/routes/config/database).

**Tech Stack:** PHP 8.2 / Laravel package, Pest 3 tests (Testbench), PHPStan (larastan), Pint. Windows: run PHP tooling via PowerShell (Herd), never Git Bash.

**Branch:** `prune-server-api-surface` off `main` (55b26ce) in worktree `.claude/worktrees/xenodochial-swartz-335f5a`. PR target: `main`.

**Verification commands** (PowerShell, from worktree root; no composer scripts exist):
- `vendor\bin\pest` — expect all green
- `vendor\bin\phpstan analyse --no-progress` — expect 0 errors
- `vendor\bin\pint --test` — CRLF smudge can false-positive locally (memory: needs LF clone); if only line-ending noise, rely on CI's Linux checkout

---

### Task 1: Drop `ContentApi` and `AnalyticsApi` entirely

**Files:**
- Delete: `src/Api/ContentApi.php`, `src/Api/AnalyticsApi.php`
- Modify: `src/VizorManager.php`, `src/Facades/Vizor.php`
- Test: `tests/Feature/FacadeTest.php`, `tests/Feature/ApiEndpointPathsTest.php`

- [ ] **Step 1: Update tests to the target surface (removal-first TDD: suite must fail while classes still referenced nowhere, pass after deletion)**

`tests/Feature/FacadeTest.php` — replace the entire file with:

```php
<?php

use Illuminate\Support\Facades\Http;
use Vizor\Laravel\Api\Client;
use Vizor\Laravel\Api\LicenseKeysApi;
use Vizor\Laravel\Facades\Vizor;

describe('Vizor Facade', function () {

    // ──────────────────────────── License Keys ────────────────────────────

    it('returns true from licenseKeys()->validate() when API confirms valid', function () {
        Http::fake(['*' => Http::response(['valid' => true], 200)]);

        // Use a fresh Client + API instance to avoid singleton cache issues
        $client = new Client(
            baseUrl: config('vizor.api_url'),
            apiKey: config('vizor.api_key'),
        );
        $api = new LicenseKeysApi($client);
        $result = $api->validate('test-key');

        expect($result)->toBeTrue();

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/license/validate-standalone')
            && $request['licenseKey'] === 'test-key'
        );
    });

    it('returns false from licenseKeys()->validate() when API says invalid', function () {
        Http::fake(['*' => Http::response(['valid' => false], 200)]);

        $client = new Client(
            baseUrl: config('vizor.api_url'),
            apiKey: config('vizor.api_key'),
        );
        $api = new LicenseKeysApi($client);
        $result = $api->validate('bad-key');

        expect($result)->toBeFalse();
    });

    // ──────────────────────────── Billing ────────────────────────────

    it('sends GET /api/v1/billing/plans with the x-api-key header on billing()->plans()', function () {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Vizor::billing()->plans();

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), '/api/v1/billing/plans')
            && $request->hasHeader('x-api-key', 'test-api-key-123')
        );
    });

});
```

Note: the `x-api-key` header assertion moves here from the deleted `content()->list()` test so client-auth coverage survives. Confirm `test-api-key-123` is set in `tests/TestCase.php` before relying on it.

`tests/Feature/ApiEndpointPathsTest.php`:
- Remove `use Vizor\Laravel\Api\AnalyticsApi;` and `use Vizor\Laravel\Api\ContentApi;` imports.
- Delete the `it('hits the real analytics routes', ...)` dataset test (lines 167-183).
- In the `describe('Content and Billing Api endpoint paths', ...)` block: delete the five content tests (`lists content`, `gets a single content item`, `creates`, `updates`, `deletes`), keep the billing dataset test for now (Task 3 prunes `status`), rename describe to `'Billing Api endpoint paths'`.
- Rewrite the file-header docblock (it names the deleted classes):

```php
/**
 * Contract tests: every Api class must hit the REAL Vizor API paths.
 *
 * Http::preventStrayRequests() makes any request to an unfaked (= wrong)
 * URL throw. The license validation methods (validate() / validateDetailed()
 * on ApiKeysApi and LicenseKeysApi) swallow exceptions internally, so a
 * regressed path there never bubbles up as a loud failure -- instead it
 * shows up as a plain assertion failure on the happy-path tests below
 * (expected valid=true / a real tier, got the "unreachable" fallback
 * instead). BillingApi does not catch exceptions, so a regressed path there
 * throws loudly instead. Either way, every endpoint gets at least one
 * happy-path test against its exact URL -- a wildcard fake would hide the
 * regression entirely.
 */
```

- [ ] **Step 2: Delete the classes and ripple through manager + facade**

Delete `src/Api/ContentApi.php` and `src/Api/AnalyticsApi.php` (`git rm`).

`src/VizorManager.php` — remove the two imports, two fields, two accessors. Final file:

```php
<?php

namespace Vizor\Laravel;

use Vizor\Laravel\Api\ApiKeysApi;
use Vizor\Laravel\Api\BillingApi;
use Vizor\Laravel\Api\Client;
use Vizor\Laravel\Api\LicenseKeysApi;

/**
 * Manager class behind the Vizor Facade.
 * Provides access to all Vizor API resource classes.
 */
class VizorManager
{
    private ?Client $client = null;

    private ?ApiKeysApi $apiKeysApi = null;

    private ?LicenseKeysApi $licenseKeysApi = null;

    private ?BillingApi $billingApi = null;

    /**
     * Get the HTTP client instance.
     */
    public function client(): Client
    {
        if (! $this->client) {
            $this->client = new Client(
                baseUrl: config('vizor.api_url', 'https://api.vizor-vr.com'),
                apiKey: config('vizor.api_key', ''),
            );
        }

        return $this->client;
    }

    /**
     * API key validation methods.
     */
    public function apiKeys(): ApiKeysApi
    {
        return $this->apiKeysApi ??= new ApiKeysApi($this->client());
    }

    /**
     * License key validation methods.
     */
    public function licenseKeys(): LicenseKeysApi
    {
        return $this->licenseKeysApi ??= new LicenseKeysApi($this->client());
    }

    /**
     * Billing methods.
     */
    public function billing(): BillingApi
    {
        return $this->billingApi ??= new BillingApi($this->client());
    }
}
```

`src/Facades/Vizor.php` — docblock keeps only:

```php
/**
 * @method static \Vizor\Laravel\Api\Client client()
 * @method static \Vizor\Laravel\Api\ApiKeysApi apiKeys()
 * @method static \Vizor\Laravel\Api\LicenseKeysApi licenseKeys()
 * @method static \Vizor\Laravel\Api\BillingApi billing()
 *
 * @see VizorManager
 */
```

- [ ] **Step 3: Run suite**

Run: `vendor\bin\pest` → expect PASS (content/analytics tests are gone with their classes).

- [ ] **Step 4: Commit**

```bash
git add -A src/Api src/VizorManager.php src/Facades/Vizor.php tests/Feature/FacadeTest.php tests/Feature/ApiEndpointPathsTest.php
git commit -m "refactor!: drop ContentApi and AnalyticsApi — server key cannot reach Clerk-gated routes"
```

### Task 2: Cut management methods from `ApiKeysApi` / `LicenseKeysApi`

**Files:**
- Modify: `src/Api/ApiKeysApi.php` (delete `list()`, `create()`, `revoke()`; class docblock → `API key validation methods.`)
- Modify: `src/Api/LicenseKeysApi.php` (delete `list()`, `generate()` — killing the dead `tier` param — and `revoke()`; class docblock → `License key validation methods.`)
- Test: `tests/Feature/FacadeTest.php` (already pruned in Task 1), `tests/Feature/ApiEndpointPathsTest.php`

- [ ] **Step 1: Delete the `describe('ApiKeys and LicenseKeys management endpoint paths', ...)` block** (the last describe in `ApiEndpointPathsTest.php` — all 6 tests: list/create/revoke keys, list/generate/revoke license keys).

- [ ] **Step 2: Delete methods `list`/`create`/`revoke` from `ApiKeysApi` and `list`/`generate`/`revoke` from `LicenseKeysApi`.** Each class keeps constructor + `validate()` + `validateDetailed()` unchanged. Class docblocks per above.

- [ ] **Step 3: Run suite**

Run: `vendor\bin\pest` → expect PASS.

- [ ] **Step 4: Commit**

```bash
git add src/Api/ApiKeysApi.php src/Api/LicenseKeysApi.php tests/Feature/ApiEndpointPathsTest.php
git commit -m "refactor!: prune key-management methods (Clerk admin routes); keep validation surface"
```

### Task 3: Cut `BillingApi::status()`

**Files:**
- Modify: `src/Api/BillingApi.php` (delete `status()`, keep `plans()`)
- Test: `tests/Feature/ApiEndpointPathsTest.php`

- [ ] **Step 1: Replace the billing dataset test with a single plans test** in the (renamed) `describe('Billing Api endpoint paths', ...)`:

```php
    it('lists billing plans via GET /api/v1/billing/plans', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.vizor-vr.test/api/v1/billing/plans' => Http::response(['data' => []], 200),
        ]);

        $result = (new BillingApi(makeClient()))->plans();

        expect($result)->toBe(['data' => []]);
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === 'https://api.vizor-vr.test/api/v1/billing/plans'
        );
    });
```

- [ ] **Step 2: Delete `status()` from `src/Api/BillingApi.php`** (docblock stays `Billing API methods.`).

- [ ] **Step 3: Run suite**

Run: `vendor\bin\pest` → expect PASS.

- [ ] **Step 4: Commit**

```bash
git add src/Api/BillingApi.php tests/Feature/ApiEndpointPathsTest.php
git commit -m "refactor!: drop BillingApi::status (owner-role Clerk route); keep public plans()"
```

### Task 4: README + CHANGELOG

**Files:**
- Modify: `README.md:249-302` (Vizor Facade section)
- Modify: `CHANGELOG.md` (`[Unreleased]`)

- [ ] **Step 1: Replace README facade section** (everything from `## Vizor Facade` through the `### Billing` code fence, keeping the `---` separators) with:

```markdown
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
```

- [ ] **Step 2: Add CHANGELOG `[Unreleased]` entries**

```markdown
## [Unreleased]

### Removed
- **BREAKING:** `Vizor::content()` (`ContentApi`) and `Vizor::analytics()` (`AnalyticsApi`), plus the management methods `ApiKeysApi::list()/create()/revoke()`, `LicenseKeysApi::list()/generate()/revoke()`, and `BillingApi::status()`. A 2026-07-22 audit of the Vizor API proved every one of these routes requires a Clerk user session (`requireAuth`, most also admin/owner role) and the API has no dual-auth path — the package's `x-api-key` header can never authenticate them, so all 18 methods returned 401 in production regardless of input. The surviving surface is exactly what works server-side: `ApiKeysApi::validate()/validateDetailed()`, `LicenseKeysApi::validate()/validateDetailed()` (key posted in the body to the public license endpoints), and `BillingApi::plans()` (public route). Server-side content/analytics access may return if the API grows scoped `x-api-key` auth for those routes.
- The dead `tier` parameter died with `LicenseKeysApi::generate()`: the API's Zod schema silently stripped it and derives tier from the organization's plan server-side, so it never had any effect.
```

- [ ] **Step 3: Full verification**

Run: `vendor\bin\pest` → PASS; `vendor\bin\phpstan analyse --no-progress` → 0 errors; `vendor\bin\pint --test` → clean (or line-ending-only noise per memory).

- [ ] **Step 4: Commit**

```bash
git add README.md CHANGELOG.md
git commit -m "docs: document pruned server-callable API surface"
```

### Task 5: Push + PR

- [ ] Push `prune-server-api-surface`, open PR to `main` titled `refactor!: prune API surface to server-callable endpoints`, body summarizing the auth-model audit matrix. Do NOT merge — user's call.

**Post-merge loose ends handled outside the repo:** memory update (done — `vizor-laravel-versioning.md`), background-task chip for the post-launch API-side scoped `x-api-key` feature decision.
