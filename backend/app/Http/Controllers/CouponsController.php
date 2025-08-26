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

    // public function update(Request $request, $id)
    // {
    //     $discount = Discount::findOrFail($id);

    //     $data = $request->validate([
    //         'code' => 'required|string|unique:hotel_coupons,code,' . $discount->id,
    //         'description' => 'nullable|string',
    //         'discount_amount' => 'required|integer|min:0',
    //         'usage_limit' => 'required|integer|min:1',
    //         'is_active' => 'required|boolean',
    //         'expires_at' => 'nullable|date'
    //     ]);

    //     $discount->update($data);

    //     return $discount;
    // }

    // public function destroy($id)
    // {
    //     $discount = Discount::findOrFail($id);
    //     $discount->delete();
    //     return response()->json(['message' => 'Deleted']);
    // }
    // public function getDiscountAmount(Request $request)
    // {
    //     // Bước 1: Xác thực đầu vào
    //     $request->validate([
    //         'code' => 'required|string', // Đảm bảo rằng 'code' là một chuỗi và không được để trống
    //     ]);

    //     // Bước 2: Tìm mã giảm giá trong cơ sở dữ liệu
    //     $discount = Discount::where('code', $request->code)->first();

    //     // Bước 3: Kiểm tra xem mã giảm giá có tồn tại không
    //     if (!$discount) {
    //         return response()->json(['message' => 'Mã giảm giá không tồn tại'], 404);
    //     }

    //     // Bước 4: Lấy ngày hiện tại
    //     $currentDate = now();

    //     // Bước 5: Kiểm tra trạng thái hoạt động
    //     if (!$discount->is_active) {
    //         return response()->json(['message' => 'Mã giảm giá không hoạt động'], 400);
    //     }

    //     // Bước 6: Kiểm tra số lượt sử dụng
    //     if ($discount->used_count >= $discount->usage_limit) {
    //         return response()->json(['message' => 'Mã giảm giá đã hết lượt sử dụng'], 400);
    //     }

    //     // Bước 7: Kiểm tra ngày hết hạn
    //     if ($discount->expires_at !== null && $currentDate >= $discount->expires_at) {
    //         return response()->json(['message' => 'Mã giảm giá đã hết hạn'], 400);
    //     }
    //     $discount->used_count += 1;
    //     $discount->save();
    //     // Bước 8: Nếu tất cả điều kiện đều đúng, trả về số tiền giảm giá
    //     return response()->json([
    //         'discount_amount' => $discount->discount_amount,
    //         'discount_id' => $discount->id,
    //         'message' => 'Áp dụng mã giảm giá thành công!'
    //     ]);
    // }

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
        $discount->used_count += 1;

        // **LOGIC TỰ ĐỘNG CHUYỂN TRẠNG THÁI**
        // Nếu sau khi tăng, số lượt đã dùng bằng giới hạn, tự động tắt mã
        if ($discount->used_count >= $discount->usage_limit) {
            $discount->is_active = false;
        }

        $discount->save();
        
        return response()->json([
            'discount_amount' => $discount->discount_amount,
            'discount_id' => $discount->id,
            'message' => 'Áp dụng mã giảm giá thành công!'
        ]);
    }
}
