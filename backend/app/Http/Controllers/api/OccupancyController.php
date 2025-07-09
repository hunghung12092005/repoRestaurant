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
            'customer_id_number' => 'required|string|max:20',

        ]);

        try {
            // Tìm room để lấy type_id
            $room = DB::table('rooms')->where('room_id', $room_id)->first();
            if (!$room) {
                return response()->json(['message' => 'Không tìm thấy phòng.'], 404);
            }

            $now = now();
            $price = DB::table('prices')
                ->where('type_id', $room->type_id)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderByDesc('priority')
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

            $totalHours = $checkIn->floatDiffInHours($checkOut);

            if ($totalHours <= 6) {
                // Thuê dưới hoặc bằng 6h → tính theo giờ
                $hours = ceil($totalHours);
                $total_price = $hours * $price->hourly_price;
            } else {
                // Tính ngày + giờ
                $fullDays = floor($totalHours / 24);
                $remainingHours = $totalHours - ($fullDays * 24);

                if ($remainingHours > 6) {
                    $fullDays += 1; // Nếu phần dư > 6h → thêm 1 ngày
                    $remainingHours = 0;
                }

                $total_price = ($fullDays * $price->price_per_night) + (ceil($remainingHours) * $price->hourly_price);
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
                'customer_id_number' => $validated['customer_id_number'],

            ]);

            // Tạo booking
            $bookingId = DB::table('booking_hotel')->insertGetId([
                'customer_id' => $customerId,
                'payment_method' => 'thanh_toan_ngay',
                'booking_type' => 'offline',
                'pricing_type' => $validated['pricing_type'],
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'total_rooms' => 1,
                'total_price' => $total_price,
                'additional_fee' => 0,
                'payment_status' => 'pending',
                'status' => 'confirmed',
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('booking_hotel_detail')->insert([
                'booking_id' => $bookingId,
                'room_type' => $room->type_id,
                'room_id' => $room_id,
                'gia_phong' => $total_price,
                'gia_dich_vu' => 0,
                'total_price' => $total_price,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            // Đổi trạng thái phòng
            DB::table('rooms')->where('room_id', $room_id)->update([
                'status' => 'occupied'
            ]);

            return response()->json([
                'message' => 'Đặt phòng thành công',
                'total_price' => $total_price
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
        try {
            $room = DB::table('rooms')
                ->join('room_types', 'rooms.type_id', '=', 'room_types.type_id')
                ->where('rooms.room_id', $room_id)
                ->select(
                    'rooms.room_id',
                    'rooms.room_name',
                    'rooms.floor_number',
                    'rooms.status',
                    'room_types.type_name',
                    'room_types.bed_count'
                )
                ->first();

            if (!$room) {
                return response()->json(['message' => 'Không tìm thấy phòng.'], 404);
            }

            $bookingDetail = DB::table('booking_hotel_detail')
                ->where('room_id', $room_id)
                ->latest('created_at')
                ->first();

            if (!$bookingDetail) {
                return response()->json(['message' => 'Không tìm thấy thông tin chi tiết phòng.'], 404);
            }

            $booking = DB::table('booking_hotel')
                ->where('booking_id', $bookingDetail->booking_id)
                ->first();

            $customer = DB::table('customers')
                ->where('customer_id', $booking->customer_id)
                ->first();

            return response()->json([
                'success' => true,
                'room' => $room,
                'customer' => $customer,
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy chi tiết phòng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkoutRoom(Request $request, $room_id)
    {
        $additionalFee = $request->input('additional_fee', 0);
        $serviceIds = $request->input('service_ids', []);

        try {
            $room = Room::find($room_id);
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy phòng với ID: $room_id"
                ], 404);
            }
            if (!$room->type_id) {
                return response()->json([
                    'success' => false,
                    'message' => "Phòng chưa có loại phòng (type_id), không thể tính giá."
                ], 400);
            }
            $bookingDetail = DB::table('booking_hotel_detail')
                ->where('room_id', $room_id)
                ->latest('created_at')
                ->first();

            if (!$bookingDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin đặt phòng cho phòng này.'
                ], 404);
            }

            $booking = DB::table('booking_hotel')
                ->where('booking_id', $bookingDetail->booking_id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đặt phòng để thanh toán.'
                ], 404);
            }

            $actualCheckout = now();
            $checkIn = \Carbon\Carbon::parse($booking->check_in_date);

            $now = now();
            $price = DB::table('prices')
                ->where('type_id', $room->type_id)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderByDesc('priority')
                ->first();

            if (!$price) {
                return response()->json(['message' => 'Không tìm thấy bảng giá.'], 400);
            }

            $totalHours = $checkIn->floatDiffInHours($actualCheckout);
            $newTotal = 0;

            if ($totalHours <= 2) {
                $newTotal = 2 * $price->hourly_price;
            } elseif ($totalHours <= 6) {
                $hours = ceil($totalHours);
                $newTotal = $hours * $price->hourly_price;
            } else {
                $fullDays = floor($totalHours / 24);
                $remainingHours = $totalHours - ($fullDays * 24);

                if ($remainingHours > 6) {
                    $fullDays += 1;
                    $remainingHours = 0;
                }

                $newTotal = ($fullDays * $price->price_per_night) + (ceil($remainingHours) * $price->hourly_price);
            }

            // Tính chênh lệch dự kiến
            $expectedCheckout = \Carbon\Carbon::parse($booking->check_out_date);
            $expectedHours = $checkIn->floatDiffInHours($expectedCheckout);
            $expectedTotal = 0;

            if ($expectedHours <= 6) {
                $expectedTotal = ceil($expectedHours) * $price->hourly_price;
            } else {
                $fullDays = floor($expectedHours / 24);
                $remainingHours = $expectedHours - ($fullDays * 24);
                if ($remainingHours > 6) {
                    $fullDays += 1;
                    $remainingHours = 0;
                }
                $expectedTotal = ($fullDays * $price->price_per_night) + (ceil($remainingHours) * $price->hourly_price);
            }

            $difference = $expectedTotal - $newTotal;

            if ($totalHours <= 2) {
                $newTotal = 2 * $price->hourly_price;
                $note = "Khách trả trong 2 giờ đầu. Tính phí cố định 2 giờ.";
            } else {
                $note = $difference > 0
                    ? "Khách trả sớm, hoàn lại " . number_format($difference, 0, ',', '.') . " VND"
                    : ($difference < 0
                        ? "Khách ở quá giờ, phụ thu thêm " . number_format(abs($difference), 0, ',', '.') . " VND"
                        : "Thanh toán đúng như dự kiến.");
            }

            $newTotal += $additionalFee;

            // Gộp tiền dịch vụ nếu có
            $totalServiceFee = 0;
            if (!empty($serviceIds)) {
                $bookingDetail = DB::table('booking_hotel_detail')
                    ->where('booking_id', $booking->booking_id)
                    ->where('room_id', $room_id)
                    ->latest('created_at')
                    ->first();

                if (!$bookingDetail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy chi tiết đặt phòng để gán dịch vụ.'
                    ], 404);
                }

                foreach ($serviceIds as $serviceId) {
                    DB::table('booking_hotel_service')->insert([
                        'booking_detail_id' => $bookingDetail->booking_detail_id,
                        'service_id' => $serviceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $totalServiceFee = DB::table('services')
                    ->whereIn('service_id', $serviceIds)
                    ->sum('price');

                DB::table('booking_hotel_detail')
                    ->where('booking_detail_id', $bookingDetail->booking_detail_id)
                    ->update([
                        'gia_dich_vu' => $totalServiceFee,
                        'total_price' => $newTotal + $totalServiceFee,
                        'updated_at' => now()
                    ]);

                $newTotal += $totalServiceFee;
            }


            // Cập nhật lại booking
            DB::table('booking_hotel')->where('booking_id', $booking->booking_id)->update([
                'total_price' => $newTotal,
                'status' => 'completed',
                'payment_status' => 'paid',
                'note' => $note,
                'updated_at' => now()
            ]);

            $room->status = 'available';
            $room->save();

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công. Phòng đã chuyển về trạng thái trống.',
                'room' => $room,
                'actual_total' => $newTotal + $totalServiceFee,
                'room_total' => $newTotal,
                'service_total' => $totalServiceFee,
                'note' => $note
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
            if (!$room) {
                return response()->json(['message' => 'Không tìm thấy phòng.'], 404);
            }

            $typeId = $room->type_id;

            $checkInCarbon = \Carbon\Carbon::parse($validated['check_in_date']);
            $dayOfWeek = $checkInCarbon->dayOfWeekIso; // 1 (Thứ 2) -> 7 (Chủ Nhật)
            $hour = $checkInCarbon->format('H'); // Giờ 2 chữ số

            $now = now();
            $price = DB::table('prices')
                ->where('type_id', $typeId)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderByDesc('priority')
                ->first();

            if (!$price) {
                return response()->json(['message' => 'Không tìm thấy bảng giá phù hợp.'], 400);
            }

            $isExtend = $request->boolean('is_extend'); // nhận từ frontend
            $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
            $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);

            $startTime = $checkIn;

            if ($isExtend) {
                $bookingDetail = DB::table('booking_hotel_detail')
                    ->where('room_id', $validated['room_id'])
                    ->latest('created_at')
                    ->first();

                if ($bookingDetail) {
                    $booking = DB::table('booking_hotel')
                        ->where('booking_id', $bookingDetail->booking_id)
                        ->first();

                    if ($booking) {
                        $startTime = \Carbon\Carbon::parse($booking->check_out_date);
                    }
                }
            }

            $totalHours = $startTime->floatDiffInHours($checkOut);

            $total_price = 0;

            if ($totalHours <= 6) {
                $total_price = ceil($totalHours) * $price->hourly_price;
            } else {
                $fullDays = floor($totalHours / 24);
                $remainingHours = $totalHours - ($fullDays * 24);

                if ($remainingHours > 6) {
                    $fullDays += 1;
                    $remainingHours = 0;
                }

                $total_price = ($fullDays * $price->price_per_night) + (ceil($remainingHours) * $price->hourly_price);
            }

            return response()->json(['total_price' => $total_price]);
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
            $bookingDetail = DB::table('booking_hotel_detail')
                ->where('room_id', $room_id)
                ->latest('created_at')
                ->first();

            if (!$bookingDetail) {
                return response()->json(['message' => 'Không tìm thấy booking chi tiết cho phòng này.'], 404);
            }

            $booking = DB::table('booking_hotel')
                ->where('booking_id', $bookingDetail->booking_id)
                ->first();

            if (!$booking) {
                return response()->json(['message' => 'Không tìm thấy đặt phòng phù hợp.'], 404);
            }


            if (!$booking) {
                return response()->json(['message' => 'Không tìm thấy đặt phòng phù hợp.'], 404);
            }

            $room = DB::table('rooms')->where('room_id', $room_id)->first();

            $checkIn = \Carbon\Carbon::parse($booking->check_in_date);
            $newCheckOut = \Carbon\Carbon::parse($request->check_out_date);

            if ($newCheckOut <= $checkIn) {
                return response()->json(['message' => 'Ngày giờ trả phải sau ngày nhận.'], 400);
            }

            $now = now();
            $price = DB::table('prices')
                ->where('type_id', $room->type_id)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderByDesc('priority')
                ->first();

            if (!$price) {
                return response()->json(['message' => 'Không tìm thấy bảng giá phù hợp.'], 400);
            }

            $totalHours = $checkIn->floatDiffInHours($newCheckOut);
            $newTotal = 0;

            if ($totalHours <= 6) {
                $newTotal = ceil($totalHours) * $price->hourly_price;
            } else {
                $fullDays = floor($totalHours / 24);
                $remainingHours = $totalHours - ($fullDays * 24);

                if ($remainingHours > 6) {
                    $fullDays += 1;
                    $remainingHours = 0;
                }

                $newTotal = ($fullDays * $price->price_per_night) + (ceil($remainingHours) * $price->hourly_price);
            }


            // Cập nhật lại DB
            DB::table('booking_hotel')->where('booking_id', $booking->booking_id)->update([
                'check_out_date' => $newCheckOut,
                'total_price' => $newTotal,
                'updated_at' => now()
            ]);
            DB::table('booking_hotel_detail')->where('booking_detail_id', $bookingDetail->booking_detail_id)->update([
                'gia_phong' => $newTotal,
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
    public function getServices()
    {
        try {
            $services = DB::table('services')
                ->select('service_id', 'service_name', 'price')
                ->get();

            return response()->json(['services' => $services]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách dịch vụ',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
