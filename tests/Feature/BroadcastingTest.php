<?php

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Vizor\Laravel\Events\PlayerEnded;
use Vizor\Laravel\Events\PlayerError;
use Vizor\Laravel\Events\PlayerPause;
use Vizor\Laravel\Events\PlayerPlay;
use Vizor\Laravel\Events\PlayerReady;
use Vizor\Laravel\Events\PlayerTimeUpdate;
use Vizor\Laravel\Livewire\VideoPlayer;

// ──────────────────────────── Interface / Contract ────────────────────────────

it('PlayerPlay implements ShouldBroadcast', function () {
    $event = new PlayerPlay(contentId: 'abc-123', userId: 'user-1');

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
});

// ──────────────────────────── broadcastWhen ────────────────────────────

it('broadcastWhen returns true when broadcasting is enabled', function () {
    config(['vizor.broadcasting.enabled' => true]);

    $event = new PlayerPlay(contentId: 'abc-123', userId: 'user-1');

    expect($event->broadcastWhen())->toBeTrue();
});

it('broadcastWhen returns false when broadcasting is disabled', function () {
    config(['vizor.broadcasting.enabled' => false]);

    $event = new PlayerPlay(contentId: 'abc-123', userId: 'user-1');

    expect($event->broadcastWhen())->toBeFalse();
});

// ──────────────────────────── Channel Name ────────────────────────────

it('broadcasts on the correct channel using config prefix', function () {
    config(['vizor.broadcasting.channel_prefix' => 'vizor']);

    $event = new PlayerPlay(contentId: 'abc-123', userId: 'user-1');
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe('vizor.abc-123');
});

it('respects a custom channel prefix', function () {
    config(['vizor.broadcasting.channel_prefix' => 'custom-prefix']);

    $event = new PlayerPause(contentId: 'vid-456', userId: 'user-2');
    $channels = $event->broadcastOn();

    expect($channels[0]->name)->toBe('custom-prefix.vid-456');
});

// ──────────────────────────── Payloads ────────────────────────────

it('PlayerPlay carries correct payload', function () {
    $event = new PlayerPlay(contentId: 'content-1', userId: 'user-1');
    $payload = $event->broadcastWith();

    expect($payload)->toBe([
        'contentId' => 'content-1',
        'userId' => 'user-1',
    ]);
});

it('PlayerPause carries correct payload', function () {
    $event = new PlayerPause(contentId: 'content-2', userId: 'user-2');
    $payload = $event->broadcastWith();

    expect($payload)->toBe([
        'contentId' => 'content-2',
        'userId' => 'user-2',
    ]);
});

it('PlayerEnded carries correct payload', function () {
    $event = new PlayerEnded(contentId: 'content-3', userId: 'user-3');
    $payload = $event->broadcastWith();

    expect($payload)->toBe([
        'contentId' => 'content-3',
        'userId' => 'user-3',
    ]);
});

it('PlayerError includes code and message in payload', function () {
    $event = new PlayerError(
        contentId: 'content-err',
        userId: 'user-1',
        code: 'MEDIA_ERR_DECODE',
        message: 'The media could not be decoded',
    );
    $payload = $event->broadcastWith();

    expect($payload)->toBe([
        'contentId' => 'content-err',
        'userId' => 'user-1',
        'code' => 'MEDIA_ERR_DECODE',
        'message' => 'The media could not be decoded',
    ]);
});

it('PlayerTimeUpdate includes currentTime and duration in payload', function () {
    $event = new PlayerTimeUpdate(
        contentId: 'content-time',
        userId: 'user-1',
        currentTime: 42.5,
        duration: 120.0,
    );
    $payload = $event->broadcastWith();

    expect($payload)->toBe([
        'contentId' => 'content-time',
        'userId' => 'user-1',
        'currentTime' => 42.5,
        'duration' => 120.0,
    ]);
});

// ──────────────────────────── Dispatchable ────────────────────────────

it('events are dispatchable', function () {
    Event::fake();

    PlayerPlay::dispatch('dispatch-test', 'user-1');
    PlayerPause::dispatch('dispatch-test', 'user-2');
    PlayerEnded::dispatch('dispatch-test', 'user-3');
    PlayerError::dispatch('dispatch-test', 'user-4', 'ERR', 'msg');
    PlayerTimeUpdate::dispatch('dispatch-test', 'user-5', 10.0, 60.0);
    PlayerReady::dispatch('dispatch-test', 'user-6');

    Event::assertDispatched(PlayerPlay::class);
    Event::assertDispatched(PlayerPause::class);
    Event::assertDispatched(PlayerEnded::class);
    Event::assertDispatched(PlayerError::class);
    Event::assertDispatched(PlayerTimeUpdate::class);
    Event::assertDispatched(PlayerReady::class);
});

// ──────────────────────────── VideoPlayer::onTimeUpdate() ────────────────────────────

it('broadcasts the actual currentTime and duration on timeupdate', function () {
    view()->share('slot', '');

    config(['vizor.broadcasting.enabled' => true]);
    Event::fake([PlayerTimeUpdate::class]);

    Livewire::test(VideoPlayer::class, ['src' => '/v.mp4'])
        ->call('onTimeUpdate', 12.5, 60.0);

    Event::assertDispatched(
        PlayerTimeUpdate::class,
        fn (PlayerTimeUpdate $event) => (float) $event->currentTime === 12.5 && (float) $event->duration === 60.0
    );
});
