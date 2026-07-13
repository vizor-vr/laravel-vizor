<?php

namespace Vizor\Laravel\Livewire;

use Livewire\Component;
use Vizor\Laravel\Support\FormatEnum;
use Vizor\Laravel\Traits\HasVizorEvents;
use Vizor\Laravel\Traits\HasVizorProps;

class ImgViewer extends Component
{
    use HasVizorEvents, HasVizorProps;

    public bool $ready = false;

    // Props
    public ?string $src = null;

    public ?FormatEnum $format = null;

    public ?string $title = null;

    public ?string $poster = null;

    public ?string $apiKey = null;

    public ?string $licenseKey = null;

    public ?string $apiEndpoint = null;

    public ?string $primaryColor = null;

    public ?string $contentId = null;

    public function onReady(): void
    {
        $this->ready = true;
    }

    public function onError(string $code, string $message): void
    {
        $this->broadcastIfEnabled('player.error', ['code' => $code, 'message' => $message]);
    }

    public function render()
    {
        return view('vizor::livewire.img-viewer');
    }
}
