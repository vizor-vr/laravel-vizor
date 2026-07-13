<?php

namespace Vizor\Laravel\Components;

use Illuminate\View\Component;

/**
 * Caption/subtitle track for a Vizor player element (WS-G).
 *
 * The player has no <vz-caption> custom element — captions are standard HTML5
 * <track kind="subtitles"> children the player picks up (mirrors the React
 * wrapper's VzCaption). Nest inside <x-vizor-video>:
 *
 *   <x-vizor-video format="MONO_360" :sources="[...]">
 *     <x-vizor-caption src="/subs-en.vtt" srclang="en" label="English" default />
 *   </x-vizor-video>
 */
final class VzCaption extends Component
{
    public function __construct(
        public readonly string $src,
        public readonly string $srclang,
        public readonly ?string $label = null,
        public readonly bool $default = false,
    ) {}

    public function render()
    {
        return view('vizor::components.caption');
    }
}
