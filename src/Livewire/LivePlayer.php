<?php

namespace Vizor\Laravel\Livewire;

use Livewire\Component;
use Vizor\Laravel\Support\FormatEnum;
use Vizor\Laravel\Traits\HasVizorEvents;
use Vizor\Laravel\Traits\HasVizorProps;

/**
 * Live streams have no seekable timeline, so this component intentionally has
 * no onTimeUpdate handler and never broadcasts player.timeupdate.
 */
class LivePlayer extends Component
{
    use HasVizorEvents, HasVizorProps;

    // Reactive state
    public bool $playing = false;

    public bool $ready = false;

    public float $volume = 1;

    public bool $isMuted = false;

    // Props
    public ?string $src = null;

    public ?FormatEnum $format = null;

    public ?string $title = null;

    public ?string $poster = null;

    public bool $controls = true;

    public ?string $apiKey = null;

    public ?string $licenseKey = null;

    public ?string $apiEndpoint = null;

    public ?string $primaryColor = null;

    public ?string $contentId = null;

    public function play(): void
    {
        $this->playing = true;
        $this->dispatch('vizor-command', command: 'play');
    }

    public function pause(): void
    {
        $this->playing = false;
        $this->dispatch('vizor-command', command: 'pause');
    }

    public function onReady(): void
    {
        $this->ready = true;
        $this->broadcastIfEnabled('player.ready');
    }

    public function onPlay(): void
    {
        $this->playing = true;
        $this->broadcastIfEnabled('player.play');
    }

    public function onPause(): void
    {
        $this->playing = false;
        $this->broadcastIfEnabled('player.pause');
    }

    public function onEnded(): void
    {
        $this->playing = false;
        $this->broadcastIfEnabled('player.ended');
    }

    public function onVolumeChange(float $vol, bool $muted): void
    {
        $this->volume = $vol;
        $this->isMuted = $muted;
    }

    public function onError(string $code, string $message): void
    {
        $this->broadcastIfEnabled('player.error', ['code' => $code, 'message' => $message]);
    }

    public function render()
    {
        return view('vizor::livewire.live-player');
    }
}
