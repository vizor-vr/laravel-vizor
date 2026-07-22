<?php

use Livewire\Livewire;
use Vizor\Laravel\Livewire\CinemaPlayer;
use Vizor\Laravel\Livewire\ImgViewer;
use Vizor\Laravel\Livewire\LivePlayer;
use Vizor\Laravel\Livewire\PlaylistPlayer;
use Vizor\Laravel\Livewire\TourViewer;
use Vizor\Laravel\Livewire\VideoPlayer;
use Vizor\Laravel\Support\FormatEnum;

beforeEach(function () {
    // Livewire views use {{ $slot }} which is only populated when
    // components are rendered as Blade components with content.
    // When testing via Livewire::test(), slot is not defined,
    // so we share an empty default.
    view()->share('slot', '');
});

// ══════════════════════════════════════════════════════════════════════
//  VideoPlayer
// ══════════════════════════════════════════════════════════════════════

describe('VideoPlayer Livewire component', function () {

    it('mounts with default state', function () {
        Livewire::test(VideoPlayer::class)
            ->assertSet('playing', false)
            ->assertSet('currentTime', 0)
            ->assertSet('duration', 0)
            ->assertSet('volume', 1)
            ->assertSet('isMuted', false)
            ->assertSet('ready', false);
    });

    it('renders vz-video element', function () {
        Livewire::test(VideoPlayer::class)
            ->assertSee('<vz-video', false);
    });

    it('renders with src and format props', function () {
        Livewire::test(VideoPlayer::class, [
            'src' => 'video.mp4',
            'format' => FormatEnum::MONO_360,
        ])
            ->assertSee('src="video.mp4"', false)
            ->assertSee('format="MONO_360"', false);
    });

    it('sets playing to true and dispatches vizor-command on play()', function () {
        Livewire::test(VideoPlayer::class)
            ->call('play')
            ->assertSet('playing', true)
            ->assertDispatched('vizor-command', command: 'play');
    });

    it('sets playing to false and dispatches vizor-command on pause()', function () {
        Livewire::test(VideoPlayer::class)
            ->call('play')
            ->call('pause')
            ->assertSet('playing', false)
            ->assertDispatched('vizor-command', command: 'pause');
    });

    it('updates currentTime and dispatches vizor-command on seek()', function () {
        Livewire::test(VideoPlayer::class)
            ->call('seek', 42.5)
            ->assertSet('currentTime', 42.5)
            ->assertDispatched('vizor-command', command: 'seek', time: 42.5);
    });

    it('syncs currentTime and duration on onTimeUpdate()', function () {
        Livewire::test(VideoPlayer::class)
            ->call('onTimeUpdate', 10.0, 120.0)
            ->assertSet('currentTime', 10.0)
            ->assertSet('duration', 120.0);
    });

    it('sets playing to true on onPlay()', function () {
        Livewire::test(VideoPlayer::class)
            ->call('onPlay')
            ->assertSet('playing', true);
    });

    it('sets playing to false on onPause()', function () {
        Livewire::test(VideoPlayer::class)
            ->call('play')
            ->call('onPause')
            ->assertSet('playing', false);
    });

    it('sets playing to false on onEnded()', function () {
        Livewire::test(VideoPlayer::class)
            ->call('play')
            ->call('onEnded')
            ->assertSet('playing', false);
    });

    it('renders content-id and api credentials on the vz-video element', function () {
        Livewire::test(VideoPlayer::class, [
            'contentId' => 'cnt_123',
            'apiKey' => 'vz_live_k',
            'apiEndpoint' => 'https://api.vizor-vr.com',
        ])
            ->assertSee('content-id="cnt_123"', false)
            ->assertSee('api-key="vz_live_k"', false)
            ->assertSee('api-endpoint="https://api.vizor-vr.com"', false);
    });

    it('renders license-key on the vz-video element', function () {
        Livewire::test(VideoPlayer::class, [
            'licenseKey' => 'VZR-XXXX',
        ])
            ->assertSee('license-key="VZR-XXXX"', false);
    });

    it('does not render content-id, api-key, license-key, or api-endpoint when unset', function () {
        Livewire::test(VideoPlayer::class, [
            'src' => 'video.mp4',
        ])
            ->assertDontSee('content-id=', false)
            ->assertDontSee('api-key=', false)
            ->assertDontSee('license-key=', false)
            ->assertDontSee('api-endpoint=', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  ImgViewer
// ══════════════════════════════════════════════════════════════════════

describe('ImgViewer Livewire component', function () {

    it('mounts with ready=false', function () {
        Livewire::test(ImgViewer::class)
            ->assertSet('ready', false);
    });

    it('renders vz-img element', function () {
        Livewire::test(ImgViewer::class)
            ->assertSee('<vz-img', false);
    });

    it('sets ready to true on onReady()', function () {
        Livewire::test(ImgViewer::class)
            ->call('onReady')
            ->assertSet('ready', true);
    });

    it('renders with src and format props', function () {
        Livewire::test(ImgViewer::class, [
            'src' => 'panorama.jpg',
            'format' => FormatEnum::MONO_360,
        ])
            ->assertSee('src="panorama.jpg"', false)
            ->assertSee('format="MONO_360"', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  TourViewer
// ══════════════════════════════════════════════════════════════════════

describe('TourViewer Livewire component', function () {

    it('mounts with currentProbeId=null', function () {
        Livewire::test(TourViewer::class)
            ->assertSet('currentProbeId', null);
    });

    it('renders vz-tour element', function () {
        Livewire::test(TourViewer::class)
            ->assertSee('<vz-tour', false);
    });

    it('updates currentProbeId on onTourNavigate()', function () {
        Livewire::test(TourViewer::class)
            ->call('onTourNavigate', 'probe-1', 'probe-2')
            ->assertSet('currentProbeId', 'probe-2');
    });

    it('renders with start-probe-id prop', function () {
        Livewire::test(TourViewer::class, [
            'startProbeId' => 'first-probe',
        ])
            ->assertSee('start-probe-id="first-probe"', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  CinemaPlayer
// ══════════════════════════════════════════════════════════════════════

describe('CinemaPlayer Livewire component', function () {

    it('mounts with default state', function () {
        Livewire::test(CinemaPlayer::class)
            ->assertSet('playing', false)
            ->assertSet('currentTime', 0)
            ->assertSet('duration', 0)
            ->assertSet('ready', false);
    });

    it('renders vz-cinema element', function () {
        Livewire::test(CinemaPlayer::class)
            ->assertSee('<vz-cinema', false);
    });

    it('sets playing to true and dispatches command on play()', function () {
        Livewire::test(CinemaPlayer::class)
            ->call('play')
            ->assertSet('playing', true)
            ->assertDispatched('vizor-command', command: 'play');
    });

    it('sets playing to false and dispatches command on pause()', function () {
        Livewire::test(CinemaPlayer::class)
            ->call('play')
            ->call('pause')
            ->assertSet('playing', false)
            ->assertDispatched('vizor-command', command: 'pause');
    });

    it('updates currentTime and dispatches command on seek()', function () {
        Livewire::test(CinemaPlayer::class)
            ->call('seek', 30.5)
            ->assertSet('currentTime', 30.5)
            ->assertDispatched('vizor-command', command: 'seek', time: 30.5);
    });

    it('syncs currentTime and duration on onTimeUpdate()', function () {
        Livewire::test(CinemaPlayer::class)
            ->call('onTimeUpdate', 25.0, 90.0)
            ->assertSet('currentTime', 25.0)
            ->assertSet('duration', 90.0);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  LivePlayer
// ══════════════════════════════════════════════════════════════════════

describe('LivePlayer Livewire component', function () {

    it('mounts with default state', function () {
        Livewire::test(LivePlayer::class)
            ->assertSet('playing', false)
            ->assertSet('ready', false)
            ->assertSet('volume', 1)
            ->assertSet('isMuted', false);
    });

    it('renders vz-live element', function () {
        Livewire::test(LivePlayer::class)
            ->assertSee('<vz-live', false);
    });

    it('sets playing to true and dispatches command on play()', function () {
        Livewire::test(LivePlayer::class)
            ->call('play')
            ->assertSet('playing', true)
            ->assertDispatched('vizor-command', command: 'play');
    });

    it('sets playing to false and dispatches command on pause()', function () {
        Livewire::test(LivePlayer::class)
            ->call('play')
            ->call('pause')
            ->assertSet('playing', false)
            ->assertDispatched('vizor-command', command: 'pause');
    });

    it('updates volume and isMuted on onVolumeChange()', function () {
        Livewire::test(LivePlayer::class)
            ->call('onVolumeChange', 0.5, true)
            ->assertSet('volume', 0.5)
            ->assertSet('isMuted', true);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  PlaylistPlayer
// ══════════════════════════════════════════════════════════════════════

describe('PlaylistPlayer Livewire component', function () {

    it('mounts with default state', function () {
        Livewire::test(PlaylistPlayer::class)
            ->assertSet('currentIndex', 0)
            ->assertSet('currentTitle', null)
            ->assertSet('totalItems', 0)
            ->assertSet('playing', false)
            ->assertSet('ready', false);
    });

    it('renders vz-playlist element', function () {
        Livewire::test(PlaylistPlayer::class)
            ->assertSee('<vz-playlist', false);
    });

    it('dispatches next command on next()', function () {
        Livewire::test(PlaylistPlayer::class)
            ->call('next')
            ->assertDispatched('vizor-command', command: 'next');
    });

    it('dispatches previous command on previous()', function () {
        Livewire::test(PlaylistPlayer::class)
            ->call('previous')
            ->assertDispatched('vizor-command', command: 'previous');
    });

    it('updates index, title, and total on onPlaylistChange()', function () {
        Livewire::test(PlaylistPlayer::class)
            ->call('onPlaylistChange', 2, 'Track Three', 10)
            ->assertSet('currentIndex', 2)
            ->assertSet('currentTitle', 'Track Three')
            ->assertSet('totalItems', 10);
    });

    it('dispatches goTo command with index', function () {
        Livewire::test(PlaylistPlayer::class)
            ->call('goTo', 5)
            ->assertDispatched('vizor-command', command: 'goTo', index: 5);
    });

});
