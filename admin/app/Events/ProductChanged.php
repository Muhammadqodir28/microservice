<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ProductChanged implements ShouldBroadcastNow
{
    public function __construct(
        public string $type,
        public array $product,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('products')];
    }

    public function broadcastAs(): string
    {
        return 'product.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'product' => $this->product,
        ];
    }
}
