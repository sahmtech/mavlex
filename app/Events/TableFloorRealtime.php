<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TableFloorRealtime implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $businessId;

    public $eventName;

    public $payload;

    public function __construct($businessId, $eventName, array $payload = [])
    {
        $this->businessId = (int) $businessId;
        $this->eventName = $eventName;
        $this->payload = $payload;

        $this->forwardToSocketIo();
    }

    public function broadcastOn()
    {
        return new Channel('table-orders.'.$this->businessId);
    }

    public function broadcastAs()
    {
        return $this->eventName;
    }

    public function broadcastWith()
    {
        return $this->payload;
    }

    protected function forwardToSocketIo()
    {
        $url = env('SOCKET_IO_URL');
        if (empty($url)) {
            return;
        }

        try {
            Http::timeout(2)->post(rtrim($url, '/').'/emit', [
                'event' => $this->eventName,
                'room' => 'table-orders.'.$this->businessId,
                'payload' => $this->payload,
            ]);
        } catch (\Throwable $e) {
            Log::debug('Socket.IO emit skipped: '.$e->getMessage());
        }
    }
};
