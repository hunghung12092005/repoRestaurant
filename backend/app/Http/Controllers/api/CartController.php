<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartController extends Controller
{
    // Thêm sản phẩm vào giỏ hàng
    public function addToCart(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        // Kiểm tra sản phẩm có tồn tại
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Thêm sản phẩm vào giỏ hàng
        $cart = Cart::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $productId],
            ['quantity' => \DB::raw("quantity + $quantity")]
        );

        return response()->json(['message' => 'Product added to cart', 'cart' => $cart]);
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function updateCart(Request $request, $cartId)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $cartItem = Cart::find($cartId);

        if (!$cartItem || $cartItem->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $quantity = $request->input('quantity');
        $cartItem->update(['quantity' => $quantity]);

        return response()->json(['message' => 'Cart updated successfully']);
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeFromCart($cartId)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $cartItem = Cart::find($cartId);

        if (!$cartItem || $cartItem->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cartItem->delete();
        return response()->json(['message' => 'Product removed from cart']);
    }

    // Thanh toán
    public function checkout(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $cartItems = Cart::where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Xử lý thanh toán (giả định)
        // Xóa giỏ hàng sau khi thanh toán
        Cart::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Checkout successful']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
