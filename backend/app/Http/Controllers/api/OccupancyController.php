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
    $validated = $request->validate([
        'customer_name'      => 'required|string|max:255',
        'customer_phone'     => 'required|string|max:20',
        'customer_email'     => 'required|email|max:255',
        'address'            => 'nullable|string|max:255',
        'check_in_date'      => 'required|date',
        'check_out_date'     => 'required|date|after:check_in_date',
        'pricing_type'       => 'required|in:hourly,nightly',
    ]);

    try {
        // Tìm room để lấy type_id
        $room = DB::table('rooms')->where('room_id', $room_id)->first();
        if (!$room) {
            return response()->json(['message' => 'Không tìm thấy phòng.'], 404);
        }

        // Tìm giá theo thời gian check-in và type_id
        $price = DB::table('prices')
            ->where('type_id', $room->type_id)
            ->whereDate('start_date', '<=', $validated['check_in_date'])
            ->whereDate('end_date', '>=', $validated['check_in_date'])
            ->orderBy('priority', 'desc')
            ->first();

        if (!$price) {
            return response()->json(['message' => 'Không tìm thấy bảng giá phù hợp.'], 400);
        }

        // Tính giá
        $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);
        $total_price = 0;

        if ($checkOut <= $checkIn) {
            return response()->json([
                'message' => 'Thời gian trả phòng phải sau thời gian nhận phòng.'
            ], 422);
        }
        
        if ($validated['pricing_type'] === 'hourly') {
            $hours = ceil($checkOut->floatDiffInHours($checkIn));
            $total_price = $hours * $price->hourly_price;
        } else {
            $nights = max(1, $checkIn->copy()->startOfDay()->diffInDays($checkOut->copy()->startOfDay()));

            $total_price = $nights * $price->price_per_night;
        }

        // Thêm khách hàng
        $customerId = DB::table('customers')->insertGetId([
            'customer_name'  => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'address'        => $validated['address'] ?? null,
            'room_id'        => $room_id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Tạo booking
        DB::table('bookings')->insert([
            'customer_id'        => $customerId,
            'room_id'            => $room_id,
            'check_in_date'      => $validated['check_in_date'],
            'check_out_date'     => $validated['check_out_date'],
            'booking_type'       => 'walk_in',
            'pricing_type'       => $validated['pricing_type'],
            'total_price'        => $total_price,
            'status'             => 'confirmed',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // Đổi trạng thái phòng
        DB::table('rooms')->where('room_id', $room_id)->update([
            'status' => 'occupied'
        ]);

        return response()->json([
            'message' => 'Đặt phòng thành công',
            'total_price' => number_format($total_price, 0, ',', '.') . ' VND'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Lỗi khi đặt phòng.',
            'error' => $e->getMessage()
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
public function previewPrice(Request $request)
{
    $validated = $request->validate([
        'check_in_date' => 'required|date',
        'check_out_date' => 'required|date|after:check_in_date',
        'pricing_type' => 'required|in:hourly,nightly',
        'room_id' => 'required|exists:rooms,room_id'
    ]);

    try {
        $room = DB::table('rooms')->where('room_id', $validated['room_id'])->first();

        $price = DB::table('prices')
            ->where('type_id', $room->type_id)
            ->whereDate('start_date', '<=', $validated['check_in_date'])
            ->whereDate('end_date', '>=', $validated['check_in_date'])
            ->orderBy('priority', 'desc')
            ->first();

        if (!$price) {
            return response()->json(['message' => 'Không tìm thấy bảng giá phù hợp.'], 400);
        }
        $isExtend = $request->boolean('is_extend'); // nhận từ frontend
        $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);
        $booking = DB::table('bookings')
    ->where('room_id', $validated['room_id'])
    ->where('status', 'confirmed')
    ->latest()
    ->first();

// ✅ Nếu đang gia hạn → tính từ ngày check_out cũ
    $startTime = ($isExtend && $booking) ? \Carbon\Carbon::parse($booking->check_out_date) : $checkIn;
        $total_price = 0;

        if ($validated['pricing_type'] === 'hourly') {
            $hours = ceil($checkOut->floatDiffInHours($startTime));
            $total_price = $hours * $price->hourly_price;
        } else {
            $nights = max(1, $startTime->copy()->startOfDay()->diffInDays($checkOut->copy()->startOfDay()));
            $total_price = $nights * $price->price_per_night;
        }

        return response()->json([
            'total_price' => number_format($total_price, 0, ',', '.')
        ]);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Lỗi khi tính giá', 'error' => $e->getMessage()], 500);
    }
}
public function extendStay(Request $request, $room_id)
{
    $request->validate([
        'check_out_date' => 'required|date|after:now',
    ]);

    try {
        $booking = DB::table('bookings')
            ->where('room_id', $room_id)
            ->where('status', 'confirmed')
            ->latest()
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Không tìm thấy đặt phòng phù hợp.'], 404);
        }

        $room = DB::table('rooms')->where('room_id', $room_id)->first();

        $checkIn = \Carbon\Carbon::parse($booking->check_in_date);
        $newCheckOut = \Carbon\Carbon::parse($request->check_out_date);

        if ($newCheckOut <= $checkIn) {
            return response()->json(['message' => 'Ngày giờ trả phải sau ngày nhận.'], 400);
        }

        $newTotal = 0;

        if ($booking->pricing_type === 'hourly') {
            $hours = $checkIn->diffInHours($newCheckOut);
            for ($i = 0; $i < $hours; $i++) {
                $currentHour = $checkIn->copy()->addHours($i);
                $price = DB::table('prices')
                    ->where('type_id', $room->type_id)
                    ->whereDate('start_date', '<=', $currentHour)
                    ->whereDate('end_date', '>=', $currentHour)
                    ->orderBy('priority', 'desc')
                    ->first();

                if ($price) {
                    $newTotal += $price->hourly_price;
                }
            }
        } else {
            $days = $checkIn->diffInDays($newCheckOut);
            $days = $days === 0 ? 1 : $days;

            for ($i = 0; $i < $days; $i++) {
                $currentDay = $checkIn->copy()->addDays($i)->startOfDay();
                $price = DB::table('prices')
                    ->where('type_id', $room->type_id)
                    ->whereDate('start_date', '<=', $currentDay)
                    ->whereDate('end_date', '>=', $currentDay)
                    ->orderBy('priority', 'desc')
                    ->first();

                if ($price) {
                    $newTotal += $price->price_per_night;
                }
            }
        }

        // Cập nhật lại DB
        DB::table('bookings')->where('booking_id', $booking->booking_id)->update([
            'check_out_date' => $newCheckOut,
            'total_price' => $newTotal,
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Gia hạn thành công.',
            'total_price' => number_format($newTotal, 0, ',', '.') . ' VND'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Lỗi server khi gia hạn.',
            'error' => $e->getMessage()
        ], 500);
    }
}


}