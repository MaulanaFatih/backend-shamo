<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $description = $request->input('description');

        $tags = $request->input('tags');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if ($id) {
            $product = Product::with(['category', 'galleries'])->find($id);

            if ($product) {
                return ResponseFormatter::success(
                    $product,
                    'Success',

                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'not found product data',
                    404
                );
            }
        }

        $product = Product::with('category', 'galleries');

        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }
        if ($description) {
            $product->where('description', 'like', '%' . $description . '%');
        }
        if ($tags) {
            $product->where('tags', 'like', '%' . $tags . '%');
        }
        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }
        if ($price_to) {
            $product->where('price', '>=', $price_to);
        }
        if ($categories) {
            $product->where('categories', $categories);
        }
        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data Product Successfully Fetched',

        );
    }
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            // Add other validation rules as needed
        ]);

        $product = Product::create($request->all());

        return ResponseFormatter::success(
            $product,
            'Product Created Successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ResponseFormatter::error(
                null,
                'Product not found',
                404
            );
        }

        $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric',
            // Add other validation rules as needed
        ]);

        $product->update($request->all());

        return ResponseFormatter::success(
            $product,
            'Product Updated Successfully'
        );
    }

}
