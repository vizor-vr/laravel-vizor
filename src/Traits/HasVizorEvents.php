<?php

namespace Vizor\Laravel\Traits;

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
            'player.ready' => \Vizor\Laravel\Events\PlayerReady::class,
            'player.play' => \Vizor\Laravel\Events\PlayerPlay::class,
            'player.pause' => \Vizor\Laravel\Events\PlayerPause::class,
            'player.ended' => \Vizor\Laravel\Events\PlayerEnded::class,
            'player.error' => \Vizor\Laravel\Events\PlayerError::class,
            'player.timeupdate' => \Vizor\Laravel\Events\PlayerTimeUpdate::class,
            default => null,
        };
    }
}
