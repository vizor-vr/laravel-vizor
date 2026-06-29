<?php

use Vizor\Laravel\Support\FormatEnum;

// ══════════════════════════════════════════════════════════════════════
//  VzVideo
// ══════════════════════════════════════════════════════════════════════

describe('VzVideo Blade component', function () {

    it('renders the vz-video custom element tag', function () {
        $view = $this->blade('<x-vizor-video />');

        $view->assertSee('<vz-video', false);
        $view->assertSee('</vz-video>', false);
    });

    it('maps src and format props to HTML attributes', function () {
        $view = $this->blade('<x-vizor-video src="test.mp4" :format="$format" />', [
            'format' => FormatEnum::MONO_360,
        ]);

        $view->assertSee('src="test.mp4"', false);
        $view->assertSee('format="MONO_360"', false);
    });

    it('renders muted as a standalone boolean attribute', function () {
        $view = $this->blade('<x-vizor-video :muted="true" />');

        $view->assertSee('muted', false);
    });

    it('renders hide-controls when controls is false', function () {
        $view = $this->blade('<x-vizor-video :controls="false" />');

        $view->assertSee('hide-controls', false);
    });

    it('renders source children from the sources array', function () {
        $view = $this->blade('<x-vizor-video :sources="$sources" />', [
            'sources' => [
                ['src' => 'video-720.mp4', 'type' => 'video/mp4', 'quality' => '720p'],
                ['src' => 'video-1080.mp4', 'type' => 'video/mp4', 'quality' => '1080p'],
            ],
        ]);

        $view->assertSee('<source src="video-720.mp4"', false);
        $view->assertSee('type="video/mp4"', false);
        $view->assertSee('data-quality="720p"', false);
        $view->assertSee('<source src="video-1080.mp4"', false);
        $view->assertSee('data-quality="1080p"', false);
    });

    it('passes slot content through', function () {
        $view = $this->blade('<x-vizor-video><p>Custom overlay</p></x-vizor-video>');

        $view->assertSee('<p>Custom overlay</p>', false);
    });

    it('includes Alpine x-data and x-ref attributes', function () {
        $view = $this->blade('<x-vizor-video />');

        $view->assertSee('x-data="vizorPlayer"', false);
        $view->assertSee('x-ref="player"', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  VzImg
// ══════════════════════════════════════════════════════════════════════

describe('VzImg Blade component', function () {

    it('renders the vz-img custom element tag', function () {
        $view = $this->blade('<x-vizor-img />');

        $view->assertSee('<vz-img', false);
        $view->assertSee('</vz-img>', false);
    });

    it('maps src and format props to HTML attributes', function () {
        $view = $this->blade('<x-vizor-img src="pano.jpg" :format="$format" />', [
            'format' => FormatEnum::MONO_360,
        ]);

        $view->assertSee('src="pano.jpg"', false);
        $view->assertSee('format="MONO_360"', false);
    });

    it('renders title and poster attributes', function () {
        $view = $this->blade('<x-vizor-img title="My Pano" poster="thumb.jpg" />');

        $view->assertSee('title="My Pano"', false);
        $view->assertSee('poster="thumb.jpg"', false);
    });

    it('passes slot content through', function () {
        $view = $this->blade('<x-vizor-img><span>Hotspot</span></x-vizor-img>');

        $view->assertSee('<span>Hotspot</span>', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  VzTour
// ══════════════════════════════════════════════════════════════════════

describe('VzTour Blade component', function () {

    it('renders the vz-tour custom element tag', function () {
        $view = $this->blade('<x-vizor-tour />');

        $view->assertSee('<vz-tour', false);
        $view->assertSee('</vz-tour>', false);
    });

    it('maps src and format props to HTML attributes', function () {
        $view = $this->blade('<x-vizor-tour src="tour.json" :format="$format" />', [
            'format' => FormatEnum::MONO_360,
        ]);

        $view->assertSee('src="tour.json"', false);
        $view->assertSee('format="MONO_360"', false);
    });

    it('renders start-probe-id attribute', function () {
        $view = $this->blade('<x-vizor-tour start-probe-id="probe-1" />');

        $view->assertSee('start-probe-id="probe-1"', false);
    });

    it('passes slot content through', function () {
        $view = $this->blade('<x-vizor-tour><div>Tour child</div></x-vizor-tour>');

        $view->assertSee('<div>Tour child</div>', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  VzCinema
// ══════════════════════════════════════════════════════════════════════

describe('VzCinema Blade component', function () {

    it('renders the vz-cinema custom element tag', function () {
        $view = $this->blade('<x-vizor-cinema />');

        $view->assertSee('<vz-cinema', false);
        $view->assertSee('</vz-cinema>', false);
    });

    it('maps src and format props to HTML attributes', function () {
        $view = $this->blade('<x-vizor-cinema src="movie.mp4" :format="$format" />', [
            'format' => FormatEnum::MONO_FLAT,
        ]);

        $view->assertSee('src="movie.mp4"', false);
        $view->assertSee('format="MONO_FLAT"', false);
    });

    it('renders muted as a standalone boolean attribute', function () {
        $view = $this->blade('<x-vizor-cinema :muted="true" />');

        $view->assertSee('muted', false);
    });

    it('renders source children from the sources array', function () {
        $view = $this->blade('<x-vizor-cinema :sources="$sources" />', [
            'sources' => [
                ['src' => 'cinema.webm', 'type' => 'video/webm', 'quality' => '4K'],
            ],
        ]);

        $view->assertSee('<source src="cinema.webm"', false);
        $view->assertSee('type="video/webm"', false);
        $view->assertSee('data-quality="4K"', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  VzLive
// ══════════════════════════════════════════════════════════════════════

describe('VzLive Blade component', function () {

    it('renders the vz-live custom element tag', function () {
        $view = $this->blade('<x-vizor-live />');

        $view->assertSee('<vz-live', false);
        $view->assertSee('</vz-live>', false);
    });

    it('maps src and format props to HTML attributes', function () {
        $view = $this->blade('<x-vizor-live src="https://stream.example.com/live.m3u8" :format="$format" />', [
            'format' => FormatEnum::MONO_360,
        ]);

        $view->assertSee('src="https://stream.example.com/live.m3u8"', false);
        $view->assertSee('format="MONO_360"', false);
    });

    it('renders source children from the sources array', function () {
        $view = $this->blade('<x-vizor-live :sources="$sources" />', [
            'sources' => [
                ['src' => 'stream.m3u8', 'type' => 'application/x-mpegURL'],
            ],
        ]);

        $view->assertSee('<source src="stream.m3u8"', false);
        $view->assertSee('type="application/x-mpegURL"', false);
    });

    it('renders hide-controls when controls is false', function () {
        $view = $this->blade('<x-vizor-live :controls="false" />');

        $view->assertSee('hide-controls', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  VzPlaylist
// ══════════════════════════════════════════════════════════════════════

describe('VzPlaylist Blade component', function () {

    it('renders the vz-playlist custom element tag', function () {
        $view = $this->blade('<x-vizor-playlist />');

        $view->assertSee('<vz-playlist', false);
        $view->assertSee('</vz-playlist>', false);
    });

    it('renders loop-playlist boolean attribute', function () {
        $view = $this->blade('<x-vizor-playlist :loop-playlist="true" />');

        $view->assertSee('loop-playlist', false);
    });

    it('renders panel attribute', function () {
        $view = $this->blade('<x-vizor-playlist panel="bottom" />');

        $view->assertSee('panel="bottom"', false);
    });

    it('passes slot content through for playlist items', function () {
        $view = $this->blade(
            '<x-vizor-playlist>' .
            '<x-vizor-video src="a.mp4" />' .
            '</x-vizor-playlist>'
        );

        $view->assertSee('<vz-playlist', false);
        $view->assertSee('<vz-video', false);
    });

});

// ══════════════════════════════════════════════════════════════════════
//  VzAnnotation
// ══════════════════════════════════════════════════════════════════════

describe('VzAnnotation Blade component', function () {

    it('renders the vz-annotation custom element tag', function () {
        $view = $this->blade('<x-vizor-annotation />');

        $view->assertSee('<vz-annotation', false);
        $view->assertSee('</vz-annotation>', false);
    });

    it('renders lat, lon, and title attributes', function () {
        $view = $this->blade('<x-vizor-annotation :lat="45.5" :lon="-73.5" title="Montreal" />');

        $view->assertSee('lat="45.5"', false);
        $view->assertSee('lon="-73.5"', false);
        $view->assertSee('title="Montreal"', false);
    });

    it('renders time-start and time-end attributes', function () {
        $view = $this->blade('<x-vizor-annotation :time-start="5.0" :time-end="15.0" />');

        $view->assertSee('time-start="5"', false);
        $view->assertSee('time-end="15"', false);
    });

    it('passes slot content through for popup body', function () {
        $view = $this->blade('<x-vizor-annotation title="Info"><p>Details here</p></x-vizor-annotation>');

        $view->assertSee('<p>Details here</p>', false);
    });

});
