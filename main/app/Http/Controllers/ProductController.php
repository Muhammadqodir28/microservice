<?php

namespace App\Http\Controllers;

use App\Jobs\ProductLiked;
use App\Models\Product;
use App\Models\ProductUser;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function like(int $id, Request $request)
    {
        $user = $request->user();

        try {
            $productUser = ProductUser::create([
                'user_id' => $user->id,
                'product_id' => $id,
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return response([
                'message' => 'already liked',
            ], 409);
        }

        ProductLiked::dispatch($productUser->toArray())->onQueue('product-likes');

        return response()->json(['message' => 'success'], 202);
    }
}
