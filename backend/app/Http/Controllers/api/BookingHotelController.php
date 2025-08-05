<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingHotel;
use App\Models\BookingHotelDetail;
use App\Models\BookingHotelService;
use App\Models\CancelBooking;
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
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BookingHotelController extends Controller
{
    /**
     * Kiểm tra số lượng phòng trống theo loại phòng trong khoảng thời gian
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $checkInDate = $request->input('check_in_date');
        $checkOutDate = $request->input('check_out_date');

        try {
            $roomTypes = RoomType::all();
            $availability = [];

            foreach ($roomTypes as $roomType) {
                $rooms = Room::where('type_id', $roomType->type_id)->get();

                $bookedRoomIds = BookingHotelDetail::whereHas('booking', function ($query) use ($checkInDate, $checkOutDate) {
                    $query->whereIn('status', ['pending_confirmation', 'confirmed_not_assigned', 'confirmed'])
                        ->where(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                                ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                                ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                                    $q->where('check_in_date', '<=', $checkInDate)
                                        ->where('check_out_date', '>=', $checkOutDate);
                                });
                        });
                })->pluck('room_id');

                $availableRoomsCount = $rooms->whereNotIn('room_id', $bookedRoomIds)->count();

                $availability[] = [
                    'room_type' => $roomType->type_id,
                    'type_name' => $roomType->type_name,
                    'available_rooms' => $availableRoomsCount
                ];
            }

            Log::info('Kiểm tra phòng trống thành công', [
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'availability' => $availability
            ]);

            return response()->json($availability, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra phòng trống: ' . $e->getMessage(), [
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Không thể kiểm tra phòng trống',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách phòng trống dựa trên room_type và khoảng thời gian
     */
    public function getAvailableRooms(Request $request)
    {
        $request->validate([
            'room_type' => 'required|integer|exists:room_types,type_id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $roomTypeId = $request->input('room_type');
        $checkInDate = $request->input('check_in_date');
        $checkOutDate = $request->input('check_out_date');

        try {
            // Lấy danh sách room_id đã được gán trong khoảng thời gian yêu cầu
            $occupiedRoomIds = BookingHotelDetail::whereHas('booking', function ($query) use ($checkInDate, $checkOutDate) {
                $query->whereIn('status', ['pending_confirmation', 'confirmed_not_assigned', 'confirmed'])
                    ->where(function ($q) use ($checkInDate, $checkOutDate) {
                        $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                            ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                            ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                                $q->where('check_in_date', '<=', $checkInDate)
                                    ->where('check_out_date', '>=', $checkOutDate);
                            });
                    });
            })->whereNotNull('room_id')
                ->pluck('room_id');

            // Lấy danh sách phòng trống thuộc room_type
            $availableRooms = Room::where('type_id', $roomTypeId)
                ->whereNotIn('room_id', $occupiedRoomIds)
                ->with('roomType')
                ->get()
                ->map(function ($room) {
                    return [
                        'room_id' => $room->room_id,
                        'room_name' => $room->room_name,
                        'type_name' => $room->roomType->type_name ?? 'N/A',
                        'status' => $room->status, // Trả về trạng thái phòng để debug
                    ];
                });

            Log::info('Lấy danh sách phòng trống thành công', [
                'room_type' => $roomTypeId,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'available_rooms_count' => $availableRooms->count(),
                'occupied_room_ids' => $occupiedRoomIds->toArray(),
            ]);

            return response()->json($availableRooms, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách phòng trống: ' . $e->getMessage(), [
                'room_type' => $roomTypeId,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Không thể lấy danh sách phòng trống',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gán phòng cho chi tiết booking
     */
    public function assignRoom($bookingDetailId, Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,room_id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        try {
            $bookingDetail = BookingHotelDetail::findOrFail($bookingDetailId);
            $checkInDate = $request->input('check_in_date');
            $checkOutDate = $request->input('check_out_date');

            $booking = BookingHotel::findOrFail($bookingDetail->booking_id);
            // Cho phép xếp phòng khi đang chờ xác nhận hoặc đã xác nhận nhưng chưa xếp đủ
            if (!in_array($booking->status, ['pending_confirmation', 'confirmed_not_assigned'])) {
                Log::warning('Không thể xếp phòng vì booking không ở trạng thái phù hợp', [
                    'booking_detail_id' => $bookingDetailId,
                    'booking_id' => $booking->booking_id,
                    'status' => $booking->status
                ]);
                return response()->json([
                    'error' => 'Chỉ có thể xếp phòng khi booking đang chờ xác nhận hoặc đã xác nhận chưa xếp phòng'
                ], 400);
            }

            // Kiểm tra phòng có trống không
            $isRoomOccupied = BookingHotelDetail::where('room_id', $request->room_id)
                ->where('booking_detail_id', '!=', $bookingDetailId) // Loại trừ chính nó ra
                ->whereHas('booking', function ($query) use ($checkInDate, $checkOutDate) {
                    $query->whereIn('status', ['pending_confirmation', 'confirmed_not_assigned', 'confirmed'])
                        ->where(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->where(function ($subq) use ($checkInDate, $checkOutDate) {
                                $subq->where('check_in_date', '<', $checkOutDate)
                                     ->where('check_out_date', '>', $checkInDate);
                            });
                        });
                })->exists();


            if ($isRoomOccupied) {
                Log::warning('Phòng đã được xếp cho booking khác trong khoảng thời gian này', [
                    'room_id' => $request->room_id,
                    'check_in_date' => $checkInDate,
                    'check_out_date' => $checkOutDate
                ]);
                return response()->json([
                    'error' => 'Phòng đã được xếp cho booking khác trong khoảng thời gian này'
                ], 400);
            }

            $bookingDetail->room_id = $request->room_id;
            $bookingDetail->save();

            Log::info('Xếp phòng thành công', [
                'booking_detail_id' => $bookingDetailId,
                'room_id' => $request->room_id,
            ]);

            // Trả về chỉ thông tin cần thiết, không bao gồm booking_status nữa
            return response()->json([
                'message' => 'Xếp phòng thành công',
                'booking_detail' => $bookingDetail
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xếp phòng: ' . $e->getMessage(), [
                'booking_detail_id' => $bookingDetailId,
                'room_id' => $request->room_id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Không thể xếp phòng',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo token JWT cho khách hàng
     */
    public function generateToken(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'phone' => 'required',
            ]);

            $customer = Customer::firstOrCreate(['customer_phone' => $request->phone], [
                'customer_name' => $request->name,
                'customer_phone' => $request->phone,
                'address' => $request->address ?? 'Unknown',
            ]);

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
     * Tạo mới.booking
     */
    public function storeBooking(Request $request)
    {
        try {
            $bookingDetails = $request->validate([
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date',
                'check_in_time' => 'required',
                'check_out_time' => 'required',
                'total_rooms' => 'required|integer',
                'roomDetails' => 'required|array',
                'total_price' => 'required|numeric',
                'payment_method' => 'required',
                'booking_type' => 'required|string',
                'adult' => 'required|integer',
                'child' => 'required|integer',
                'note' => 'nullable|string',
                'orderCode' => 'nullable',
            ]);

            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

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

            $customerId = $user->customer_id;

            $booking = BookingHotel::create([
                'customer_id' => $customerId,
                'payment_method' => $bookingDetails['payment_method'],
                'booking_type' => $bookingDetails['booking_type'],
                'adult' => $bookingDetails['adult'],
                'child' => $bookingDetails['child'],
                'orderCode' => $bookingDetails['orderCode'],
                'check_in_date' => $bookingDetails['check_in_date'],
                'check_out_date' => $bookingDetails['check_out_date'],
                'check_in_time' => $bookingDetails['check_in_time'],
                'check_out_time' => $bookingDetails['check_out_time'],
                'total_rooms' => $bookingDetails['total_rooms'],
                'total_price' => $bookingDetails['total_price'],
                'payment_status' => 'pending',
                'status' => 'pending_confirmation',
                'note' => $bookingDetails['note'],
            ]);

            foreach ($bookingDetails['roomDetails'] as $roomDetail) {
                $bookingDetail = BookingHotelDetail::create([
                    'booking_id' => $booking->booking_id,
                    'room_type' => (int)$roomDetail['id'],
                    'gia_phong' => number_format($roomDetail['price'], 2, '.', ''),
                    'gia_dich_vu' => number_format($roomDetail['totalServiceCost'], 2, '.', ''),
                    'total_price' => number_format($roomDetail['totalServiceCost'] + $roomDetail['price'], 2, '.', ''),
                    'note' => $bookingDetails['note'],
                ]);

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
    public function deleteBookingHistory(Request $request, $id)
    {
        // Log::info('Đã vào controller!');
        $sub = JWTAuth::parseToken()->getPayload()->get('sub');
        $booking = BookingHotel::where('booking_id', $id)->where('customer_id', $sub)->first();

        if (!$booking) {
            return response()->json(['message' => 'Đơn đặt phòng không tồn tại'], 404);
        }

        // if ($booking->status !== 'pending_confirmation') {
        //     return response()->json(['message' => 'Chỉ có thể hủy đơn cho xác nhận'], 400);
        // }
        if ($booking->status == 'cancelled') {
            return response()->json(['message' => 'Don Da Huy'], 400);
        }

        $now = Carbon::today();
        $checkInDate = Carbon::parse($booking->check_in_date);

        if ($checkInDate->isToday()) {
            return response()->json(['message' => 'Không thể hủy đặt phòng cho ngày hiện tại'], 400);
        }

        // Tính toán số tiền hoàn lại
        $refundAmount = 0;
        if ($booking->payment_method === 'thanh_toan_qr' && $booking->payment_status === 'completed') {
            $daysBeforeCheckIn = $now->diffInDays($checkInDate);
            if ($daysBeforeCheckIn >= 7) $refundAmount = $booking->total_price;
            elseif ($daysBeforeCheckIn >= 5) $refundAmount = $booking->total_price * 0.7;
            elseif ($daysBeforeCheckIn >= 1) $refundAmount = $booking->total_price * 0.5;
        }

        $booking->status = 'pending_cancel';
        $booking->save();

        $cancel = CancelBooking::create([
            'booking_id' => $booking->booking_id,
            'customer_id' => $sub,
            'cancellation_reason' => $request->input('cancellation_reason', 'Không có lý do cụ thể'),
            'refund_amount' => $refundAmount,
            'status' => 'requested',
        ]);

        Log::info('Tạo yêu cầu hủy thành công', ['cancel_id' => $cancel->cancel_id, 'booking_id' => $booking->booking_id]);

        return response()->json([
            'message' => 'Yêu cầu hủy đơn đặt phòng đã được gửi, đang chờ xác nhận',
            'refund_amount' => $refundAmount
        ], 200);
    }

    public function confirmCancelBooking(Request $request, $cancel_id)
    {
        try {
            $cancel = CancelBooking::where('cancel_id', $cancel_id)->first();
            if (!$cancel) {
                return response()->json(['message' => 'Không tìm thấy yêu cầu hủy'], 404);
            }

            if ($cancel->status !== 'requested') {
                return response()->json(['message' => 'Yêu cầu hủy không thể xác nhận vì đã được xử lý'], 400);
            }

            $booking = BookingHotel::where('booking_id', $cancel->booking_id)->first();
            if (!$booking) {
                return response()->json(['message' => 'Đơn đặt phòng không tồn tại'], 404);
            }

            $newStatus = $request->input('status', 'processed');
            if (!in_array($newStatus, ['processed', 'failed'])) {
                return response()->json(['message' => 'Trạng thái không hợp lệ'], 400);
            }

            $cancel->status = $newStatus;
            $cancel->refund_bank = $request->input('refund_bank');
            $cancel->refund_account_number = $request->input('refund_account_number');
            $cancel->refund_account_name = $request->input('refund_account_name');
            $cancel->save();

            $booking->status = 'cancelled';
            $booking->save();

            Log::info('Xác nhận hủy thành công', ['cancel_id' => $cancel_id, 'booking_id' => $cancel->booking_id]);

            return response()->json([
                'message' => 'Xác nhận hủy đặt phòng thành công',
                'data' => $cancel
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xác nhận hủy: ' . $e->getMessage(), [
                'cancel_id' => $cancel_id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Lỗi server, vui lòng thử lại sau'], 500);
        }
    }

    public function getCancelInfo($booking_id)
    {
        try {
            $cancel = CancelBooking::where('booking_id', $booking_id)->first();
            if (!$cancel) {
                Log::warning('Không tìm thấy bản ghi hủy cho booking_id', ['booking_id' => $booking_id]);
                return response()->json(['message' => 'Không tìm thấy thông tin hủy cho đặt phòng này'], 404);
            }

            // Kiểm tra trạng thái hợp lệ
            $validStatuses = ['requested', 'processed', 'failed'];
            if (!in_array($cancel->status, $validStatuses)) {
                Log::warning('Trạng thái không hợp lệ', ['booking_id' => $booking_id, 'status' => $cancel->status]);
                return response()->json(['message' => 'Trạng thái hủy không hợp lệ'], 400);
            }

            $data = [
                'cancel_id' => $cancel->cancel_id,
                'booking_id' => $cancel->booking_id,
                'customer_id' => $cancel->customer_id,
                'reason' => $cancel->cancellation_reason,
                'cancellation_date' => $cancel->cancellation_date,
                'refund_amount' => $cancel->refund_amount,
                'status' => $cancel->status,
                'refund_bank' => $cancel->refund_bank,
                'refund_account_number' => $cancel->refund_account_number,
                'refund_account_name' => $cancel->refund_account_name,
                'created_at' => $cancel->created_at,
                'updated_at' => $cancel->updated_at,
            ];

            Log::info('Lấy thông tin hủy thành công', ['booking_id' => $booking_id]);
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thông tin hủy: ' . $e->getMessage(), [
                'booking_id' => $booking_id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Lỗi server, vui lòng thử lại sau'], 500);
        }
    }

    public function showBookingCancel($bookingId)
    {
        try {
            $cancel = CancelBooking::where('booking_id', $bookingId)->first();
            if (!$cancel) {
                Log::warning('Không tìm thấy bản ghi hủy cho booking_id', ['booking_id' => $bookingId]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin hủy đặt phòng.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'cancel_id' => $cancel->cancel_id,
                    'booking_id' => $cancel->booking_id,
                    'customer_id' => $cancel->customer_id,
                    'reason' => $cancel->cancellation_reason,
                    'refund_amount' => $cancel->refund_amount,
                    'refund_bank' => $cancel->refund_bank,
                    'refund_account_number' => $cancel->refund_account_number,
                    'refund_account_name' => $cancel->refund_account_name,
                    'cancellation_date' => $cancel->cancellation_date,
                    'status' => $cancel->status,
                    'created_at' => $cancel->created_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thông tin hủy: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi server, vui lòng thử lại sau',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function updateBankInfo(Request $request, $bookingId)
    {
        $request->validate([
            'refund_bank' => 'required|string|max:100',
            'refund_account_number' => 'required|string|max:100',
            'refund_account_name' => 'required|string|max:150'
        ]);

        $cancel = CancelBooking::where('booking_id', $bookingId)->first();

        if (!$cancel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hủy.'
            ], 404);
        }

        $cancel->update([
            'refund_bank' => $request->refund_bank,
            'refund_account_number' => $request->refund_account_number,
            'refund_account_name' => $request->refund_account_name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thông tin ngân hàng thành công.',
            'data' => [
                'refund_bank' => $cancel->refund_bank,
                'refund_account_number' => $cancel->refund_account_number,
                'refund_account_name' => $cancel->refund_account_name,
            ]
        ]);
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
        $latestBooking = BookingHotel::where('payment_method', 'thanh_toan_qr')
            ->orderBy('created_at', 'desc')
            ->first();

        $bookingId = $latestBooking ? $latestBooking->booking_id : null;

        $data = [
            "orderCode" => intval(substr(strval(microtime(true) * 10000), -6)),
            "amount" => $amount,
            "description" => "Booking HXH",
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
     * Lấy danh sách booking với phân trang và bộ lọc
     */
    public function getBookings(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 5);
            $page = $request->query('page', 1);
            $sortBy = $request->query('sort_by', 'booking_id');
            $sortOrder = $request->query('sort_order', 'desc');
            $search = $request->query('search');
            $status = $request->query('status');
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');

            // Xây dựng truy vấn
            $query = BookingHotel::select([
                'booking_hotel.booking_id',
                'booking_hotel.customer_id',
                'booking_hotel.check_in_date',
                'booking_hotel.check_in_time',
                'booking_hotel.check_out_date',
                'booking_hotel.check_out_time', 
                'booking_hotel.total_price',
                'booking_hotel.status',
                'booking_hotel.payment_status',
                'booking_hotel.payment_method',
                'booking_hotel.orderCode',
                'booking_hotel.booking_type',
                'booking_hotel.note'
            ])
                ->leftJoin('customers', 'booking_hotel.customer_id', '=', 'customers.customer_id')
                ->with([
                    'customer' => function ($query) {
                        $query->select('customer_id', 'customer_name', 'customer_phone', 'customer_email');
                    },
                    'details' => function ($query) {
                        $query->select('booking_hotel_detail.booking_detail_id', 'booking_hotel_detail.booking_id', 'booking_hotel_detail.room_type', 'booking_hotel_detail.total_price', 'booking_hotel_detail.room_id')
                            ->with(['roomType' => function ($q) {
                                $q->select('type_id', 'type_name');
                            }]);
                    }
                ]);

            // Áp dụng bộ lọc trạng thái
            if ($status) {
                $statuses = is_array($status) ? $status : [$status];
                $query->whereIn('booking_hotel.status', $statuses);
            } else {
                $query->whereIn('booking_hotel.status', ['pending_confirmation', 'confirmed', 'pending_cancel', 'cancelled', 'confirmed_not_assigned']);
            }

            // Áp dụng tìm kiếm
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('booking_hotel.booking_id', 'like', "%$search%")
                        ->orWhere('customers.customer_name', 'like', "%$search%")
                        ->orWhere('customers.customer_phone', 'like', "%$search%");
                });
            }

            // Áp dụng bộ lọc ngày
            if ($fromDate) {
                $query->where('booking_hotel.check_in_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->where('booking_hotel.check_in_date', '<=', $toDate . ' 23:59:59');
            }

            // Sắp xếp
            $allowedSortColumns = ['booking_id', 'check_in_date', 'check_out_date'];
            $sortColumn = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'booking_id';
            $sortDirection = $sortOrder === 'asc' ? 'asc' : 'desc';
            $query->orderBy("booking_hotel.$sortColumn", $sortDirection);

            // Phân trang
            $bookings = $query->paginate($perPage, ['*'], 'page', $page);

            // Xử lý trạng thái thanh toán
            $bookings->getCollection()->transform(function ($booking) {
                $booking->details->each(function ($detail) {
                    $detail->type_name = $detail->roomType ? $detail->roomType->type_name : 'N/A';
                });

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

                // Thêm định dạng thời gian trả phòng
                $booking->check_out_datetime = $booking->check_out_date && $booking->check_out_time 
                    ? Carbon::createFromFormat('Y-m-d H:i:s', $booking->check_out_date . ' ' . $booking->check_out_time)->format('d/m/Y H:i')
                    : ($booking->check_out_date ? Carbon::createFromFormat('Y-m-d', $booking->check_out_date)->format('d/m/Y') : 'Chưa xác định');

                return $booking;
            });

            return response()->json([
                'data' => $bookings->items(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'params' => $request->all()
            ]);
            return response()->json([
                'error' => 'Không thể lấy danh sách booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Định dạng trạng thái thanh toán thành tiếng Việt
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
            // Kiểm tra booking_id tồn tại trước
            $bookingExists = BookingHotel::where('booking_id', $bookingId)->exists();
            if (!$bookingExists) {
                Log::warning('Không tìm thấy booking', ['booking_id' => $bookingId]);
                return response()->json(['message' => 'Booking không tồn tại'], 404);
            }

            $bookingDetails = BookingHotelDetail::where('booking_id', $bookingId)
                ->with([
                    'room' => function ($query) {
                        $query->select('room_id', 'room_name', 'status');
                    },
                    'booking.customer' => function ($query) {
                        $query->select('customer_id', 'customer_name', 'customer_phone', 'customer_email');
                    },
                    'roomType' => function ($query) {
                        $query->select('type_id', 'type_name');
                    }
                ])
                ->select([
                    'booking_detail_id',
                    'booking_id',
                    'room_type',
                    'room_id',
                    'gia_phong',
                    'gia_dich_vu',
                    'total_price',
                    'note'
                ])
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
                        ] : ['room_id' => null, 'room_name' => 'Chưa xếp', 'status' => null],
                        'roomType' => $detail->roomType ? [
                            'type_id' => $detail->roomType->type_id,
                            'type_name' => $detail->roomType->type_name,
                        ] : ['type_id' => null, 'type_name' => 'N/A'],
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

            if ($bookingDetails->isEmpty()) {
                Log::info('Không tìm thấy chi tiết booking', ['booking_id' => $bookingId]);
                return response()->json([], 200);
            }

            Log::info('Lấy chi tiết booking thành công', ['booking_id' => $bookingId, 'count' => $bookingDetails->count()]);
            return response()->json($bookingDetails, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết booking: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString(),
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
     * Xác nhận booking
     */
    public function confirmBooking(Request $request, $bookingId)
    {
        try {
            $booking = BookingHotel::findOrFail($bookingId);
            if ($booking->status !== 'pending_confirmation') {
                Log::warning('Không thể xác nhận booking vì không ở trạng thái pending_confirmation', [
                    'booking_id' => $bookingId,
                    'status' => $booking->status
                ]);
                return response()->json([
                    'error' => 'Chỉ có thể xác nhận booking đang chờ xác nhận'
                ], 400);
            }

            $booking->status = 'confirmed_not_assigned';
            $booking->save();

            Log::info('Xác nhận đặt phòng thành công', [
                'booking_id' => $bookingId,
                'new_status' => 'confirmed_not_assigned'
            ]);

            return response()->json([
                'message' => 'Xác nhận đặt phòng thành công',
                'booking' => $booking
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xác nhận đặt phòng: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Không thể xác nhận đặt phòng',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hoàn tất xác nhận booking
     */
    public function completeBooking(Request $request, $bookingId)
    {
        try {
            $booking = BookingHotel::findOrFail($bookingId);
            if ($booking->status !== 'confirmed_not_assigned') {
                Log::warning('Không thể hoàn tất xác nhận vì booking không ở trạng thái confirmed_not_assigned', [
                    'booking_id' => $bookingId,
                    'status' => $booking->status
                ]);
                return response()->json([
                    'error' => 'Chỉ có thể hoàn tất xác nhận booking đã xác nhận chưa xếp phòng'
                ], 400);
            }

            // Kiểm tra nếu tất cả chi tiết booking đã được xếp phòng
            $allDetailsAssigned = BookingHotelDetail::where('booking_id', $booking->booking_id)
                ->whereNull('room_id')
                ->doesntExist();

            if (!$allDetailsAssigned) {
                Log::warning('Không thể hoàn tất xác nhận vì còn phòng chưa được xếp', [
                    'booking_id' => $bookingId
                ]);
                return response()->json([
                    'error' => 'Vui lòng xếp tất cả các phòng trước khi hoàn tất xác nhận'
                ], 400);
            }

            $booking->status = 'confirmed';
            $booking->save();

            Log::info('Hoàn tất xác nhận booking thành công', [
                'booking_id' => $bookingId,
                'new_status' => 'confirmed'
            ]);

            return response()->json([
                'message' => 'Hoàn tất xác nhận thành công',
                'booking' => $booking
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi hoàn tất xác nhận booking: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Không thể hoàn tất xác nhận booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy booking bởi Admin
     */
    public function cancelBookingByAdmin(Request $request, $bookingId)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $booking = BookingHotel::findOrFail($bookingId);

            // Chỉ cho phép admin hủy các đơn đang chờ hoặc đã xác nhận nhưng chưa xếp phòng
            if (!in_array($booking->status, ['pending_confirmation', 'confirmed_not_assigned'])) {
                return response()->json([
                    'error' => 'Chỉ có thể hủy các đơn đang chờ xác nhận hoặc đã xác nhận chưa xếp phòng.'
                ], 400);
            }

            $booking->status = 'cancelled';
            $booking->save();

            CancelBooking::create([
                'booking_id' => $booking->booking_id,
                'customer_id' => $booking->customer_id,
                'cancellation_reason' => 'Hủy bởi Admin: ' . $request->input('reason'),
                'refund_amount' => 0, 
                'status' => 'processed', 
            ]);
            
            DB::commit();

            Log::info('Admin đã hủy đặt phòng thành công', [
                'booking_id' => $bookingId,
                'reason' => $request->input('reason')
            ]);

            return response()->json([
                'message' => 'Hủy đặt phòng thành công!',
                'booking' => $booking // Trả về booking đã cập nhật
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi Admin hủy đặt phòng: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Không thể hủy đặt phòng, đã có lỗi xảy ra.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
