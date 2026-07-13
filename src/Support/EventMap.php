<?php

namespace Vizor\Laravel\Support;

/**
 * Event name constants matching the @vizor-vr/player event system.
 */
final class EventMap
{
    // Standard HTML5 media events
    public const PLAY = 'play';

    public const PAUSE = 'pause';

    public const PLAYING = 'playing';

    public const SEEKING = 'seeking';

    public const SEEKED = 'seeked';

    public const ENDED = 'ended';

    public const LOADSTART = 'loadstart';

    public const PROGRESS = 'progress';

    public const SUSPEND = 'suspend';

    public const ABORT = 'abort';

    public const ERROR = 'error';

    public const EMPTIED = 'emptied';

    public const STALLED = 'stalled';

    public const LOADED_METADATA = 'loadedmetadata';

    public const LOADED_DATA = 'loadeddata';

    public const CAN_PLAY = 'canplay';

    public const CAN_PLAY_THROUGH = 'canplaythrough';

    public const DURATION_CHANGE = 'durationchange';

    public const TIME_UPDATE = 'timeupdate';

    public const RATE_CHANGE = 'ratechange';

    public const RESIZE = 'resize';

    public const VOLUME_CHANGE = 'volumechange';

    public const WAITING = 'waiting';

    // Vizor custom events
    public const VZ_READY = 'vz-ready';

    public const VZ_LOADING_START = 'vz-loading-start';

    public const VZ_LOADING_PROGRESS = 'vz-loading-progress';

    public const VZ_LOADING_COMPLETE = 'vz-loading-complete';

    public const VZ_QUALITY_CHANGE = 'vz-quality-change';

    public const VZ_XR_ENTER = 'vz-xr-enter';

    public const VZ_XR_EXIT = 'vz-xr-exit';

    public const VZ_FULLSCREEN_ENTER = 'vz-fullscreen-enter';

    public const VZ_FULLSCREEN_EXIT = 'vz-fullscreen-exit';

    public const VZ_ORIENTATION_CHANGE = 'vz-orientation-change';

    public const VZ_TOUR_NAVIGATE = 'vz-tour-navigate';

    public const VZ_CHAPTER_CHANGE = 'vz-chapter-change';

    public const VZ_COLLAB_JOIN = 'vz-collab-join';

    public const VZ_COLLAB_LEAVE = 'vz-collab-leave';

    public const VZ_ERROR = 'vz-error';

    public const VZ_LICENSE = 'vz-license';

    public const VZ_PLAYLIST_CHANGE = 'vz-playlist-change';

    public const VZ_PLAYLIST_END = 'vz-playlist-end';

    /**
     * All standard HTML5 media event names.
     *
     * @return array<int, string>
     */
    public static function mediaEvents(): array
    {
        return [
            self::PLAY, self::PAUSE, self::PLAYING, self::SEEKING, self::SEEKED,
            self::ENDED, self::LOADSTART, self::PROGRESS, self::SUSPEND, self::ABORT,
            self::ERROR, self::EMPTIED, self::STALLED, self::LOADED_METADATA,
            self::LOADED_DATA, self::CAN_PLAY, self::CAN_PLAY_THROUGH,
            self::DURATION_CHANGE, self::TIME_UPDATE, self::RATE_CHANGE,
            self::RESIZE, self::VOLUME_CHANGE, self::WAITING,
        ];
    }

    /**
     * All Vizor custom event names.
     *
     * @return array<int, string>
     */
    public static function vizorEvents(): array
    {
        return [
            self::VZ_READY, self::VZ_LOADING_START, self::VZ_LOADING_PROGRESS,
            self::VZ_LOADING_COMPLETE, self::VZ_QUALITY_CHANGE, self::VZ_XR_ENTER,
            self::VZ_XR_EXIT, self::VZ_FULLSCREEN_ENTER, self::VZ_FULLSCREEN_EXIT,
            self::VZ_ORIENTATION_CHANGE, self::VZ_TOUR_NAVIGATE, self::VZ_CHAPTER_CHANGE,
            self::VZ_COLLAB_JOIN, self::VZ_COLLAB_LEAVE, self::VZ_ERROR, self::VZ_LICENSE,
            self::VZ_PLAYLIST_CHANGE, self::VZ_PLAYLIST_END,
        ];
    }

    /**
     * All event names combined (media + custom).
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_merge(self::mediaEvents(), self::vizorEvents());
    }

    /**
     * Events commonly forwarded to the server via Livewire.
     *
     * @return array<int, string>
     */
    public static function livewireEvents(): array
    {
        return [
            self::PLAY, self::PAUSE, self::ENDED, self::TIME_UPDATE,
            self::VZ_READY, self::VZ_ERROR, self::VZ_FULLSCREEN_ENTER,
            self::VZ_FULLSCREEN_EXIT, self::VZ_QUALITY_CHANGE,
        ];
    }
}
