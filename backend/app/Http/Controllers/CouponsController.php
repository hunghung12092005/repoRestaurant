<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    public function index()
    {
        return Discount::orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:hotel_coupons,code',
            'description' => 'nullable|string',
            'discount_amount' => 'required|integer|min:0',
            'usage_limit' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'expires_at' => 'nullable|date'
        ]);

        $data['used_count'] = 0;

        return Discount::create($data);
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        // Kiểm tra nếu mã đã được sử dụng
        if ($discount->used_count > 0) {
            return response()->json(['message' => 'Không thể cập nhật mã giảm giá đã được sử dụng.'], 403);
        }

        $data = $request->validate([
            'code' => 'required|string|unique:hotel_coupons,code,' . $discount->id,
            'description' => 'nullable|string',
            'discount_amount' => 'required|integer|min:0',
            'usage_limit' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'expires_at' => 'nullable|date'
        ]);
        $discount->update($data);

        return $discount;
    }

    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);

        // Kiểm tra nếu mã đã được sử dụng
        if ($discount->used_count > 0) {
            return response()->json(['message' => 'Không thể xóa mã giảm giá đã được sử dụng.'], 403);
        }

        $discount->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function getDiscountAmount(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $discount = Discount::where('code', $request->code)->first();

        if (!$discount) {
            return response()->json(['message' => 'Mã giảm giá không tồn tại'], 404);
        }

        if (!$discount->is_active) {
            return response()->json(['message' => 'Mã giảm giá không hoạt động'], 400);
        }

        if ($discount->used_count >= $discount->usage_limit) {
            return response()->json(['message' => 'Mã giảm giá đã hết lượt sử dụng'], 400);
        }

        $currentDate = now();
        if ($discount->expires_at !== null && $currentDate >= $discount->expires_at) {
            return response()->json(['message' => 'Mã giảm giá đã hết hạn'], 400);
        }

        // Tăng lượt sử dụng lên 1
        $discount->increment('used_count'); 

        if ($discount->used_count >= $discount->usage_limit) {
            $discount->is_active = false;
            $discount->save();
        }
        
        return response()->json([
            'discount_amount' => $discount->discount_amount,
            'discount_id' => $discount->id,
            'message' => 'Áp dụng mã giảm giá thành công!'
        ]);
    }
}