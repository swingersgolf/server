<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        Log::info("Inside constructor of messageevent");

        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        Log::info("Inside BroadcastOn");
        return [
            new PrivateChannel('message-group.' . $this->message->message_group_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => 'foobar',
        ];
    }

    public function broadcastAs(): string
    {
        return 'my-event';
    }
}
