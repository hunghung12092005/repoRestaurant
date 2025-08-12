<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingHistory;
use App\Models\BookingHotel;
use App\Models\BookingHotelDetail;
use App\Models\CancelBooking;
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
                ->join('rooms', 'rooms.room_id', '=', 'bkd.room_id')
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
                    'rooms.room_name',
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
                    'room_name' => $booking->room_name,
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
            if (!isset($validated['check_in_date']) || !isset($validated['check_out_date'])) {
                return response()->json(['message' => 'Vui lòng chọn ngày nhận và ngày trả phòng.'], 422);
            }

            $checkIn = Carbon::parse($validated['check_in_date'] . ' ' . ($validated['check_in_time'] ?? '14:00'));
            $checkOut = Carbon::parse($validated['check_out_date'] . ' ' . ($validated['check_out_time'] ?? '12:00'));

            if ($checkOut <= $checkIn) {
                return response()->json([
                    'message' => '⚠️ Giờ trả phòng phải sau giờ nhận phòng. Vui lòng kiểm tra lại.'
                ], 422);
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
                ->where('bkd.status', '!=', 'cancelled')
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
                $note = "� ở {$hours} giờ.";
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
                'payment_status'   => 'pending',
                'status'           => 'confirmed',
                'note'             => $note,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Thêm chi tiết booking
            $bookingDetailId = DB::table('booking_hotel_detail')->insertGetId([
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

            // Thêm bản ghi vào booking_room_status
            DB::table('booking_room_status')->insert([
                'customer_id'      => $customerId,
                'booking_id'       => $bookingId,
                'booking_detail_id' => $bookingDetailId,
                'room_id'          => $room_id,
                'check_in'         => $checkIn,
                'check_out'        => $checkOut,
                'room_price'       => $total_price,
                'service_price'    => 0,
                'surcharge'        => 0,
                'surcharge_reason' => null,
                'total_paid'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Cập nhật trạng thái phòng
            DB::table('rooms')->where('room_id', $room_id)->update(['status' => 'occupied']);

            return response()->json([
                'message' => 'Đặt phòng thành công',
                'booking_id' => $bookingId,
                'booking_detail_id' => $bookingDetailId,
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

            // Kiểm tra thời gian hợp lệ nếu người dùng đã chọn đầy đủ
            if ($validated['check_in_date'] && $validated['check_out_date']) {
                if ($checkOut <= $checkIn) {
                    Log::warning("Thời gian trả phòng không hợp lệ: check_in={$checkIn}, check_out={$checkOut}");
                    return response()->json([
                        'message' => '⚠️ Vui lòng chọn thời gian trả phòng sau thời gian nhận phòng.'
                    ], 422);
                }

                // Nếu đặt trong cùng ngày, kiểm tra giờ
                if ($validated['check_in_date'] === $validated['check_out_date'] && $checkOutTime <= $checkInTime) {
                    return response()->json([
                        'message' => '⚠️ Giờ trả phòng phải sau giờ nhận phòng trong cùng ngày.'
                    ], 422);
                }
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
     * Gia hạn thời gian lưu trú cho một phòng cụ thể trong booking.
     */


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
    public function updateBookingTime(Request $request, $booking_id)
    {
        $request->validate([
            'check_in_date' => 'required|date',
            'check_in_time' => 'sometimes|required|date_format:H:i',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'check_out_time' => 'sometimes|required|date_format:H:i',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email',
            'address' => 'nullable|string',
        ]);

        try {
            // Lấy thông tin booking
            $booking = DB::table('booking_hotel')->where('booking_id', $booking_id)->first();
            if (!$booking) {
                return response()->json(['message' => 'Không tìm thấy booking.'], 404);
            }

            // Cập nhật thời gian vào bảng booking_hotel
            DB::table('booking_hotel')->where('booking_id', $booking_id)->update([
                'check_in_date' => $request->check_in_date,
                'check_in_time' => $request->check_in_time ?? '14:00',
                'check_out_date' => $request->check_out_date,
                'check_out_time' => $request->check_out_time ?? '12:00',
                'updated_at' => now(),
            ]);

            // Cập nhật thông tin khách hàng
            if ($booking->customer_id) {
                DB::table('customers')->where('customer_id', $booking->customer_id)->update([
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'address' => $request->address,
                    'updated_at' => now(),
                ]);
            }

            // Cập nhật thời gian vào booking_room_status
            $checkIn = Carbon::parse($request->check_in_date . ' ' . ($request->check_in_time ?? '14:00'));
            $checkOut = Carbon::parse($request->check_out_date . ' ' . ($request->check_out_time ?? '12:00'));

            $bookingRoomStatus = DB::table('booking_room_status')
                ->where('booking_id', $booking_id)
                ->first();

            if ($bookingRoomStatus) {
                // Nếu đã có thì cập nhật
                DB::table('booking_room_status')
                    ->where('booking_id', $booking_id)
                    ->update([
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'updated_at' => now(),
                    ]);
            } else {
                // Nếu chưa có thì tạo mới
                DB::table('booking_room_status')->insert([
                    'booking_id' => $booking_id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => 'booked', // hoặc trạng thái mặc định của bạn
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['message' => 'Cập nhật thời gian và thông tin khách hàng thành công.']);
        } catch (\Exception $e) {
            Log::error("Lỗi updateBookingTime booking_id={$booking_id}: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật dữ liệu.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy danh sách phòng theo ngày và thời gian.
     */
    public function getRoomsByDate(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $time = $request->input('time', now()->toTimeString('minute'));

        // Kiểm tra định dạng thời gian
        if ($time && !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            Log::warning("Thời gian không đúng định dạng: time={$time}");
            return response()->json(['message' => 'Thời gian không đúng định dạng (H:i).'], 400);
        }
        try {
            Log::info('Bắt đầu lấy danh sách phòng', ['date' => $date, 'time' => $time]);

            // Lấy danh sách phòng cùng trạng thái từ DB
            $rooms = DB::table('rooms')
                ->join('room_types', 'rooms.type_id', '=', 'room_types.type_id')
                ->select(
                    'rooms.room_id',
                    'rooms.room_name',
                    'rooms.type_id',
                    'rooms.floor_number',
                    'rooms.status', // lấy trạng thái phòng từ DB
                    'room_types.type_name',
                    'room_types.bed_count',
                    'room_types.max_occupancy',
                    'room_types.max_occupancy_child'
                )
                ->orderBy('rooms.floor_number')
                ->orderBy('rooms.room_name')
                ->get();

            Log::info('Lấy danh sách phòng thành công', ['room_count' => $rooms->count()]);

            foreach ($rooms as $room) {
                Log::info('Kiểm tra booking liên quan phòng', ['room_id' => $room->room_id]);

                $bookingInfo = DB::table('booking_hotel_detail as bkd')
                    ->join('booking_hotel as bk', 'bk.booking_id', '=', 'bkd.booking_id')
                    ->where('bkd.room_id', $room->room_id)
                    ->whereIn('bk.status', ['pending', 'confirmed', 'cancellation_requested'])
                    ->where('bkd.status', 'active')  // chỉ lấy booking còn hiệu lực
                    ->whereNotNull('bk.check_in_date')
                    ->whereNotNull('bk.check_out_date')
                    ->where(function ($query) use ($date, $time) {
                        $query->whereDate('bk.check_in_date', '<=', $date)
                            ->whereDate('bk.check_out_date', '>=', $date);

                        if ($time) {
                            $query->whereRaw('CONCAT(bk.check_in_date, " ", COALESCE(bk.check_in_time, "14:00")) <= ?', [$date . ' ' . $time])
                                ->whereRaw('CONCAT(bk.check_out_date, " ", COALESCE(bk.check_out_time, "12:00")) >= ?', [$date . ' ' . $time]);
                        }
                    })
                    ->select(
                        'bk.booking_id',
                        'bk.status as booking_status',
                        'bkd.booking_detail_id'
                    )
                    ->first();

                // Gán booking info nhưng KHÔNG ghi đè lại trạng thái phòng đã lấy từ DB
                if ($bookingInfo) {
                    Log::info('Tìm thấy booking', ['room_id' => $room->room_id, 'booking_id' => $bookingInfo->booking_id]);
                    $room->booking_id = $bookingInfo->booking_id;
                    $room->booking_detail_id = $bookingInfo->booking_detail_id;
                    $room->payment_status = 'pending'; // nếu bạn cần
                } else {
                    Log::info('Không tìm thấy booking', ['room_id' => $room->room_id]);
                    $room->booking_id = null;
                    $room->booking_detail_id = null;
                    $room->payment_status = 'pending'; // nếu bạn cần
                }
            }

            return response()->json($rooms);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách phòng theo ngày và thời gian: ' . $e->getMessage(), [
                'exception' => $e,
                'date' => $date,
                'time' => $time,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Lỗi khi lấy danh sách phòng.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }


    /**
     * Trả phòng và thanh toán cho một phòng cụ thể trong booking.
     */
    public function checkoutRoom(Request $request, $booking_detail_id)
    {
        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();

        try {
            // Tìm chi tiết đặt phòng
            $bookingDetail = BookingHotelDetail::with('booking')
                ->where('booking_detail_id', $booking_detail_id)
                ->whereHas('booking', function ($query) {
                    $query->whereIn('status', ['pending', 'confirmed']);
                })
                ->firstOrFail();

            $booking = $bookingDetail->booking;
            $room_id = $bookingDetail->room_id;

            // Kiểm tra phòng
            $room = Room::with('roomType')->findOrFail($room_id);
            if (!$room->roomType) {
                throw new \Exception("Phòng chưa được gán loại.");
            }

            // Lấy thông tin giá
            $now = now();
            $price = Price::where('type_id', $room->type_id)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderByDesc('priority')
                ->first();

            if (!$price) {
                throw new \Exception("Không tìm thấy bảng giá hiện tại cho loại phòng này.");
            }

            // Lấy thời gian và tính toán giá phòng
            $checkIn = Carbon::parse($booking->check_in_date . ' ' . ($booking->check_in_time ?? '14:00'));
            $actualCheckout = now();
            $totalHours = $checkIn->floatDiffInHours($actualCheckout);

            // Kiểm tra thời gian trả phòng hợp lệ
            if ($totalHours < 0) {
                DB::rollBack();
                return response()->json([
                    'message' => "Chưa đến ngày trả phòng. Hiện tại là {$actualCheckout->format('d-m-Y H:i')}, nhưng thời gian nhận phòng là {$checkIn->format('d-m-Y H:i')}."
                ], 400);
            }

            $recalculatedRoomPrice = 0;
            $note = '';
            $calculationNote = '';

            if ($totalHours <= 2) {
                $recalculatedRoomPrice = 2 * $price->hourly_price;
                $note = "Ở dưới 2 giờ, tính giá cố định 2 giờ.";
                $calculationNote = "Tính theo giờ: 2 giờ x " . number_format($price->hourly_price) . "đ = " . number_format($recalculatedRoomPrice) . "đ";
            } elseif ($totalHours <= 6) {
                $hours = ceil($totalHours);
                $recalculatedRoomPrice = $hours * $price->hourly_price;
                $note = "Ở {$hours} giờ.";
                $calculationNote = "Tính theo giờ: {$hours} giờ x " . number_format($price->hourly_price) . "đ = " . number_format($recalculatedRoomPrice) . "đ";
            } else {
                $fullDays = ceil($totalHours / 24);
                $recalculatedRoomPrice = $fullDays * $price->price_per_night;
                $note = "Ở {$fullDays} ngày (trên 6 giờ, tính theo ngày).";
                $calculationNote = "Tính theo ngày: {$fullDays} ngày x " . number_format($price->price_per_night) . "đ = " . number_format($recalculatedRoomPrice) . "đ";
            }

            // Xử lý phụ phí và dịch vụ
            $additionalFee = (float) $request->input('additional_fee', 0);
            $surchargeReason = $request->input('surcharge_reason', null);
            $servicesInput = $request->input('services', []);

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
                            'booking_detail_id' => $booking_detail_id,
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

            // Tính tổng tiền phòng
            $actualTotal = $recalculatedRoomPrice + $totalServiceFee + $additionalFee;

            // Cập nhật booking_hotel_detail
            $bookingDetail->update([
                'gia_phong' => $recalculatedRoomPrice,
                'gia_dich_vu' => $totalServiceFee,
                'total_price' => $recalculatedRoomPrice + $totalServiceFee,
                'note' => $note,
                'status' => 'paid',
                'updated_at' => $now,
            ]);

            // Cập nhật hoặc tạo booking_room_status
            $roomStatus = DB::table('booking_room_status')
                ->where('booking_detail_id', $booking_detail_id)
                ->first();

            if ($roomStatus) {
                DB::table('booking_room_status')
                    ->where('booking_detail_id', $booking_detail_id)
                    ->update([
                        'room_price' => $recalculatedRoomPrice,
                        'service_price' => $totalServiceFee,
                        'surcharge' => $additionalFee,
                        //'surcharge_reason' => $surchargeReason,
                        'total_paid' => $actualTotal,
                        'check_out' => $actualCheckout,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('booking_room_status')->insert([
                    'customer_id' => $booking->customer_id,
                    'booking_id' => $booking->booking_id,
                    'booking_detail_id' => $booking_detail_id,
                    'room_id' => $room_id,
                    'check_in' => $checkIn,
                    'check_out' => $actualCheckout,
                    'room_price' => $recalculatedRoomPrice,
                    'service_price' => $totalServiceFee,
                    'surcharge' => $additionalFee,
                    'surcharge_reason' => $surchargeReason,
                    'total_paid' => $actualTotal,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Cập nhật trạng thái phòng

            // Kiểm tra xem tất cả các phòng trong booking đã thanh toán chưa
            $allDetails = BookingHotelDetail::where('booking_id', $booking->booking_id)->get();
            $allPaid = DB::table('booking_room_status')
                ->where('booking_id', $booking->booking_id)
                ->where('total_paid', '>', 0) // Thay vì kiểm tra payment_status
                ->count() === $allDetails->count();

            $response = [
                'message' => 'Thanh toán phòng thành công.',
                'booking_detail_id' => $booking_detail_id,
                'room_total' => $recalculatedRoomPrice,
                'service_total' => $totalServiceFee,
                'additional_fee' => $additionalFee,
                'surcharge_reason' => $surchargeReason,
                'actual_total' => $actualTotal,
                'calculation_note' => $calculationNote,
                'note' => $bookingDetail->note ?? 'Không có ghi chú.',
                'booking_completed' => $allPaid,
                'room_id' => $room_id,
            ];
            //return response()->json($room_id);
            Room::where('room_id', $room_id)->update(['status' => 'available']);

            // Nếu tất cả phòng đã thanh toán, cập nhật booking_hotel
            if ($allPaid) {
                $booking_total = DB::table('booking_room_status')
                    ->where('booking_id', $booking->booking_id)
                    ->sum('total_paid');

                $booking->update([
                    'status' => 'completed',
                    'payment_status' => 'completed',
                    'total_price' => $booking_total,
                    'check_out_time' => $actualCheckout,
                    'updated_at' => $now,
                ]);

                $response['booking_total'] = $booking_total;
            }

            // Commit transaction
            DB::commit();
            // return response()->json([
            //     'message' => 'Thanh toán phòng thành công dajdaadjb.',
            //     'room_id' => $room_id,
            // ]);
            return response()->json($response);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning("Lỗi thanh toán: Không tìm thấy bản ghi cần thiết. " . $e->getMessage());
            return response()->json(['message' => 'Không tìm thấy thông tin đặt phòng hoặc phòng để thanh toán.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi thanh toán booking_detail_id {$booking_detail_id}: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            return response()->json([
                'message' => 'Lỗi máy chủ khi thanh toán phòng.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

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
    public function getFutureBookings()
    {
        $now = Carbon::now();

        $futureBookings = BookingHotelDetail::with(['room', 'booking.customer'])
            ->whereHas('booking', function ($query) use ($now) {
                $query->where('status', '!=', 'cancelled')
                    ->whereRaw("STR_TO_DATE(CONCAT(check_in_date, ' ', check_in_time), '%Y-%m-%d %H:%i:%s') > ?", [$now]);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->booking_detail_id,
                    'room_name' => optional($item->room)->room_name,
                    'customer_name' => optional($item->booking->customer)->customer_name ?? 'Không rõ',
                    'check_in_time' => optional($item->booking)->check_in_date . ' ' . optional($item->booking)->check_in_time,
                ];
            });

        return response()->json($futureBookings);
    }

    public function cancelNow(Request $request)
    {
        $request->validate([
            'detail_id' => 'required|exists:booking_hotel_detail,booking_detail_id',
            'reason' => 'nullable|string',
        ]);

        $detail = BookingHotelDetail::with('booking')->findOrFail($request->detail_id);
        $booking = $detail->booking;

        $checkIn = Carbon::parse($booking->check_in_date . ' ' . $booking->check_in_time);
        $now = Carbon::now();

        if ($now->gte($checkIn)) {
            return response()->json([
                'message' => 'Không thể hủy vì đã đến giờ check-in hoặc đang sử dụng.'
            ], 422);
        }

        // Ghi nhận yêu cầu hủy vào bảng cancelbooking
        CancelBooking::create([
            'booking_id' => $booking->booking_id,
            'customer_id' => $booking->customer_id,
            'cancellation_reason' => $request->reason ?? 'Không có lý do',
            'refund_amount' => 0.00,
            'status' => 'requested',
        ]);

        $booking->status = 'cancelled';
        $booking->save();

        $detail->status = 'cancelled';
        $detail->save();

        // Trả phòng nếu có phòng gắn với booking detail
        $room = $detail->room;
        if ($room) {
            $room->status = 'available';
            $room->save();
        }

        return response()->json(['message' => 'Đã hủy phòng thành công.']);
    }
    public function addMultipleBookings(Request $request)
    {
        $validated = $request->validate([
            'bookings' => 'required|array|min:1',
            'bookings.*.room_id' => 'required|exists:rooms,room_id',
            'bookings.*.customer_name' => 'required|string|max:255',
            'bookings.*.customer_phone' => 'required|digits_between:10,11',
            'bookings.*.customer_email' => 'nullable|email|max:255',
            'bookings.*.customer_id_number' => 'required|digits:12',
            'bookings.*.check_in_date' => 'required|date',
            'bookings.*.check_in_time' => 'nullable|date_format:H:i',
            'bookings.*.check_out_date' => 'required|date',
            'bookings.*.check_out_time' => 'nullable|date_format:H:i',
            'bookings.*.pricing_type' => 'required|in:hourly,nightly',
        ]);

        DB::beginTransaction();

        try {
            $data = $validated['bookings'];
            $first = $data[0];

            // Thời gian dùng chung
            $checkIn = Carbon::parse($first['check_in_date'] . ' ' . ($first['check_in_time'] ?? '14:00'));
            $checkOut = Carbon::parse($first['check_out_date'] . ' ' . ($first['check_out_time'] ?? '12:00'));

            // Tạo khách hàng
            $customerId = DB::table('customers')->insertGetId([
                'customer_name' => $first['customer_name'],
                'customer_phone' => $first['customer_phone'],
                'customer_email' => $first['customer_email'],
                'customer_id_number' => $first['customer_id_number'],
                'room_id' => $first['room_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tạo booking chung
            $bookingId = DB::table('booking_hotel')->insertGetId([
                'customer_id' => $customerId,
                'payment_method' => 'thanh_toan_ngay',
                'booking_type' => 'offline',
                'pricing_type' => $first['pricing_type'],
                'check_in_date' => $first['check_in_date'],
                'check_out_date' => $first['check_out_date'],
                'check_in_time' => $first['check_in_time'] ?? '14:00',
                'check_out_time' => $first['check_out_time'] ?? '12:00',
                'total_rooms' => count($data),
                'total_price' => 0,
                'additional_fee' => 0,
                'payment_status' => 'pending',
                'status' => 'confirmed',
                'note' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $total_price_all = 0;

            foreach ($data as $row) {
                // Kiểm tra phòng
                $room = DB::table('rooms')->where('room_id', $row['room_id'])->first();
                if (!$room) {
                    throw new \Exception("Không tìm thấy phòng ID: {$row['room_id']}");
                }

                // Kiểm tra trùng lịch
                $overlap = DB::table('booking_hotel_detail as bkd')
                    ->join('booking_hotel as bk', 'bk.booking_id', '=', 'bkd.booking_id')
                    ->where('bkd.room_id', $row['room_id'])
                    ->whereNotIn('bk.status', ['completed', 'cancelled'])
                    ->whereRaw("
                    TIMESTAMP(bk.check_in_date, bk.check_in_time) < ?
                    AND TIMESTAMP(bk.check_out_date, bk.check_out_time) > ?
                ", [
                        $checkOut->format('Y-m-d H:i:s'),
                        $checkIn->format('Y-m-d H:i:s')
                    ])
                    ->exists();

                if ($overlap) {
                    throw new \Exception("Phòng ID {$row['room_id']} đã được đặt trong thời gian này.");
                }

                // Lấy bảng giá
                $price = DB::table('prices')
                    ->where('type_id', $room->type_id)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->orderByDesc('priority')
                    ->first();

                if (!$price) {
                    throw new \Exception("Không tìm thấy bảng giá cho loại phòng ID: {$room->type_id}");
                }

                // Tính tiền
                $total_price = 0;
                if ($row['pricing_type'] === 'hourly') {
                    $totalHours = $checkIn->floatDiffInHours($checkOut);
                    if ($totalHours <= 2) {
                        $total_price = 2 * $price->hourly_price;
                    } elseif ($totalHours <= 6) {
                        $total_price = ceil($totalHours) * $price->hourly_price;
                    } else {
                        $total_price = ceil($totalHours / 24) * $price->price_per_night;
                    }
                } else {
                    $totalNights = $checkIn->diffInDays($checkOut);
                    $total_price = max(1, $totalNights) * $price->price_per_night;
                }

                $total_price_all += $total_price;

                // booking_hotel_detail
                $bookingDetailId = DB::table('booking_hotel_detail')->insertGetId([
                    'booking_id' => $bookingId,
                    'room_type' => $room->type_id,
                    'room_id' => $row['room_id'],
                    'gia_phong' => $total_price,
                    'gia_dich_vu' => 0,
                    'total_price' => $total_price,
                    'note' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // booking_room_status
                DB::table('booking_room_status')->insert([
                    'customer_id' => $customerId,
                    'booking_id' => $bookingId,
                    'booking_detail_id' => $bookingDetailId,
                    'room_id' => $row['room_id'],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'room_price' => $total_price,
                    'service_price' => 0,
                    'surcharge' => 0,
                    'surcharge_reason' => null,
                    'total_paid' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Cập nhật trạng thái phòng
                DB::table('rooms')->where('room_id', $row['room_id'])->update(['status' => 'occupied']);
            }

            // Cập nhật tổng tiền
            DB::table('booking_hotel')->where('booking_id', $bookingId)->update([
                'total_price' => $total_price_all
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Đặt nhiều phòng thành công!',
                'booking_id' => $bookingId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi đặt nhiều phòng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Lỗi khi đặt phòng!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getBookingDetails($booking_id)
    {
        try {
            // Lấy booking và kiểm tra trạng thái
            $booking = DB::table('booking_hotel')->where('booking_id', $booking_id)->first();

            if (!$booking) {
                return response()->json(['message' => 'Không tìm thấy booking.'], 404);
            }

            if ($booking->status === 'completed') {
                return response()->json(['message' => 'Booking đã thanh toán xong.'], 400);
            }

            // Lấy danh sách chi tiết phòng thuộc booking này
            $details = DB::table('booking_hotel_detail as bkd')
                ->join('rooms', 'rooms.room_id', '=', 'bkd.room_id')
                ->join('room_types', 'room_types.type_id', '=', 'rooms.type_id')
                ->where('bkd.booking_id', $booking_id)
                ->select(
                    'bkd.booking_detail_id',
                    'rooms.room_name',
                    'rooms.floor_number',
                    'room_types.type_name'
                )
                ->get();

            return response()->json($details);
        } catch (\Exception $e) {
            Log::error("Lỗi lấy chi tiết booking {$booking_id}: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi lấy chi tiết booking.', 'error' => $e->getMessage()], 500);
        }
    }
    public function getUnpaidBookings()
    {
        try {
            $bookings = DB::table('booking_hotel as bk')
                ->join('customers as c', 'bk.customer_id', '=', 'c.customer_id')
                ->whereNotIn('bk.status', ['completed', 'cancelled'])
                ->select('bk.booking_id', 'c.customer_name')
                ->orderByDesc('bk.booking_id')
                ->get();

            return response()->json($bookings);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách booking chưa thanh toán: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tải danh sách booking.', 'error' => $e->getMessage()], 500);
        }
    }
    public function payByBookingId(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:booking_hotel,booking_id',
            'rooms' => 'required|array|min:1',
            'rooms.*.booking_detail_id' => 'required|exists:booking_hotel_detail,booking_detail_id',
            'rooms.*.services' => 'array',
            'rooms.*.additional_fee' => 'nullable|numeric',
            'rooms.*.surcharge_reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $now = now();
            $booking_id = $request->booking_id;

            foreach ($request->rooms as $room) {
                $detailId = $room['booking_detail_id'];

                $bookingDetail = BookingHotelDetail::with('booking')->find($detailId);
                if (!$bookingDetail) {
                    throw new \Exception("Không tìm thấy booking detail ID {$detailId}");
                }

                $booking = $bookingDetail->booking;

                // Bỏ qua nếu đã thanh toán
                $alreadyPaid = DB::table('booking_room_status')
                    ->where('booking_detail_id', $detailId)
                    ->where('total_paid', '>', 0)
                    ->exists();

                if ($alreadyPaid) {
                    continue;
                }

                // Lấy giá phòng
                $roomModel = Room::with('roomType')->find($bookingDetail->room_id);
                if (!$roomModel) {
                    throw new \Exception("Không tìm thấy phòng ID {$bookingDetail->room_id}");
                }

                $price = Price::where('type_id', $roomModel->type_id)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->orderByDesc('priority')
                    ->first();

                if (!$price) {
                    throw new \Exception("Không tìm thấy bảng giá cho phòng {$roomModel->room_name}");
                }

                // Tính tiền phòng
                $checkIn = Carbon::parse($booking->check_in_date . ' ' . ($booking->check_in_time ?? '14:00'));
                $checkOut = now();
                $hours = $checkIn->floatDiffInHours($checkOut);

                if ($hours <= 2) {
                    $roomPrice = 2 * $price->hourly_price;
                } elseif ($hours <= 6) {
                    $roomPrice = ceil($hours) * $price->hourly_price;
                } else {
                    $roomPrice = ceil($hours / 24) * $price->price_per_night;
                }

                // Dịch vụ
                $totalService = 0;
                if (!empty($room['services'])) {
                    foreach ($room['services'] as $s) {
                        $service = \App\Models\Service::find($s['service_id']);
                        if ($service && $s['quantity'] > 0) {
                            $totalService += $service->price * $s['quantity'];

                            DB::table('booking_hotel_service')->insert([
                                'booking_detail_id' => $detailId,
                                'service_id' => $service->service_id,
                                'quantity' => $s['quantity'],
                                'price' => $service->price,
                                'total' => $service->price * $s['quantity'],
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }
                }

                $additionalFee = $room['additional_fee'] ?? 0;
                $surchargeReason = $room['surcharge_reason'] ?? null;
                $total = $roomPrice + $totalService + $additionalFee;

                // Update booking detail
                $bookingDetail->update([
                    'gia_phong' => $roomPrice,
                    'gia_dich_vu' => $totalService,
                    'total_price' => $total,
                    'updated_at' => $now,
                ]);

                // Update or insert status
                DB::table('booking_room_status')->updateOrInsert(
                    ['booking_detail_id' => $detailId],
                    [
                        'customer_id' => $booking->customer_id,
                        'booking_id' => $booking_id,
                        'room_id' => $bookingDetail->room_id,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'room_price' => $roomPrice,
                        'service_price' => $totalService,
                        'surcharge' => $additionalFee,
                        'surcharge_reason' => $surchargeReason,
                        'total_paid' => $total,
                        'updated_at' => $now,
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'), // tránh overwrite
                    ]
                );

                // Update trạng thái phòng
                DB::table('rooms')
                    ->where('room_id', $bookingDetail->room_id)
                    ->update(['status' => 'available']);
            }

            // Kiểm tra đã thanh toán hết chưa
            $totalPaid = DB::table('booking_room_status')
                ->where('booking_id', $booking_id)
                ->sum('total_paid');

            $totalRoom = DB::table('booking_hotel_detail')->where('booking_id', $booking_id)->count();
            $paidRoom = DB::table('booking_room_status')->where('booking_id', $booking_id)->where('total_paid', '>', 0)->count();

            if ($totalRoom === $paidRoom) {
                DB::table('booking_hotel')->where('booking_id', $booking_id)->update([
                    'status' => 'completed',
                    'payment_status' => 'completed',
                    'total_price' => $totalPaid,
                    'check_out_time' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Thanh toán nhóm thành công.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi thanh toán nhóm: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Lỗi thanh toán nhóm.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    //roi phong
    public function getAvailableRoomsLeaveRoom($typeId, $roomId)
    {
        // Cập nhật phòng hiện tại thành Available
        DB::table('rooms')
            ->where('room_id', $roomId)
            ->update(['status' => 'Available']);

        // Lấy danh sách phòng trống theo loại
        $rooms = DB::table('rooms')
            ->where('type_id', $typeId)
            ->where('status', 'Available')
            ->select('room_id', 'room_name', 'floor_number', 'status')
            ->orderBy('floor_number')
            ->orderBy('room_name')
            ->get();

        return response()->json($rooms);
    }
    public function changeRoom(Request $request)
    {
        $request->validate([
            'booking_detail_id' => 'required',
            'new_room_id' => 'required',
            'reason' => 'required|string' // validate lý do
        ]);

        // Lấy booking detail
        $detail = BookingHotelDetail::where('booking_detail_id', $request->booking_detail_id)
            ->firstOrFail();

        // Cập nhật phòng trong booking detail
        $detail->room_id = $request->new_room_id;
        $detail->save();

        // Cập nhật trạng thái phòng mới => Occupied
        Room::where('room_id', $request->new_room_id)->update(['status' => 'occupied']);

        // Cập nhật room_id + lý do vào BookingHistory
        BookingHistory::where('booking_detail_id', $request->booking_detail_id)
            ->update([
                'room_id' => $request->new_room_id,
                'surcharge_reason' => $request->reason // lưu lý do đổi phòng vào cột status
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Room changed successfully',
            'data' => $detail
        ]);
    }
}
