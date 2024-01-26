<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function addProduct(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if (!$validate->fails()) {
            $cart = auth()->user()->cart;
            $productId = $request->get('product_id');

            if ($product = $cart->products()->find($productId)) {
                $cart->products()->updateExistingPivot($productId, ['quantity' => $product->pivot->quantity + 1]);
            } else {
                $cart->products()->attach($request->get('product_id'));
            }

            return response()->json(['success' => true, 'message' => 'New product has been added in the cart.']);
        }
        return response()->json(['success' => false, 'message' => $validate->errors()->first()]);
    }

    public function removeProduct(Request $request, int $productId): JsonResponse
    {
        $cart = auth()->user()->cart;
        if (in_array($productId, $cart->products()->pluck('products.id')->toArray())) {
            $cart->products()->detach($productId);
            return response()->json(['success' => true, 'message' => 'Product has been removed from the cart.']);
        }
        return response()->json(['success' => false, 'message' => 'Product not found in the cart.']);
    }

    public function increaseQuantity(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'product_id' => 'required|numeric',
        ]);

        if (!$validate->fails()) {
            $productId = $request->get('product_id');
            $cart = auth()->user()->cart;
            if ($product = $cart->products()->find($productId)) {
                $cart->products()->updateExistingPivot($productId, ['quantity' => max(1, $product->pivot->quantity + 1)]);
                return response()->json(['success' => true, 'message' => 'Product quantity increased.']);
            }
            return response()->json(['success' => false, 'message' => 'Product not found in the cart.']);
        }
        return response()->json(['success' => false, 'message' => $validate->errors()->first()]);
    }

    public function decreaseQuantity(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'product_id' => 'required|numeric',
        ]);

        if (!$validate->fails()) {
            $productId = $request->get('product_id');
            $cart = auth()->user()->cart;
            if ($product = $cart->products()->find($productId)) {
                $updatedQuantity = max(0, $product->pivot->quantity - 1);
                if ($updatedQuantity == 0) {
                    $cart->products()->detach($productId);
                    return response()->json(['success' => true, 'message' => 'Product has been removed from the cart.']);
                } else {
                    $cart->products()->updateExistingPivot($productId, ['quantity' => max(1, $product->pivot->quantity - 1)]);
                    return response()->json(['success' => true, 'message' => 'Product quantity decreased.']);
                }
            }
            return response()->json(['success' => false, 'message' => 'Product not found in the cart.']);
        }
        return response()->json(['success' => false, 'message' => $validate->errors()->first()]);
    }

    public function makeOrder(): JsonResponse
    {
        $user = auth()->user();
        $cart = $user->cart;

        $cartProducts = $cart->products();

        if ($cartProducts->count() > 0) {
            $order = $user->orders()->create();

            foreach ($cartProducts->get() as $product) {
                $order->products()->attach($product->id, ['quantity' => $product->pivot->quantity]);
                $cart->products()->detach($product->id);
            }
            return response()->json(['success' => true, 'message' => 'Order created.']);
        }
        return response()->json(['success' => false, 'message' => 'Cart is empty.']);
    }
}
