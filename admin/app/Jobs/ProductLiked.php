<?php

namespace App\Jobs;

use App\Events\ProductChanged;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProductLiked implements ShouldQueue
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
        $product = Product::find($this->data['product_id']);

        if (! $product) {
            return;
        }

        $product->increment('likes');
        try {
            event(new ProductChanged('liked', $product->fresh()->toArray()));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
