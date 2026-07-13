<?php

namespace Vizor\Laravel\Livewire;

use Livewire\Component;
use Vizor\Laravel\Support\FormatEnum;
use Vizor\Laravel\Traits\HasVizorEvents;
use Vizor\Laravel\Traits\HasVizorProps;

class VideoPlayer extends Component
{
    use HasVizorEvents, HasVizorProps;

    // Reactive state (wire:model bindable)
    public float $currentTime = 0;

    public float $duration = 0;

    public float $volume = 1;

    public bool $playing = false;

    public bool $isMuted = false;

    public bool $fullscreen = false;

    public bool $ready = false;

    // Props
    public ?string $src = null;

    public ?FormatEnum $format = null;

    public ?string $title = null;

    public ?string $poster = null;

    public bool $loop = false;

    public bool $controls = true;

    public bool $autoplay = false;

    public ?string $preload = null;

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

    public function seek(float $time): void
    {
        $this->currentTime = $time;
        $this->dispatch('vizor-command', command: 'seek', time: $time);
    }

    public function setVolume(float $vol): void
    {
        $this->volume = $vol;
        $this->dispatch('vizor-command', command: 'setVolume', volume: $vol);
    }

    public function onReady(): void
    {
        $this->ready = true;
        $this->broadcastIfEnabled('player.ready');
    }

    public function onTimeUpdate(float $time, float $dur): void
    {
        $this->currentTime = $time;
        $this->duration = $dur;
        $this->broadcastIfEnabled('player.timeupdate');
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
        return view('vizor::livewire.video-player');
    }
}
