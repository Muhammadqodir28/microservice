<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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

        $product->likes++;
        $product->save();
    }
}
