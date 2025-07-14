<?php

namespace App\Http\Controllers;

use App\Models\BookingHotel;
use App\Models\BookingHotelDetail;
use App\Models\BookingHotelService;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use PayOS\PayOS;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use GuzzleHttp\Client; // === THÊM MÃ === Thêm import để sử dụng Guzzle
use GuzzleHttp\Exception\RequestException; // === THÊM MÃ === Thêm import để xử lý lỗi Guzzle

class BookingHotelController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $checkInDate = $request->input('check_in_date');
        $checkOutDate = $request->input('check_out_date');

        // Lấy tất cả các loại phòng
        $roomTypes = RoomType::all();
        $availability = [];

        foreach ($roomTypes as $roomType) {
            // Lấy tất cả các phòng thuộc loại này
            $rooms = Room::where('type_id', $roomType->type_id)->get();
            //return response()->json(['rooms' => $rooms, 'roomType' => $roomType]);
            // Lấy các room_id đã được đặt trong khoảng thời gian này
            $bookedRoomIds = BookingHotelDetail::whereHas('booking', function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('status', 'confirmed')
                    ->where(function ($query) use ($checkInDate, $checkOutDate) {
                        $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                            ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate]);
                    });
            })->pluck('room_id');

            // Tính số lượng phòng còn trống
            $availableRoomsCount = $rooms->whereNotIn('room_id', $bookedRoomIds)->count();

            $availability[] = [
                'room_type' => $roomType->type_id,
                'available_rooms' => $availableRoomsCount
            ];
        }

        return response()->json($availability);
    }
    /**
     * Lấy danh sách phòng trống dựa trên room_type
     */
    public function getAvailableRooms(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'room_type' => 'required|integer|exists:room_types,type_id',
        ]);

        $roomTypeId = $request->input('room_type');

        try {
            // Lấy danh sách booking đang hoạt động (pending_confirmation hoặc confirmed)
            $activeBookings = BookingHotel::whereIn('status', ['pending_confirmation', 'confirmed'])
                ->pluck('booking_id');

            // Lấy danh sách room_id đã được gán trong các booking đang hoạt động
            $occupiedRoomIds = BookingHotelDetail::whereIn('booking_id', $activeBookings)
                ->whereNotNull('room_id')
                ->pluck('room_id');

            // Lấy danh sách phòng trống thuộc room_type
            $availableRooms = Room::where('type_id', $roomTypeId)
                ->where('status', 'available') // Chỉ lấy phòng có trạng thái available
                ->whereNotIn('room_id', $occupiedRoomIds)
                ->with('roomType')
                ->get()
                ->map(function ($room) {
                    return [
                        'room_id' => $room->room_id,
                        'room_name' => $room->room_name,
                        'type_name' => $room->roomType->type_name ?? 'N/A',
                        'status' => $room->status, // Trả về trạng thái phòng để frontend xử lý
                    ];
                });

            Log::info('Lấy danh sách phòng trống thành công', [
                'room_type' => $roomTypeId,
                'available_rooms_count' => $availableRooms->count(),
                'occupied_room_ids' => $occupiedRoomIds->toArray(),
            ]);

            return response()->json($availableRooms, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách phòng trống: ' . $e->getMessage(), [
                'room_type' => $roomTypeId,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Không thể lấy danh sách phòng trống',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo token JWT cho khách hàng
     */
    public function generateToken(Request $request)
    {
        try {
            // Xác thực dữ liệu nhập vào
            $request->validate([
                'name' => 'required|string',
                'phone' => 'required',
            ]);

            // Tìm hoặc tạo khách hàng dựa trên phone
            $customer = Customer::firstOrCreate(['customer_phone' => $request->phone], [
                'customer_name' => $request->name,
                'customer_phone' => $request->phone,
                'address' => $request->address ?? 'Unknown',
            ]);

            // Tạo token cho khách hàng
            $token = JWTAuth::fromUser($customer);
            return response()->json(['token' => $token]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create token',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo mới booking
     */
    public function storeBooking(Request $request)
    {
        try {
            // Validate the incoming request data
            $bookingDetails = $request->validate([
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date',
                'total_rooms' => 'required|integer',
                'roomDetails' => 'required|array',
                'total_price' => 'required|numeric',
                'payment_method' => 'required',
                'booking_type' => 'required|string',
                'note' => 'nullable|string',
                'orderCode' => 'nullable',
            ]);

            // Lấy token từ header
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            // Xác thực token và lấy thông tin người dùng
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if (!$user) {
                    $sub = JWTAuth::parseToken()->getPayload()->get('sub');
                    $user = Customer::find($sub);
                }
                if (!$user) {
                    return response()->json(['token' => false], 404);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'Token is invalid'], 401);
            }

            // Lấy customer_id từ user
            $customerId = $user->customer_id;

            // Lưu thông tin booking chính
            $booking = BookingHotel::create([
                'customer_id' => $customerId,
                'payment_method' => $bookingDetails['payment_method'],
                'booking_type' => $bookingDetails['booking_type'],
                'orderCode' => $bookingDetails['orderCode'],
                'check_in_date' => $bookingDetails['check_in_date'],
                'check_out_date' => $bookingDetails['check_out_date'],
                'total_rooms' => $bookingDetails['total_rooms'],
                'total_price' => $bookingDetails['total_price'],
                'payment_status' => 'pending',
                'status' => 'pending_confirmation',
                'note' => $bookingDetails['note'],
            ]);

            // Lưu thông tin chi tiết cho từng phòng
            foreach ($bookingDetails['roomDetails'] as $roomDetail) {
                $bookingDetail = BookingHotelDetail::create([
                    'booking_id' => $booking->booking_id,
                    'room_type' => (int)$roomDetail['id'], // ID loại phòng
                    'gia_phong' => number_format($roomDetail['price'], 2, '.', ''), // Định dạng giá
                    'gia_dich_vu' => number_format($roomDetail['totalServiceCost'], 2, '.', ''), // Định dạng giá
                    'total_price' => number_format($roomDetail['totalServiceCost'] + $roomDetail['price'], 2, '.', ''),
                    'note' => $bookingDetails['note'],
                ]);

                // Lưu thông tin dịch vụ cho từng phòng
                foreach ($roomDetail['serviceChoose'] as $serviceId) {
                    BookingHotelService::create([
                        'booking_detail_id' => $bookingDetail->id,
                        'service_id' => $serviceId,
                    ]);
                }
            }

            return response()->json(['message' => 'Booking created successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lấy lịch sử booking của khách hàng
     */
    public function getBookingHistory(Request $request)
    {
        try {
            $sub = JWTAuth::parseToken()->getPayload()->get('sub');

            $bookings = BookingHotel::where('customer_id', $sub)
                ->with('roomTypeInfo')
                ->orderBy('booking_id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $bookings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy dữ liệu booking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo link thanh toán PayOS
     */
    public function payos(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.price' => 'required|numeric',
        ]);

        $amount = $request->input('amount');
        $items = $request->input('items');
        // Lấy booking_id mới nhất với payment_method là 'thanh_toan_qr'
        $latestBooking = BookingHotel::where('payment_method', 'thanh_toan_qr')
            ->orderBy('created_at', 'desc') // Sắp xếp theo ngày tạo
            ->first(); // Lấy bản ghi đầu tiên (mới nhất)
        if ($latestBooking) {
            $bookingId = $latestBooking->booking_id; // Hoặc trường booking_id nếu bạn có trường này
            // return response()->json(['checkoutUrl' => $latestBooking->booking_id], 200);
        } else {
            $bookingId = null; // Nếu không có bản ghi nào
        }

        $data = [
            "orderCode" => intval(substr(strval(microtime(true) * 10000), -6)),
            "amount" => $amount,
            "description" => " booking HXH",
            "items" => $items,
            "returnUrl" => "http://127.0.0.1:5173/ThanksBooking",
            "cancelUrl" => "http://127.0.0.1:5173/"
        ];
        try {
            $payOSClientId = '078daf0e-baed-45f9-b2f9-20b79c89668e';
            $payOSApiKey = '8841f63c-c976-4b89-bf56-b769b035292b';
            $payOSChecksumKey = '266e7a5270d2b2b95f9e85c89215c258fa838acb23c3f62916d7e16ea0dcaf2d';

            $payOS = new PayOS($payOSClientId, $payOSApiKey, $payOSChecksumKey);
            $response = $payOS->createPaymentLink($data);

            if (isset($response['checkoutUrl'])) {
                return response()->json([
                    'checkoutUrl' => $response['checkoutUrl'],
                    'orderCode' => $response['orderCode']
                ], 200);
            } else {
                return response()->json(['error' => 'Không thể tạo liên kết thanh toán.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi PayOS: ' . $e->getMessage(),
                'code' => $e->getCode(),
            ], 500);
        }
    }

    /**
     * Lấy danh sách booking
     */
    public function getBookings(Request $request)
    {
        try {
            $statuses = $request->query('status', ['pending_confirmation', 'confirmed', 'completed']);
            $statuses = is_array($statuses) ? $statuses : [$statuses];

            $bookings = BookingHotel::with(['customer', 'details.roomType'])
                ->whereIn('status', $statuses)
                ->get()
                ->map(function ($booking) {
                    $booking->details->each(function ($detail) {
                        $detail->type_name = $detail->roomType ? $detail->roomType->type_name : 'Loại phòng không xác định';
                    });

                    // === THÊM MÃ === Lấy trạng thái thanh toán từ PayOS và định dạng tiếng Việt
                    $paymentStatusDisplay = 'Chưa xác định';
                    if ($booking->payment_method === 'thanh_toan_qr' && !empty($booking->orderCode)) {
                        try {
                            $client = new Client();
                            $response = $client->get("https://api-merchant.payos.vn/v2/payment-requests/{$booking->orderCode}", [
                                'headers' => [
                                    'x-client-id' => env('PAYOS_CLIENT_ID'),
                                    'x-api-key' => env('PAYOS_API_KEY'),
                                ]
                            ]);
                            $paymentInfo = json_decode($response->getBody()->getContents(), true);
                            $status = isset($paymentInfo['data']['status']) ? strtoupper($paymentInfo['data']['status']) : 'UNKNOWN';
                            $paymentStatusDisplay = $this->formatPaymentStatus($status);
                        } catch (RequestException $e) {
                            Log::warning('Lỗi khi kiểm tra trạng thái thanh toán PayOS: ' . $e->getMessage(), [
                                'orderCode' => $booking->orderCode,
                                'booking_id' => $booking->booking_id
                            ]);
                            $paymentStatusDisplay = 'Lỗi kiểm tra thanh toán';
                        }
                    } else if ($booking->payment_method !== 'thanh_toan_qr') {
                        $paymentStatusDisplay = 'Thanh toán trực tiếp';
                    }
                    $booking->payment_status_display = $paymentStatusDisplay;
                    // === KẾT THÚC MÃ THÊM ===

                    return $booking;
                });

            Log::info('Lấy danh sách booking thành công', [
                'statuses' => $statuses,
                'count' => $bookings->count()
            ]);
            return response()->json($bookings, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'statuses' => $statuses
            ]);
            return response()->json([
                'error' => 'Không thể lấy danh sách booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hàm định dạng trạng thái thanh toán thành tiếng Việt (THÊM MỚI)
     */
    private function formatPaymentStatus($status)
    {
        $statusMap = [
            'PENDING' => 'Chờ thanh toán',
            'PAID' => 'Đã thanh toán',
            'CANCELLED' => 'Đã hủy',
            'REFUNDED' => 'Đã hoàn tiền',
            'UNKNOWN' => 'Chưa xác định',
            'ERROR' => 'Lỗi kiểm tra thanh toán'
        ];
        return $statusMap[$status] ?? 'Trạng thái không xác định';
    }

    /**
     * Lấy chi tiết booking
     */
    public function getBookingDetails($bookingId)
    {
        try {
            $bookingDetails = BookingHotelDetail::where('booking_id', $bookingId)
                ->with(['room', 'booking.customer', 'roomType'])
                ->get()
                ->map(function ($detail) {
                    return [
                        'booking_detail_id' => $detail->booking_detail_id,
                        'booking_id' => $detail->booking_id,
                        'room_type' => $detail->room_type,
                        'room_id' => $detail->room_id,
                        'gia_phong' => $detail->gia_phong,
                        'gia_dich_vu' => $detail->gia_dich_vu,
                        'total_price' => $detail->total_price,
                        'note' => $detail->note,
                        'room' => $detail->room ? [
                            'room_id' => $detail->room->room_id,
                            'room_name' => $detail->room->room_name,
                            'status' => $detail->room->status,
                        ] : null,
                        'roomType' => $detail->roomType ? [
                            'type_id' => $detail->roomType->type_id,
                            'type_name' => $detail->roomType->type_name,
                        ] : null,
                        'booking' => [
                            'customer' => $detail->booking->customer ? [
                                'customer_id' => $detail->booking->customer->customer_id,
                                'customer_name' => $detail->booking->customer->customer_name,
                                'customer_phone' => $detail->booking->customer->customer_phone,
                                'customer_email' => $detail->booking->customer->customer_email,
                            ] : null,
                        ],
                    ];
                });

            Log::info('Lấy chi tiết booking thành công', ['booking_id' => $bookingId]);
            return response()->json($bookingDetails, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'booking_id' => $bookingId
            ]);
            return response()->json([
                'error' => 'Không thể lấy chi tiết booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy dịch vụ của booking
     */
    public function getBookingServices($bookingId)
    {
        try {
            $services = BookingHotelService::whereHas('bookingDetail', function ($query) use ($bookingId) {
                $query->where('booking_id', $bookingId);
            })->with('service')->get();

            Log::info('Lấy dịch vụ booking thành công', ['booking_id' => $bookingId]);
            return response()->json($services, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy dịch vụ booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'booking_id' => $bookingId
            ]);
            return response()->json([
                'error' => 'Không thể lấy dịch vụ booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gán phòng cho chi tiết booking
     */
    public function assignRoom($bookingDetailId, Request $request)
    {
        try {
            $request->validate([
                'room_id' => 'required|exists:rooms,room_id'
            ]);

            $bookingDetail = BookingHotelDetail::findOrFail($bookingDetailId);
            $room = Room::where('room_id', $request->room_id)
                ->where('type_id', $bookingDetail->room_type)
                ->where('status', 'available')
                ->first();

            if (!$room) {
                Log::warning('Phòng không hợp lệ hoặc đã được gán', [
                    'booking_detail_id' => $bookingDetailId,
                    'room_id' => $request->room_id,
                    'room_type' => $bookingDetail->room_type
                ]);
                return response()->json([
                    'error' => 'Phòng không hợp lệ, không thuộc loại phòng hoặc đã được gán'
                ], 400);
            }

            $bookingDetail->room_id = $request->room_id;
            $bookingDetail->save();

            $room->status = 'occupied';
            $room->save();

            $booking = BookingHotel::findOrFail($bookingDetail->booking_id);
            $unassignedRooms = BookingHotelDetail::where('booking_id', $booking->booking_id)
                ->whereNull('room_id')
                ->count();

            if ($unassignedRooms === 0) {
                $booking->status = 'confirmed';
                $booking->save();
                Log::info('Tự động xác nhận booking vì tất cả phòng đã được gán', [
                    'booking_id' => $booking->booking_id
                ]);
            }

            Log::info('Xếp phòng thành công', [
                'booking_detail_id' => $bookingDetailId,
                'room_id' => $request->room_id
            ]);
            return response()->json(['message' => 'Xếp phòng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xếp phòng: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'booking_detail_id' => $bookingDetailId
            ]);
            return response()->json([
                'error' => 'Không thể xếp phòng',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xác nhận booking
     */
    public function confirmBooking($bookingId)
    {
        try {
            $booking = BookingHotel::findOrFail($bookingId);
            $booking->status = 'confirmed';
            $booking->save();

            Log::info('Xác nhận booking thành công', ['booking_id' => $bookingId]);
            return response()->json(['message' => 'Xác nhận booking thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xác nhận booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'booking_id' => $bookingId
            ]);
            return response()->json([
                'error' => 'Không thể xác nhận booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hoàn thành booking
     */
    public function completeBooking(Request $request, $bookingId)
    {
        try {
            $booking = BookingHotel::findOrFail($bookingId);
            if ($booking->status !== 'confirmed') {
                Log::warning('Không thể hoàn thành booking vì trạng thái không phải confirmed', [
                    'booking_id' => $bookingId,
                    'current_status' => $booking->status
                ]);
                return response()->json([
                    'error' => 'Chỉ có thể hoàn thành booking ở trạng thái confirmed'
                ], 400);
            }

            // Lấy service_ids từ request (nếu có)
            $serviceIds = $request->input('service_ids', []);

            // Lấy chi tiết booking
            $bookingDetails = BookingHotelDetail::where('booking_id', $bookingId)
                ->whereNotNull('room_id')
                ->get();

            if ($bookingDetails->isEmpty()) {
                Log::warning('Không tìm thấy chi tiết booking hoặc phòng chưa được gán', [
                    'booking_id' => $bookingId
                ]);
                return response()->json([
                    'error' => 'Không tìm thấy chi tiết booking hoặc phòng chưa được gán'
                ], 404);
            }

            $totalPrice = 0;
            $totalServiceFee = 0;
            $note = '';

            foreach ($bookingDetails as $detail) {
                $room = Room::find($detail->room_id);
                if (!$room || !$room->type_id) {
                    Log::warning('Phòng không hợp lệ hoặc thiếu type_id', [
                        'room_id' => $detail->room_id,
                        'booking_id' => $bookingId
                    ]);
                    return response()->json([
                        'error' => "Phòng không hợp lệ hoặc thiếu type_id cho room_id: {$detail->room_id}"
                    ], 400);
                }

                // Lấy giá phòng từ bảng prices
                $now = Carbon::now();
                $price = DB::table('prices')
                    ->where('type_id', $room->type_id)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->orderByDesc('priority')
                    ->first();

                if (!$price) {
                    Log::warning('Không tìm thấy bảng giá phù hợp', [
                        'type_id' => $room->type_id,
                        'booking_id' => $bookingId
                    ]);
                    return response()->json([
                        'error' => 'Không tìm thấy bảng giá phù hợp'
                    ], 400);
                }

                // Tính giá phòng dựa trên thời gian thực tế
                $checkIn = Carbon::parse($booking->check_in_date);
                $actualCheckout = Carbon::now();
                $totalHours = $checkIn->floatDiffInHours($actualCheckout);
                $roomPrice = 0;

                if ($totalHours <= 2) {
                    $roomPrice = 2 * $price->hourly_price;
                    $note .= "Phòng {$room->room_name}: Khách trả trong 2 giờ đầu. Tính phí cố định 2 giờ. ";
                } elseif ($totalHours <= 6) {
                    $hours = ceil($totalHours);
                    $roomPrice = $hours * $price->hourly_price;
                    $note .= "Phòng {$room->room_name}: Tính theo giờ ($hours giờ). ";
                } else {
                    $fullDays = floor($totalHours / 24);
                    $remainingHours = $totalHours - ($fullDays * 24);
                    if ($remainingHours > 6) {
                        $fullDays += 1;
                        $remainingHours = 0;
                    }
                    $roomPrice = ($fullDays * $price->price_per_night) + (ceil($remainingHours) * $price->hourly_price);
                    $note .= "Phòng {$room->room_name}: Tính theo ngày ($fullDays ngày, $remainingHours giờ). ";
                }

                // Tính giá dịch vụ (nếu có)
                $serviceFee = 0;
                if (!empty($serviceIds)) {
                    $serviceFee = DB::table('services')
                        ->whereIn('service_id', $serviceIds)
                        ->sum('price');

                    // Gán dịch vụ vào booking_hotel_service
                    foreach ($serviceIds as $serviceId) {
                        DB::table('booking_hotel_service')->insert([
                            'booking_detail_id' => $detail->booking_detail_id,
                            'service_id' => $serviceId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }

                // Cập nhật chi tiết booking
                $detail->gia_phong = number_format($roomPrice, 2, '.', '');
                $detail->gia_dich_vu = number_format($serviceFee, 2, '.', '');
                $detail->total_price = number_format($roomPrice + $serviceFee, 2, '.', '');
                $detail->save();

                // Cộng vào tổng giá
                $totalPrice += ($roomPrice + $serviceFee);
                $totalServiceFee += $serviceFee;

                // Cập nhật trạng thái phòng về available
                $room->status = 'available';
                $room->save();
                Log::info('Cập nhật trạng thái phòng về available', [
                    'room_id' => $room->room_id,
                    'booking_id' => $bookingId
                ]);
            }

            // Tính chênh lệch giá so với dự kiến
            $expectedCheckout = Carbon::parse($booking->check_out_date);
            $expectedHours = $checkIn->floatDiffInHours($expectedCheckout);
            $expectedTotal = 0;

            foreach ($bookingDetails as $detail) {
                $room = Room::find($detail->room_id);
                $price = DB::table('prices')
                    ->where('type_id', $room->type_id)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->orderByDesc('priority')
                    ->first();

                if ($expectedHours <= 6) {
                    $expectedTotal += ceil($expectedHours) * $price->hourly_price;
                } else {
                    $fullDays = floor($expectedHours / 24);
                    $remainingHours = $expectedHours - ($fullDays * 24);
                    if ($remainingHours > 6) {
                        $fullDays += 1;
                        $remainingHours = 0;
                    }
                    $expectedTotal += ($fullDays * $price->price_per_night) + (ceil($remainingHours) * $price->hourly_price);
                }
            }

            $difference = $expectedTotal - ($totalPrice - $totalServiceFee);
            if ($difference > 0) {
                $note .= "Khách trả sớm, hoàn lại " . number_format($difference, 0, ',', '.') . " VND.";
            } elseif ($difference < 0) {
                $note .= "Khách ở quá giờ, phụ thu thêm " . number_format(abs($difference), 0, ',', '.') . " VND.";
            } else {
                $note .= "Thanh toán đúng như dự kiến.";
            }

            // Cập nhật booking
            $booking->total_price = number_format($totalPrice, 2, '.', '');
            $booking->status = 'completed';
            $booking->payment_status = 'completed';
            $booking->note = $note;
            $booking->actual_check_out_time = $now;
            $booking->save();

            Log::info('Hoàn thành booking thành công', [
                'booking_id' => $bookingId,
                'total_price' => $totalPrice,
                'service_total' => $totalServiceFee,
                'note' => $note
            ]);

            return response()->json([
                'message' => 'Hoàn thành booking thành công',
                'actual_total' => $totalPrice,
                'room_total' => $totalPrice - $totalServiceFee,
                'service_total' => $totalServiceFee,
                'note' => $note
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi hoàn thành booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'booking_id' => $bookingId
            ]);
            return response()->json([
                'error' => 'Không thể hoàn thành booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}