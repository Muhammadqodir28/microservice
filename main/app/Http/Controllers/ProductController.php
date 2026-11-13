<?php

namespace App\Http\Controllers;

use App\Jobs\ProductLiked;
use App\Models\Product;
use App\Models\ProductUser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    /**
     * @throws ConnectionException
     */
    public function like($id, Request $request)
    {
        $response = Http::get('http://admin:8000/api/user');

        $user = $response->json();

        try {
            $product = ProductUser::query()
                ->where('user_id', $user['id'])
                ->where('product_id', $id)
                ->first();

            if (! $product) {
                $productUser = ProductUser::create([
                    'user_id' => $user['id'],
                    'product_id' => $id
                ]);

                ProductLiked::dispatch($productUser->toArray());

                return response([
                    'message' => 'success'
                ]);
            }

            return response([
                'message' => 'already liked'
            ], 400);
        } catch (\Exception $exception) {
            return response([
                'text' => 'You already liked this product!',
                'error' => $exception->getMessage()
            ], 400);
        }
    }
}
