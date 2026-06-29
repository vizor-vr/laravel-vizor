<?php

namespace Vizor\Laravel\Livewire;

use Livewire\Component;
use Vizor\Laravel\Traits\HasVizorEvents;

class PlaylistPlayer extends Component
{
    use HasVizorEvents;

    // Reactive state
    public int $currentIndex = 0;
    public ?string $currentTitle = null;
    public int $totalItems = 0;
    public bool $playing = false;
    public bool $ready = false;

    // Props
    public bool $autoplay = false;
    public bool $loopPlaylist = false;
    public ?string $panel = null;
    public ?string $apiKey = null;
    public ?string $licenseKey = null;
    public ?string $primaryColor = null;
    public ?string $contentId = null;

    public function next(): void
    {
        $this->dispatch('vizor-command', command: 'next');
    }

    public function previous(): void
    {
        $this->dispatch('vizor-command', command: 'previous');
    }

    public function goTo(int $index): void
    {
        $this->dispatch('vizor-command', command: 'goTo', index: $index);
    }

    public function onReady(): void
    {
        $this->ready = true;
    }

    public function onPlay(): void
    {
        $this->playing = true;
    }

    public function onPause(): void
    {
        $this->playing = false;
    }

    public function onEnded(): void
    {
        $this->playing = false;
    }

    public function onPlaylistChange(int $index, string $title, int $total): void
    {
        $this->currentIndex = $index;
        $this->currentTitle = $title;
        $this->totalItems = $total;
    }

    public function onPlaylistEnd(): void
    {
        $this->playing = false;
    }

    public function render()
    {
        return view('vizor::livewire.playlist-player');
    }
}
