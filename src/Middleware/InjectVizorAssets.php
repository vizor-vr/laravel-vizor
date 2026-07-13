<?php

namespace Vizor\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Vizor\Laravel\Support\PlayerScript;

/**
 * Auto-inject the Vizor player script into HTML responses (WS-G).
 *
 * Opt-in: does nothing unless `vizor.auto_inject` is true (default OFF — most
 * apps should load the player only on pages that embed it, via @vizorScripts).
 * When enabled, the pinned player <script> tag is inserted before </head>.
 */
final class InjectVizorAssets
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (! config('vizor.auto_inject', false)) {
            return $response;
        }
        if (! $response instanceof Response) {
            return $response;
        }
        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }
        $content = $response->getContent();
        if ($content === false || ! str_contains($content, '</head>')) {
            return $response;
        }

        $response->setContent(
            str_replace('</head>', PlayerScript::tag()."\n</head>", $content),
        );

        return $response;
    }
}
