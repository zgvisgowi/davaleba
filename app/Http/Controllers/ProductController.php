<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create', 'read', 'update', 'delete']]);
    }

    public function create(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|min:2',
            'categories' => 'required|array',
            'categories.*' => 'required|exists:categories,id'
        ]);

        if (!$validate->fails()) {
            Product::create($validate->safe(['name']))->categories()->attach($request->input('categories'));
            return response()->json(['success' => true, 'message' => 'New product has been added successfully.']);
        }

        return response()->json(['success' => false, 'message' => $validate->errors()->first()]);
    }

    public function read(int $productId): JsonResponse
    {
        if ($product = Product::find($productId)) {
            return response()->json(['product' => new ProductResource($product)]);
        }
        return response()->json(['success' => false, 'message' => 'Product not found.']);
    }

    public function update(Request $request, int $productId): JsonResponse
    {
        if ($product = Product::find($productId)) {
            $validate = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'min:2'
                ],
                'categories' => 'required|array',
                'categories.*' => 'required|exists:categories,id'
            ]);

            if (!$validate->fails()) {
                $product->update($validate->safe(['name']));

                $product->categories()->sync($request->input('categories'));
                return response()->json(['success' => true, 'message' => 'Product has been updated successfully.']);
            }
            return response()->json(['success' => false, 'message' => $validate->errors()->first()]);
        }
        return response()->json(['success' => false, 'message' => 'Product not found.']);
    }

    public function delete(int $productId): JsonResponse
    {
        if ($product = Product::find($productId)) {
            $product->delete();
            return response()->json(['success' => true, 'message' => 'Product has been deleted.']);
        }
        return response()->json(['success' => false, 'message' => 'Product not found.']);
    }
}
