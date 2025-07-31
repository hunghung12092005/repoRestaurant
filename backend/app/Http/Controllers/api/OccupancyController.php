<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingHotel;
use App\Models\BookingHotelDetail;
use App\Models\Price;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OccupancyController extends Controller
{
    /**
     * Lấy danh sách tất cả các phòng.
     */
    public function index()
    {
        try {
            $rooms = DB::table('rooms')
                ->join('room_types', 'rooms.type_id', '=', 'room_types.type_id')
                ->select(
                    'rooms.room_id', 'rooms.room_name', 'rooms.floor_number',
                    'rooms.status', 'room_types.type_name', 'room_types.bed_count'
                )
                ->orderBy('rooms.floor_number', 'asc')
                ->orderBy('rooms.room_name', 'asc')
                ->get();
            return response()->json($rooms);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách phòng: ' . $e->getMessage());
            return response()->json(['message' => 'Không thể lấy dữ liệu phòng.', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Lấy thông tin khách và booking cho phòng tại thời điểm cụ thể
     */
    public function getCustomerByRoom(Request $request, $room_id)
    {
        try {
            // Kiểm tra phòng
            $room = DB::table('rooms')->where('room_id', $room_id)->first();
            if (!$room) {
                Log::warning("Không tìm thấy phòng với room_id: {$room_id}");
                return response()->json(['message' => 'Không tìm thấy phòng.'], 404);
            }

            // Lấy ngày và thời gian từ request
            $date = $request->query('date', now()->toDateString());
            $time = $request->query('time', now()->format('H:i'));

            // Parse thời gian
            try {
                $checkTime = Carbon::parse($date . ' ' . $time);
            } catch (\Exception $e) {
                Log::warning("Lỗi parse thời gian: date={$date}, time={$time}, error=" . $e->getMessage());
                return response()->json(['message' => 'Định dạng thời gian không hợp lệ.'], 400);
            }

            // Lấy booking hiện tại tại thời điểm checkTime
            $booking = DB::table('booking_hotel_detail as bkd')
                ->join('booking_hotel as bk', 'bk.booking_id', '=', 'bkd.booking_id')
                ->join('customers as c', 'bk.customer_id', '=', 'c.customer_id')
                ->join('room_types as rt', 'rt.type_id', '=', 'bkd.room_type')
                ->join('rooms', 'rooms.room_id', '=', 'bkd.room_id') // Sửa tham chiếu bảng rooms
                ->where('bkd.room_id', $room_id)
                ->where('bk.status', '!=', 'completed')
                ->whereRaw('CONCAT(bk.check_in_date, " ", COALESCE(bk.check_in_time, "14:00")) <= ?', [$checkTime->toDateTimeString()])
                ->whereRaw('CONCAT(bk.check_out_date, " ", COALESCE(bk.check_out_time, "12:00")) >= ?', [$checkTime->toDateTimeString()])
                ->select(
                    'bk.booking_id',
                    'bk.check_in_date',
                    'bk.check_in_time',
                    'bk.check_out_date',
                    'bk.check_out_time',
                    'bk.total_price',
                    'bk.note',
                    'c.customer_id',
                    'c.customer_name',
                    'c.customer_phone',
                    'c.customer_email',
                    'c.address',
                    'rt.type_name',
                    'rooms.room_name', // Sửa từ room.room_name thành rooms.room_name
                    'rooms.floor_number'
                )
                ->first();

            if (!$booking) {
                Log::info("Không tìm thấy booking cho room_id={$room_id} tại thời điểm {$checkTime}");
                return response()->json(['message' => 'Không tìm thấy booking tại thời điểm này.'], 404);
            }

            return response()->json([
                'room' => [
                    'room_id' => $room_id,
                    'room_name' => $booking->room_name, // Sử dụng từ kết quả truy vấn
                    'floor_number' => $booking->floor_number,
                    'type_name' => $booking->type_name,
                ],
                'customer' => [
                    'customer_id' => $booking->customer_id,
                    'customer_name' => $booking->customer_name,
                    'customer_phone' => $booking->customer_phone,
                    'customer_email' => $booking->customer_email,
                    'address' => $booking->address,
                ],
                'booking' => [
                    'booking_id' => $booking->booking_id,
                    'check_in_date' => $booking->check_in_date,
                    'check_in_time' => $booking->check_in_time,
                    'check_out_date' => $booking->check_out_date,
                    'check_out_time' => $booking->check_out_time,
                    'total_price' => $booking->total_price,
                    'note' => $booking->note,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi lấy thông tin khách cho phòng {$room_id}: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return response()->json(['message' => 'Lỗi server khi lấy thông tin khách.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Thêm khách vào phòng với thời gian check-in và check-out.
     */
    public function addGuestToRoom(Request $request, $room_id)
    {
        $validated = $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_phone'     => 'required|digits_between:10,11',
            'customer_email'     => 'required|email|max:255',
            'address'            => 'nullable|string|max:255',
            'check_in_date'      => 'required|date',
            'check_in_time'      => 'nullable|date_format:H:i',
            'check_out_date'     => 'required|date',
            'check_out_time'     => 'nullable|date_format:H:i',
            'pricing_type'       => 'required|in:hourly,nightly',
            'customer_id_number' => 'required|digits:12',
        ], [
            'customer_name.required'      => 'Tên khách hàng là bắt buộc.',
            'customer_name.string'        => 'Tên khách hàng phải là chuỗi ký tự.',
            'customer_name.max'           => 'Tên khách hàng không được vượt quá 255 ký tự.',
            'customer_phone.required'     => 'Số điện thoại là bắt buộc.',
            'customer_phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 chữ số.',
            'customer_email.required'     => 'Email là bắt buộc.',
            'customer_email.email'        => 'Email không đúng định dạng.',
            'customer_email.max'          => 'Email không được vượt quá 255 ký tự.',
            'address.string'             => 'Địa chỉ phải là chuỗi ký tự.',
            'address.max'                => 'Địa chỉ không được vượt quá 255 ký tự.',
            'check_in_date.required'     => 'Ngày nhận phòng là bắt buộc.',
            'check_in_date.date'         => 'Ngày nhận phòng không đúng định dạng.',
            'check_in_time.date_format'  => 'Giờ nhận phòng phải có định dạng H:i (VD: 14:00).',
            'check_out_date.required'    => 'Ngày trả phòng là bắt buộc.',
            'check_out_date.date'        => 'Ngày trả phòng không đúng định dạng.',
            'check_out_time.date_format' => 'Giờ trả phòng phải có định dạng H:i (VD: 12:00).',
            'pricing_type.required'      => 'Loại giá là bắt buộc.',
            'pricing_type.in'            => 'Loại giá phải là "hourly" hoặc "nightly".',
            'customer_id_number.required' => 'Số CCCD là bắt buộc.',
            'customer_id_number.digits'  => 'Số CCCD phải có đúng 12 chữ số.',
        ]);

        try {
            // Kiểm tra phòng
            $room = DB::table('rooms')->where('room_id', $room_id)->first();
            if (!$room) {
                Log::warning("Không tìm thấy phòng với room_id: {$room_id}");
                return response()->json(['message' => 'Không tìm thấy phòng.'], 404);
            }

            // Lấy bảng giá
            $now = now();
            $price = DB::table('prices')
                ->where('type_id', $room->type_id)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderByDesc('priority')
                ->first();
            if (!$price || !is_numeric($price->hourly_price) || !is_numeric($price->price_per_night)) {
                Log::warning("Không tìm thấy bảng giá hợp lệ hoặc giá không phải số cho type_id: {$room->type_id}");
                return response()->json(['message' => 'Không tìm thấy bảng giá hợp lệ hoặc giá không hợp lệ.'], 400);
            }

            // Đặt thời gian mặc định
            $checkInTime = $validated['check_in_time'] ?? '14:00';
            $checkOutTime = $validated['check_out_time'] ?? '12:00';
            $checkIn = Carbon::parse($validated['check_in_date'] . ' ' . $checkInTime);
            $checkOut = Carbon::parse($validated['check_out_date'] . ' ' . $checkOutTime);

            // Kiểm tra thời gian hợp lệ
            if ($checkOut <= $checkIn) {
                Log::warning("Thời gian trả phòng không hợp lệ: check_in={$checkIn}, check_out={$checkOut}");
                return response()->json(['message' => 'Thời gian trả phòng phải sau thời gian nhận phòng.'], 422);
            }

            // Kiểm tra đặt phòng trong cùng ngày
            if ($validated['check_in_date'] === $validated['check_out_date'] && $checkOutTime <= $checkInTime) {
                return response()->json(['message' => 'Giờ trả phòng phải sau giờ nhận phòng trong cùng ngày.'], 422);
            }

            // Kiểm tra trùng thời gian
            $existingBookings = DB::table('booking_hotel_detail as bkd')
                ->join('booking_hotel as bk', 'bk.booking_id', '=', 'bkd.booking_id')
                ->where('bkd.room_id', $room_id)
                ->where('bk.status', '!=', 'completed')
                ->select('bk.booking_id', 'bk.check_in_date', 'bk.check_in_time', 'bk.check_out_date', 'bk.check_out_time')
                ->get();

            $isOverlapping = false;
            foreach ($existingBookings as $booking) {
                $existingCheckIn = Carbon::parse($booking->check_in_date . ' ' . ($booking->check_in_time ?? '14:00'));
                $existingCheckOut = Carbon::parse($booking->check_out_date . ' ' . ($booking->check_out_time ?? '12:00'));

                // Kiểm tra xung đột thời gian
                if ($checkIn < $existingCheckOut && $checkOut > $existingCheckIn) {
                    $isOverlapping = true;
                    Log::info("Xung đột thời gian với booking_id: {$booking->booking_id}, existing_check_in={$existingCheckIn}, existing_check_out={$existingCheckOut}");

                    // Kiểm tra quy tắc 30 phút trong cùng ngày
                    if ($validated['check_in_date'] === $booking->check_in_date) {
                        if ($checkOut <= $existingCheckIn) {
                            $isOverlapping = false; // Đặt phòng mới kết thúc trước khi đơn hiện có bắt đầu
                        } elseif ($checkIn->diffInMinutes($existingCheckIn, false) < 30) {
                            return response()->json(['message' => 'Thời gian nhận phòng phải sớm hơn ít nhất 30 phút so với lịch đặt hiện có trong cùng ngày.'], 409);
                        }
                    }
                }
            }

            if ($isOverlapping) {
                return response()->json(['message' => 'Phòng này đã có người đặt trong khoảng thời gian đã chọn.'], 409);
            }

            // Tính giá
            $totalHours = $checkIn->floatDiffInHours($checkOut);
            if (!is_numeric($totalHours) || $totalHours <= 0) {
                Log::error("Lỗi tính totalHours: check_in={$checkIn}, check_out={$checkOut}, totalHours={$totalHours}");
                return response()->json(['message' => 'Lỗi tính toán thời gian đặt phòng.'], 422);
            }

            $total_price = 0;
            $note = '';

            if ($totalHours <= 2) {
                $total_price = 2 * $price->hourly_price;
                $note = "Ở dưới 2 giờ, tính giá cố định 2 giờ.";
            } elseif ($totalHours <= 6) {
                $hours = ceil($totalHours);
                $total_price = $hours * $price->hourly_price;
                $note = "Ở {$hours} giờ.";
            } else {
                $fullDays = ceil($totalHours / 24);
                $total_price = $fullDays * $price->price_per_night;
                $note = "Ở {$fullDays} ngày (trên 6 giờ, tính theo ngày).";
            }

            if (!is_numeric($total_price) || $total_price <= 0) {
                Log::error("Lỗi tính total_price: totalHours={$totalHours}, hourly_price={$price->hourly_price}, price_per_night={$price->price_per_night}, total_price={$total_price}");
                return response()->json(['message' => 'Lỗi tính toán giá phòng.', 'error' => 'Giá phòng không hợp lệ.'], 500);
            }

            // Thêm khách
            $customerId = DB::table('customers')->insertGetId([
                'customer_name'      => $validated['customer_name'],
                'customer_phone'     => $validated['customer_phone'],
                'customer_email'     => $validated['customer_email'],
                'address'            => $validated['address'] ?? null,
                'room_id'            => $room_id,
                'created_at'         => now(),
                'updated_at'         => now(),
                'customer_id_number' => $validated['customer_id_number'],
            ]);

            // Thêm booking
            $bookingId = DB::table('booking_hotel')->insertGetId([
                'customer_id'      => $customerId,
                'payment_method'   => 'thanh_toan_ngay',
                'booking_type'     => 'offline',
                'pricing_type'     => $validated['pricing_type'],
                'check_in_date'    => $validated['check_in_date'],
                'check_in_time'    => $checkInTime,
                'check_out_date'   => $validated['check_out_date'],
                'check_out_time'   => $checkOutTime,
                'total_rooms'      => 1,
                'total_price'      => $total_price,
                'additional_fee'   => 0,
                'payment_status'   => 'completed',
                'status'           => 'confirmed',
                'note'             => $note,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Thêm chi tiết booking
            DB::table('booking_hotel_detail')->insert([
                'booking_id'    => $bookingId,
                'room_type'     => $room->type_id,
                'room_id'       => $room_id,
                'gia_phong'     => $total_price,
                'gia_dich_vu'   => 0,
                'total_price'   => $total_price,
                'note'          => $note,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Cập nhật trạng thái phòng
            DB::table('rooms')->where('room_id', $room_id)->update(['status' => 'occupied']);

            return response()->json([
                'message' => 'Đặt phòng thành công',
                'booking_id' => $bookingId,
                'total_price' => $total_price,
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi đặt phòng {$room_id}: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi đặt phòng.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xem trước giá phòng dựa trên thời gian đặt
     */
    public function previewPrice(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,room_id',
            'check_in_date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_date' => 'required|date',
            'check_out_time' => 'nullable|date_format:H:i',
            'pricing_type' => 'required|in:hourly,nightly',
            'is_extend' => 'boolean',
        ]);

        try {
            // Kiểm tra phòng
            $room = DB::table('rooms')->where('room_id', $validated['room_id'])->first();
            if (!$room) {
                return response()->json(['message' => 'Không tìm thấy phòng.'], 404);
            }

            // Lấy bảng giá
            $now = now();
            $price = DB::table('prices')
                ->where('type_id', $room->type_id)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderByDesc('priority')
                ->first();
            if (!$price || !is_numeric($price->hourly_price) || !is_numeric($price->price_per_night)) {
                Log::warning("Không tìm thấy bảng giá hợp lệ hoặc giá không phải số cho type_id: {$room->type_id}");
                return response()->json(['message' => 'Không tìm thấy bảng giá hợp lệ hoặc giá không hợp lệ.'], 400);
            }

            // Đặt thời gian mặc định
            $checkInTime = $validated['check_in_time'] ?? '14:00';
            $checkOutTime = $validated['check_out_time'] ?? '12:00';
            $checkIn = Carbon::parse($validated['check_in_date'] . ' ' . $checkInTime);
            $checkOut = Carbon::parse($validated['check_out_date'] . ' ' . $checkOutTime);

            // Kiểm tra thời gian hợp lệ
            if ($checkOut <= $checkIn) {
                Log::warning("Thời gian trả phòng không hợp lệ: check_in={$checkIn}, check_out={$checkOut}");
                return response()->json(['message' => 'Thời gian trả phòng phải sau thời gian nhận phòng.'], 422);
            }

            // Kiểm tra đặt phòng trong cùng ngày
            if ($validated['check_in_date'] === $validated['check_out_date'] && $checkOutTime <= $checkInTime) {
                return response()->json(['message' => 'Giờ trả phòng phải sau giờ nhận phòng trong cùng ngày.'], 422);
            }

            // Tính giá
            $totalHours = $checkIn->floatDiffInHours($checkOut);
            if (!is_numeric($totalHours) || $totalHours <= 0) {
                Log::error("Lỗi tính totalHours: check_in={$checkIn}, check_out={$checkOut}, totalHours={$totalHours}");
                return response()->json(['message' => 'Lỗi tính toán thời gian đặt phòng.'], 422);
            }

            $total_price = 0;
            $note = '';

            if ($totalHours <= 2) {
                $total_price = 2 * $price->hourly_price;
                $note = "Ở dưới 2 giờ, tính giá cố định 2 giờ.";
            } elseif ($totalHours <= 6) {
                $hours = ceil($totalHours);
                $total_price = $hours * $price->hourly_price;
                $note = "Ở {$hours} giờ.";
            } else {
                $fullDays = ceil($totalHours / 24);
                $total_price = $fullDays * $price->price_per_night;
                $note = "Ở {$fullDays} ngày (trên 6 giờ, tính theo ngày).";
            }

            if (!is_numeric($total_price) || $total_price <= 0) {
                Log::error("Lỗi tính total_price: totalHours={$totalHours}, hourly_price={$price->hourly_price}, price_per_night={$price->price_per_night}, total_price={$total_price}");
                return response()->json(['message' => 'Lỗi tính toán giá phòng.'], 500);
            }

            return response()->json([
                'total_price' => $total_price,
                'note' => $note,
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi xem trước giá phòng {$validated['room_id']}: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tính giá phòng.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Gia hạn thời gian lưu trú.
     */
    public function extendStay(Request $request, $room_id)
    {
        $request->validate([
            'check_out_date' => 'required|date|after:now',
        ]);

        try {
            // Tìm booking hiện tại
            $bookingDetail = DB::table('booking_hotel_detail')
                ->where('room_id', $room_id)
                ->latest('created_at')
                ->first();
            if (!$bookingDetail) {
                return response()->json(['message' => 'Không tìm thấy booking chi tiết.'], 404);
            }

            $booking = DB::table('booking_hotel')
                ->where('booking_id', $bookingDetail->booking_id)
                ->first();
            if (!$booking) {
                return response()->json(['message' => 'Không tìm thấy đặt phòng.'], 404);
            }

            // Kiểm tra phòng
            $room = DB::table('rooms')->where('room_id', $room_id)->first();
            if (!$room) {
                return response()->json(['message' => 'Không tìm thấy phòng.'], 404);
            }

            // Lấy bảng giá
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

            // Tính giá mới
            $checkIn = Carbon::parse($booking->check_in_date . ' ' . $booking->check_in_time);
            $newCheckOut = Carbon::parse($request->check_out_date);
            if ($newCheckOut <= $checkIn) {
                return response()->json(['message' => 'Ngày giờ trả phải sau ngày nhận.'], 400);
            }

            $totalHours = $checkIn->floatDiffInHours($newCheckOut);
            $newTotal = 0;
            $note = '';

            if ($totalHours <= 6) {
                $hours = ceil($totalHours);
                $newTotal = $hours * $price->hourly_price;
                $note = "Gia hạn: Ở {$hours} giờ.";
            } elseif ($totalHours < 24) {
                $newTotal = $price->price_per_night;
                $note = "Gia hạn: Ở trên 6 giờ và dưới 1 ngày, tính 1 ngày.";
            } else {
                $fullDays = floor($totalHours / 24);
                $remainingHours = $totalHours - ($fullDays * 24);
                if ($remainingHours > 6) {
                    $fullDays += 1;
                    $newTotal = $fullDays * $price->price_per_night;
                    $note = "Gia hạn: Ở {$fullDays} ngày (dư > 6 giờ, tính thêm 1 ngày).";
                } elseif ($remainingHours > 0) {
                    $extraHours = ceil($remainingHours);
                    $extraFee = $extraHours * $price->hourly_price;
                    $newTotal = ($fullDays * $price->price_per_night) + $extraFee;
                    $note = "Gia hạn: Ở {$fullDays} ngày + thêm {$extraHours} giờ.";
                } else {
                    $newTotal = $fullDays * $price->price_per_night;
                    $note = "Gia hạn: Ở tròn {$fullDays} ngày.";
                }
            }

            // Cập nhật booking
            DB::table('booking_hotel')
                ->where('booking_id', $booking->booking_id)
                ->update([
                    'check_out_date' => $newCheckOut->toDateString(),
                    'check_out_time' => $newCheckOut->toTimeString(),
                    'total_price' => $newTotal,
                    'note' => $note,
                    'updated_at' => now(),
                ]);

            // Cập nhật booking detail
            DB::table('booking_hotel_detail')
                ->where('booking_detail_id', $bookingDetail->booking_detail_id)
                ->update([
                    'gia_phong' => $newTotal,
                    'total_price' => $newTotal,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'message' => 'Gia hạn thành công.',
                'total_price' => number_format($newTotal, 0, ',', '.') . ' VND',
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi gia hạn phòng ' . $room_id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi gia hạn.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy danh sách dịch vụ.
     */
    public function getServices()
    {
        try {
            $services = DB::table('services')
                ->select('service_id', 'service_name', 'price')
                ->get();
            return response()->json($services);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách dịch vụ: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi lấy danh sách dịch vụ.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật thông tin khách hàng.
     */
    public function updateCustomerName(Request $request, $customer_id)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            $updated = DB::table('customers')
                ->where('customer_id', $customer_id)
                ->update([
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'address' => $request->address,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                return response()->json(['message' => 'Cập nhật thông tin khách thành công.']);
            } else {
                return response()->json(['message' => 'Không tìm thấy khách hoặc không có thay đổi.'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật khách ' . $customer_id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật khách.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy danh sách phòng theo ngày và thời gian.
     */
    public function getRoomsByDate(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $time = $request->input('time', now()->toTimeString('minute')); // Sử dụng thời gian hiện tại nếu không có time

        try {
            $rooms = DB::table('rooms')
                ->join('room_types', 'rooms.type_id', '=', 'room_types.type_id')
                ->select(
                    'rooms.room_id',
                    'rooms.room_name',
                    'rooms.floor_number',
                    'room_types.type_name',
                    'room_types.bed_count'
                )
                ->orderBy('rooms.floor_number')
                ->orderBy('rooms.room_name')
                ->get();

            foreach ($rooms as $room) {
                $bookingInfo = DB::table('booking_hotel_detail as bkd')
                    ->join('booking_hotel as bk', 'bk.booking_id', '=', 'bkd.booking_id')
                    ->where('bkd.room_id', $room->room_id)
                    ->whereIn('bk.status', ['pending', 'confirmed', 'cancellation_requested'])
                    ->where(function ($query) use ($date, $time) {
                        // Kiểm tra xem ngày đặt có bao gồm ngày được chọn không
                        $query->whereDate('bk.check_in_date', '<=', $date)
                              ->whereDate('bk.check_out_date', '>=', $date);

                        // Nếu có thời gian được cung cấp, thêm điều kiện lọc theo thời gian
                        if ($time) {
                            $query->where(function ($timeQuery) use ($time, $date) {
                                $timeQuery->whereRaw('CONCAT(bk.check_in_date, " ", COALESCE(bk.check_in_time, "14:00")) <= ?', [$date . ' ' . $time])
                                          ->whereRaw('CONCAT(bk.check_out_date, " ", COALESCE(bk.check_out_time, "12:00")) >= ?', [$date . ' ' . $time]);
                            });
                        }
                    })
                    ->select('bk.booking_id', 'bk.status as booking_status')
                    ->first();

                if ($bookingInfo) {
                    $room->status = $bookingInfo->booking_status;
                    $room->booking_id = $bookingInfo->booking_id;
                } else {
                    $room->status = 'available';
                    $room->booking_id = null;
                }
            }

            return response()->json($rooms);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách phòng theo ngày và thời gian: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi lấy danh sách phòng.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Trả phòng và thanh toán.
     */
    public function checkoutRoom(Request $request, $room_id)
    {
        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();

        try {
            $date = $request->input('date', now()->toDateString());

            // 1. Tìm chi tiết đặt phòng bằng Eloquent
            $bookingDetail = BookingHotelDetail::with('booking')
                ->where('room_id', $room_id)
                ->whereHas('booking', function ($query) use ($date) {
                    $query->whereDate('check_in_date', '<=', $date)
                          ->whereDate('check_out_date', '>=', $date)
                          ->whereIn('status', ['pending', 'confirmed']);
                })
                ->firstOrFail();

            $booking = $bookingDetail->booking;

            // Nếu thời gian hiện tại trước thời gian nhận phòng, không cho phép trả phòng
            $now = Carbon::now();
            $checkInDateTime = Carbon::parse($booking->check_in_date . ' ' . ($booking->check_in_time ?? '14:00'));
            
            if ($now->lessThan($checkInDateTime)) {
                DB::rollBack();
                return response()->json([
                    'message' => "Chưa đến thời gian nhận phòng ({$checkInDateTime->format('d-m-Y H:i')}). Không thể thực hiện trả phòng."
                ], 400);
            }

            // Nếu đã qua bước kiểm tra, tiếp tục xử lý trả phòng như bình thường
            $servicesInput = $request->input('services', []);
            $additionalFee = (float) $request->input('additional_fee', 0);
            $surchargeReason = $request->input('surcharge_reason', null);

            $room = Room::with('roomType')->findOrFail($room_id);

            if (!$room->roomType) {
                throw new \Exception("Phòng chưa được gán loại.");
            }

            $price = Price::where('type_id', $room->type_id)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderByDesc('priority')
                ->first();

            if (!$price) {
                throw new \Exception("Không tìm thấy bảng giá hiện tại cho loại phòng này.");
            }

            $checkIn = Carbon::parse($booking->check_in_date . ' ' . ($booking->check_in_time ?? '14:00'));
            $actualCheckout = now();
            $totalHours = $checkIn->floatDiffInHours($actualCheckout);

            // Đảm bảo totalHours không âm
            if ($totalHours < 0) {
                DB::rollBack();
                return response()->json([
                    'message' => "Thời gian trả phòng không hợp lệ (trước thời gian nhận phòng)."
                ], 400);
            }

            $recalculatedRoomPrice = 0;
            $note = '';
            $calculationNote = '';

            if ($totalHours <= 2) {
                // Dưới 2 giờ, tính giá cố định cho 2 giờ
                $recalculatedRoomPrice = 2 * $price->hourly_price;
                $note = "Ở dưới 2 giờ, tính giá cố định 2 giờ.";
                $calculationNote = "Tính theo giờ: 2 giờ x " . number_format($price->hourly_price) . "đ = " . number_format($recalculatedRoomPrice) . "đ";
            } elseif ($totalHours <= 6) {
                // Từ 2 đến 6 giờ, tính theo giờ
                $hours = ceil($totalHours);
                $recalculatedRoomPrice = $hours * $price->hourly_price;
                $note = "Ở {$hours} giờ.";
                $calculationNote = "Tính theo giờ: {$hours} giờ x " . number_format($price->hourly_price) . "đ = " . number_format($recalculatedRoomPrice) . "đ";
            } else {
                // Trên 6 giờ, tính theo ngày
                $fullDays = ceil($totalHours / 24); // Làm tròn lên số ngày
                $recalculatedRoomPrice = $fullDays * $price->price_per_night;
                $note = "Ở {$fullDays} ngày (trên 6 giờ, tính theo ngày).";
                $calculationNote = "Tính theo ngày: {$fullDays} ngày x " . number_format($price->price_per_night) . "đ = " . number_format($recalculatedRoomPrice) . "đ";
            }

            $originalBookingPrice = $booking->total_price;

            if ($recalculatedRoomPrice < $originalBookingPrice) {
                $refund = $originalBookingPrice - $recalculatedRoomPrice;
                $note .= " Khách trả sớm hơn so với dự kiến ";
            } elseif ($recalculatedRoomPrice > $originalBookingPrice) {
                $extraFee = $recalculatedRoomPrice - $originalBookingPrice;
                $note .= " Khách ở quá giờ, phụ thu thêm " . number_format($extraFee) . " VND.";
            }

            if ($additionalFee > 0 && $surchargeReason) {
                $note .= " Phụ thu: " . number_format($additionalFee) . " VND (Lý do: {$surchargeReason}).";
            } elseif ($additionalFee > 0) {
                $note .= " Phụ thu: " . number_format($additionalFee) . " VND.";
            }

            $totalServiceFee = 0;
            $servicesToInsert = [];

            if (!empty($servicesInput)) {
                $serviceIds = collect($servicesInput)->pluck('service_id');
                $servicesFromDb = \App\Models\Service::whereIn('service_id', $serviceIds)->get()->keyBy('service_id');

                foreach ($servicesInput as $item) {
                    $serviceId = $item['service_id'];
                    $quantity = (int) $item['quantity'];

                    if ($quantity > 0 && $servicesFromDb->has($serviceId)) {
                        $service = $servicesFromDb->get($serviceId);
                        $total = $quantity * $service->price;
                        $totalServiceFee += $total;

                        $servicesToInsert[] = [
                            'booking_detail_id' => $bookingDetail->booking_detail_id,
                            'service_id' => $serviceId,
                            'quantity' => $quantity,
                            'price' => $service->price,
                            'total' => $total,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($servicesToInsert)) {
                    DB::table('booking_hotel_service')->insert($servicesToInsert);
                }
            }

            $actualTotal = $recalculatedRoomPrice + $totalServiceFee + $additionalFee;

            $bookingDetail->update([
                'gia_phong' => $recalculatedRoomPrice,
                'gia_dich_vu' => $totalServiceFee,
                'total_price' => $recalculatedRoomPrice + $totalServiceFee,
            ]);

            $booking->update([
                'status' => 'completed',
                'payment_status' => 'completed',
                'note' => $note,
                'total_price' => $actualTotal,
                'actual_check_out_time' => $actualCheckout,
            ]);

            DB::table('booking_room_status')->insert([
                'customer_id' => $booking->customer_id,
                'booking_id' => $booking->booking_id,
                'booking_detail_id' => $bookingDetail->booking_detail_id,
                'room_id' => $room_id,
                'check_in' => $checkIn,
                'check_out' => $actualCheckout,
                'room_price' => $recalculatedRoomPrice,
                'service_price' => $totalServiceFee,
                'surcharge' => $additionalFee,
                'surcharge_reason' => $surchargeReason,
                'total_paid' => $actualTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $room->update(['status' => 'available']);

            DB::commit();

            return response()->json([
                'message' => 'Thanh toán thành công. Phòng đã chuyển về trạng thái trống.',
                'original_booking_price' => $originalBookingPrice,
                'room_total' => $recalculatedRoomPrice,
                'service_total' => $totalServiceFee,
                'additional_fee' => $additionalFee,
                'surcharge_reason' => $surchargeReason,
                'actual_total' => $actualTotal,
                'note' => $note,
                'calculation_note' => $calculationNote,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning("Lỗi thanh toán: Không tìm thấy bản ghi cần thiết. " . $e->getMessage());
            return response()->json(['message' => 'Không tìm thấy thông tin đặt phòng hoặc phòng để thanh toán.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi thanh toán phòng {$room_id}: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            return response()->json([
                'message' => 'Lỗi máy chủ khi thanh toán phòng.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Yêu cầu hủy đặt phòng từ phía Admin.
     */
    public function requestCancellation(Request $request, $booking_id)
    {
        try {
            $booking = BookingHotel::findOrFail($booking_id);

            if (in_array($booking->status, ['cancelled', 'completed'])) {
                return response()->json(['message' => 'Đặt phòng đã hoàn thành hoặc đã bị hủy. Không thể yêu cầu hủy.'], 400);
            }

            if ($booking->status === 'cancellation_requested') {
                return response()->json(['message' => 'Đặt phòng này đã được yêu cầu hủy trước đó.'], 400);
            }

            $booking->status = 'cancellation_requested';
            $booking->save();

            return response()->json(['message' => 'Yêu cầu hủy đặt phòng đã được gửi thành công.']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy đơn đặt phòng này.'], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi yêu cầu hủy booking ' . $booking_id . ': ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi máy chủ khi yêu cầu hủy.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xác nhận hủy đặt phòng.
     */
    public function confirmCancellation(Request $request, $booking_id)
    {
        try {
            $booking = DB::table('booking_hotel')->where('booking_id', $booking_id)->first();
            if (!$booking) {
                return response()->json(['message' => 'Không tìm thấy đặt phòng.'], 404);
            }

            if ($booking->status !== 'cancellation_requested') {
                return response()->json(['message' => 'Đặt phòng chưa được yêu cầu hủy.'], 400);
            }

            // Cập nhật trạng thái booking
            DB::table('booking_hotel')
                ->where('booking_id', $booking_id)
                ->update(['status' => 'cancelled', 'updated_at' => now()]);

            // Cập nhật trạng thái phòng
            $roomIds = DB::table('booking_hotel_detail')
                ->where('booking_id', $booking_id)
                ->pluck('room_id');
            if ($roomIds->isNotEmpty()) {
                DB::table('rooms')
                    ->whereIn('room_id', $roomIds)
                    ->update(['status' => 'available', 'updated_at' => now()]);
            }

            return response()->json(['message' => 'Hủy đặt phòng thành công.']);
        } catch (\Exception $e) {
            Log::error('Lỗi xác nhận hủy booking ' . $booking_id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xác nhận hủy.', 'error' => $e->getMessage()], 500);
        }
    }
}
