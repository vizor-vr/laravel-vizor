<?php

use Illuminate\Support\Facades\Event;
use Vizor\Laravel\Events\PlayerEnded;
use Vizor\Laravel\Events\PlayerError;
use Vizor\Laravel\Events\PlayerPause;
use Vizor\Laravel\Events\PlayerPlay;
use Vizor\Laravel\Events\PlayerReady;
use Vizor\Laravel\Events\PlayerTimeUpdate;
use Vizor\Laravel\Traits\HasVizorEvents;

/**
 * Minimal test stub that uses the HasVizorEvents trait.
 */
function createEventsStub(array $overrides = []): object
{
    return new class($overrides)
    {
        use HasVizorEvents;

        public ?string $contentId = null;

        public function __construct(array $overrides)
        {
            foreach ($overrides as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        // Expose protected method for testing
        public function testBroadcastIfEnabled(string $eventName, array $payload = []): void
        {
            $this->broadcastIfEnabled($eventName, $payload);
        }

        // Expose private method for testing via reflection
        public function testResolveEventClass(string $eventName): ?string
        {
            $reflection = new ReflectionMethod($this, 'resolveEventClass');

            return $reflection->invoke($this, $eventName);
        }
    };
}

describe('HasVizorEvents trait', function () {
    it('broadcastIfEnabled() does nothing when broadcasting is disabled', function () {
        Event::fake();
        config()->set('vizor.broadcasting.enabled', false);

        $stub = createEventsStub(['contentId' => 'test-123']);
        $stub->testBroadcastIfEnabled('player.ready');

        Event::assertNothingDispatched();
    });

    it('broadcastIfEnabled() fires event when broadcasting is enabled', function () {
        Event::fake();
        config()->set('vizor.broadcasting.enabled', true);

        $stub = createEventsStub(['contentId' => 'video-456']);
        $stub->testBroadcastIfEnabled('player.ready');

        Event::assertDispatched(PlayerReady::class, function ($event) {
            return $event->contentId === 'video-456';
        });
    });

    it('broadcastIfEnabled() handles unknown event names gracefully', function () {
        Event::fake();
        config()->set('vizor.broadcasting.enabled', true);

        $stub = createEventsStub(['contentId' => 'test-789']);

        // Should not throw, should just not dispatch
        $stub->testBroadcastIfEnabled('player.nonexistent');

        Event::assertNothingDispatched();
    });

    it('resolveEventClass() maps known event names to correct classes', function () {
        $stub = createEventsStub();

        expect($stub->testResolveEventClass('player.ready'))->toBe(PlayerReady::class);
        expect($stub->testResolveEventClass('player.play'))->toBe(PlayerPlay::class);
        expect($stub->testResolveEventClass('player.pause'))->toBe(PlayerPause::class);
        expect($stub->testResolveEventClass('player.ended'))->toBe(PlayerEnded::class);
        expect($stub->testResolveEventClass('player.error'))->toBe(PlayerError::class);
        expect($stub->testResolveEventClass('player.timeupdate'))->toBe(PlayerTimeUpdate::class);
    });

    it('resolveEventClass() returns null for unknown event names', function () {
        $stub = createEventsStub();

        expect($stub->testResolveEventClass('player.nonexistent'))->toBeNull();
        expect($stub->testResolveEventClass('unknown'))->toBeNull();
        expect($stub->testResolveEventClass(''))->toBeNull();
    });

    it('broadcastIfEnabled() uses contentId from the component instance', function () {
        Event::fake();
        config()->set('vizor.broadcasting.enabled', true);

        $stub = createEventsStub(['contentId' => 'my-content-id']);
        $stub->testBroadcastIfEnabled('player.play');

        Event::assertDispatched(PlayerPlay::class, function ($event) {
            return $event->contentId === 'my-content-id';
        });
    });

    it('broadcastIfEnabled() defaults contentId to unknown when not set', function () {
        Event::fake();
        config()->set('vizor.broadcasting.enabled', true);

        $stub = createEventsStub(); // no contentId set
        $stub->testBroadcastIfEnabled('player.pause');

        Event::assertDispatched(PlayerPause::class, function ($event) {
            return $event->contentId === 'unknown';
        });
    });
});
