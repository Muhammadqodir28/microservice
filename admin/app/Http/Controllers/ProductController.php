<?php

namespace App\Http\Controllers;

use App\Events\ProductChanged;
use App\Models\Product;
use Illuminate\Http\Request;
use Throwable;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function show($id)
    {
        return Product::find($id);
    }

    public function store(Request $request)
    {
        $product = Product::create($request->only('title', 'image'));

        $this->broadcastChange('created', $product->toArray());

        return response()->json($product, 201);
    }

    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);

        $product->update($request->only('title', 'image'));

        $this->broadcastChange('updated', $product->fresh()->toArray());

        return response()->json($product, 201);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        $this->broadcastChange('deleted', ['id' => (int) $id]);

        return response()->json(null, 204);
    }

    private function broadcastChange(string $type, array $product): void
    {
        try {
            event(new ProductChanged($type, $product));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
