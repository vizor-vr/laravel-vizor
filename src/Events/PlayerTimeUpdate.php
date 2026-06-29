<?php

namespace Vizor\Laravel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerTimeUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $contentId,
        public readonly int|string|null $userId = null,
        public readonly float $currentTime = 0,
        public readonly float $duration = 0,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel(config('vizor.broadcasting.channel_prefix', 'vizor').".{$this->contentId}"),
        ];
    }

    /**
     * Data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'contentId' => $this->contentId,
            'userId' => $this->userId,
            'currentTime' => $this->currentTime,
            'duration' => $this->duration,
        ];
    }

    /**
     * Determine if the event should be broadcast.
     */
    public function broadcastWhen(): bool
    {
        return (bool) config('vizor.broadcasting.enabled', false);
    }
}
