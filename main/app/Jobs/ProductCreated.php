<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProductCreated implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private $data)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Product::updateOrCreate(
            ['id' => $this->data['id']],
            [
                'title' => $this->data['title'],
                'image' => $this->data['image'],
            ]
        );
    }
}
