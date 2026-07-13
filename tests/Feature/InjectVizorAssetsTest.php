<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Vizor\Laravel\Middleware\InjectVizorAssets;
use Vizor\Laravel\Support\PlayerScript;

// ──────────────────────────── Auto-inject (WS-G) ────────────────────────────

it('does nothing when auto_inject is off (the default)', function () {
    config(['vizor.auto_inject' => false]);

    $middleware = new InjectVizorAssets;
    $request = Request::create('/page');
    $html = '<html><head><title>t</title></head><body></body></html>';
    $response = $middleware->handle(
        $request,
        fn ($r) => new Response($html, 200, ['Content-Type' => 'text/html']),
    );

    expect($response->getContent())->toBe($html);
});

it('injects the pinned player script before </head> when enabled', function () {
    config(['vizor.auto_inject' => true, 'vizor.cdn_url' => null, 'vizor.use_local_assets' => false]);

    $middleware = new InjectVizorAssets;
    $request = Request::create('/page');
    $response = $middleware->handle(
        $request,
        fn ($r) => new Response('<html><head></head><body></body></html>', 200, ['Content-Type' => 'text/html']),
    );

    $content = $response->getContent();
    expect($content)->toContain(PlayerScript::tag());
    expect($content)->toContain('/dist/register.js');
    // Injected BEFORE the closing head tag.
    expect(strpos($content, 'register.js'))->toBeLessThan(strpos($content, '</head>'));
});

it('leaves non-HTML responses untouched even when enabled', function () {
    config(['vizor.auto_inject' => true]);

    $middleware = new InjectVizorAssets;
    $request = Request::create('/api/data');
    $response = $middleware->handle(
        $request,
        fn ($r) => new Response('{"ok":true}', 200, ['Content-Type' => 'application/json']),
    );

    expect($response->getContent())->toBe('{"ok":true}');
});

it('leaves HTML without a </head> untouched', function () {
    config(['vizor.auto_inject' => true]);

    $middleware = new InjectVizorAssets;
    $request = Request::create('/fragment');
    $response = $middleware->handle(
        $request,
        fn ($r) => new Response('<div>partial</div>', 200, ['Content-Type' => 'text/html']),
    );

    expect($response->getContent())->toBe('<div>partial</div>');
});

it('is aliased as vizor.inject on the router', function () {
    $middleware = app('router')->getMiddleware();
    expect($middleware)->toHaveKey('vizor.inject');
    expect($middleware['vizor.inject'])->toBe(InjectVizorAssets::class);
});
