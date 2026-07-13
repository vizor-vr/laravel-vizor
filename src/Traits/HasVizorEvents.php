<?php

namespace Vizor\Laravel\Traits;

use Vizor\Laravel\Events\PlayerEnded;
use Vizor\Laravel\Events\PlayerError;
use Vizor\Laravel\Events\PlayerPause;
use Vizor\Laravel\Events\PlayerPlay;
use Vizor\Laravel\Events\PlayerReady;
use Vizor\Laravel\Events\PlayerTimeUpdate;

/**
 * Shared event dispatching for Vizor Livewire components.
 * Handles optional broadcasting integration when enabled.
 */
trait HasVizorEvents
{
    /**
     * Broadcast a player event if broadcasting is enabled.
     */
    protected function broadcastIfEnabled(string $eventName, array $payload = []): void
    {
        if (! config('vizor.broadcasting.enabled', false)) {
            return;
        }

        $eventClass = $this->resolveEventClass($eventName);

        if ($eventClass && class_exists($eventClass)) {
            $args = array_merge([
                'contentId' => $this->contentId ?? 'unknown',
                'userId' => auth()->id(),
            ], $payload);

            event(new $eventClass(...$args));
        }
    }

    /**
     * Map event names to broadcasting event classes.
     */
    private function resolveEventClass(string $eventName): ?string
    {
        return match ($eventName) {
            'player.ready' => PlayerReady::class,
            'player.play' => PlayerPlay::class,
            'player.pause' => PlayerPause::class,
            'player.ended' => PlayerEnded::class,
            'player.error' => PlayerError::class,
            'player.timeupdate' => PlayerTimeUpdate::class,
            default => null,
        };
    }
}
