<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Quan trọng: Thêm DB Facade

class OccupancyController extends Controller
{
    /**
     * Lấy danh sách tất cả các phòng đã được nối với loại phòng.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $rooms = DB::table('rooms')
                ->join('room_types', 'rooms.type_id', '=', 'room_types.type_id')
                ->select(
                    'rooms.room_id',
                    'rooms.room_name',
                    'rooms.floor_number',
                    'rooms.status',
                    'room_types.type_name',
                    'room_types.bed_count'
                )
                ->orderBy('rooms.floor_number', 'asc')
                ->orderBy('rooms.room_name', 'asc')
                ->get();

            return response()->json($rooms);

        } catch (\Exception $e) {
            // Trả về lỗi nếu có sự cố
            return response()->json([
                'message' => 'Không thể lấy dữ liệu phòng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    //hàm đổi trạng thái khi đặt thành công
    public function addGuestToRoom(Request $request, $room_id)
{
    try {
        // Kiểm tra xem phòng có tồn tại không
        $room = Room::find($room_id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy phòng với ID: $room_id"
            ], 404);
        }

        // Cập nhật trạng thái phòng
        $room->status = 'occupied'; // hoặc 'Đã đặt' nếu bạn lưu tiếng Việt
        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái phòng thành công.',
            'room' => $room
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi server.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function storeCustomer(Request $request)
{
    // Bước 1: Validate dữ liệu
    $validated = $request->validate([
        'customer_name'   => 'required|string|max:255',
        'customer_phone'  => 'required|string|max:20|unique:customers,customer_phone',
        'customer_email'  => 'required|email|max:255|unique:customers,customer_email',
        'address'         => 'nullable|string|max:255',
        'room_id' => 'required|exists:rooms,room_id',
    ]);

    // Bước 2: Thêm vào database
    try {
        DB::table('customers')->insert([
            'customer_name'  => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'address'        => $validated['address'] ?? null,
            'room_id' => $validated['room_id'],
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm khách hàng thành công.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi thêm khách hàng.',
            'error'   => $e->getMessage()
        ], 500);
    }
}
public function getCustomerByRoom($room_id)
{
    $customer = DB::table('customers')
        ->where('room_id', $room_id)
        ->latest() // Lấy khách mới nhất gắn với phòng này
        ->first();

    if (!$customer) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy khách cho phòng này.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $customer
    ]);
}
public function checkoutRoom($room_id)
{
    try {
        $room = Room::find($room_id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy phòng với ID: $room_id"
            ], 404);
        }

        $room->status = 'available'; // hoặc 'Còn trống'
        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán thành công. Phòng đã được chuyển về trạng thái trống.',
            'room' => $room
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi server khi thanh toán.',
            'error' => $e->getMessage()
        ], 500);
    }
}

}