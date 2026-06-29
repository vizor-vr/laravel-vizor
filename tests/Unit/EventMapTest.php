<?php

use Vizor\Laravel\Support\EventMap;

describe('EventMap', function () {
    it('returns 23 standard HTML5 media event names from mediaEvents()', function () {
        $events = EventMap::mediaEvents();

        expect($events)->toBeArray()
            ->toHaveCount(23);
    });

    it('returns 18 custom vz- prefixed events from vizorEvents()', function () {
        $events = EventMap::vizorEvents();

        expect($events)->toBeArray()
            ->toHaveCount(18);
    });

    it('returns 41 total events from all()', function () {
        $all = EventMap::all();

        expect($all)->toBeArray()
            ->toHaveCount(41);

        // all() should be the combination of media + vizor
        expect($all)->toEqual(array_merge(EventMap::mediaEvents(), EventMap::vizorEvents()));
    });

    it('returns a subset of events from livewireEvents()', function () {
        $livewire = EventMap::livewireEvents();
        $all = EventMap::all();

        expect($livewire)->toBeArray();
        expect(count($livewire))->toBeLessThan(count($all));

        // Every livewire event should exist in all()
        foreach ($livewire as $event) {
            expect($all)->toContain($event);
        }
    });

    it('has correct string values for all key event constants', function () {
        // Media events
        expect(EventMap::PLAY)->toBe('play');
        expect(EventMap::PAUSE)->toBe('pause');
        expect(EventMap::PLAYING)->toBe('playing');
        expect(EventMap::SEEKING)->toBe('seeking');
        expect(EventMap::SEEKED)->toBe('seeked');
        expect(EventMap::ENDED)->toBe('ended');
        expect(EventMap::TIME_UPDATE)->toBe('timeupdate');
        expect(EventMap::VOLUME_CHANGE)->toBe('volumechange');
        expect(EventMap::LOADED_DATA)->toBe('loadeddata');
        expect(EventMap::LOADED_METADATA)->toBe('loadedmetadata');

        // Vizor custom events
        expect(EventMap::VZ_READY)->toBe('vz-ready');
        expect(EventMap::VZ_ERROR)->toBe('vz-error');
        expect(EventMap::VZ_XR_ENTER)->toBe('vz-xr-enter');
        expect(EventMap::VZ_XR_EXIT)->toBe('vz-xr-exit');
        expect(EventMap::VZ_FULLSCREEN_ENTER)->toBe('vz-fullscreen-enter');
    });

    it('has no duplicate event names in all()', function () {
        $all = EventMap::all();
        $unique = array_unique($all);

        expect($all)->toHaveCount(count($unique));
    });

    it('uses lowercase kebab-case for all event names', function () {
        $all = EventMap::all();

        foreach ($all as $event) {
            // Event names should be lowercase
            expect($event)->toBe(strtolower($event),
                "Event '{$event}' should be lowercase"
            );

            // Event names should not contain underscores (kebab-case, not snake_case)
            expect($event)->not->toContain('_',
                "Event '{$event}' should use kebab-case, not underscores"
            );

            // Event names should not contain spaces
            expect($event)->not->toContain(' ',
                "Event '{$event}' should not contain spaces"
            );
        }
    });

    it('includes expected media events in mediaEvents()', function () {
        $media = EventMap::mediaEvents();

        expect($media)->toContain('play');
        expect($media)->toContain('pause');
        expect($media)->toContain('ended');
        expect($media)->toContain('timeupdate');
        expect($media)->toContain('volumechange');
        expect($media)->toContain('loadeddata');
        expect($media)->toContain('canplay');
        expect($media)->toContain('waiting');
        expect($media)->toContain('error');
    });

    it('includes expected custom events in vizorEvents()', function () {
        $vizor = EventMap::vizorEvents();

        expect($vizor)->toContain('vz-ready');
        expect($vizor)->toContain('vz-error');
        expect($vizor)->toContain('vz-xr-enter');
        expect($vizor)->toContain('vz-xr-exit');
        expect($vizor)->toContain('vz-fullscreen-enter');
        expect($vizor)->toContain('vz-fullscreen-exit');
        expect($vizor)->toContain('vz-collab-join');
        expect($vizor)->toContain('vz-collab-leave');
        expect($vizor)->toContain('vz-playlist-change');
        expect($vizor)->toContain('vz-playlist-end');
    });
});
