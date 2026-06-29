<?php

namespace Vizor\Laravel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerError implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $contentId,
        public readonly int|string|null $userId = null,
        public readonly string $code = '',
        public readonly string $message = '',
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
            'code' => $this->code,
            'message' => $this->message,
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
