<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\BookingStatusUpdated;
use App\Models\BookingHotel;
use App\Models\BookingHotelDetail;
use App\Models\BookingHotelService;
use App\Models\CancelBooking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Models\Notification;
use App\Events\NewNotification;
use Illuminate\Support\Str;
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
use Illuminate\Support\Facades\Mail;

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
                        'status' => $room->status,
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
                ->where('booking_detail_id', '!=', $bookingDetailId)
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
                'email' => 'required',
            ]);

            $customer = Customer::firstOrCreate(['customer_phone' => $request->phone], [
                'customer_name' => $request->name,
                'customer_phone' => $request->phone,
                'customer_email' => $request->email,
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
     * Tạo mới booking
     */
    public function storeBooking(Request $request)
    {
        DB::beginTransaction();
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

            // Gửi email thông báo tạo booking
            $customer = Customer::find($customerId);
            if ($customer && $customer->customer_email) {
                $additionalInfo = [
                    'total_rooms' => $bookingDetails['total_rooms'],
                    'booking_type' => $bookingDetails['booking_type'],
                ];
                Mail::to($customer->customer_email)->send(new BookingStatusUpdated($booking, 'pending_confirmation', $additionalInfo));
                Log::info('Đã đẩy email thông báo tạo booking vào send', [
                    'booking_id' => $booking->booking_id,
                    'customer_email' => $customer->customer_email
                ]);
            } else {
                Log::warning('Không thể gửi email vì khách hàng không có email', [
                    'booking_id' => $booking->booking_id,
                    'customer_id' => $customerId
                ]);
            }

            $adminsAndStaff = User::whereIn('role', ['admin', 'staff'])->get();

            foreach ($adminsAndStaff as $adminUser) {
                $notification = new Notification();
                $notification->id = Str::uuid();
                $notification->type = 'NEW_BOOKING';
                $notification->notifiable_type = User::class;
                $notification->notifiable_id = $adminUser->id;
                $notification->subject_type = BookingHotel::class;
                $notification->subject_id = $booking->booking_id;
                $notification->data = [
                    'message' => "Bạn có một đặt phòng mới từ KH {$customer->customer_name} (Mã: {$booking->booking_id}).",
                    'link' => '/admin/bookings'
                ];
                $notification->save();

                broadcast(new NewNotification($notification))->toOthers();
            }

            DB::commit();

            return response()->json(['message' => 'Booking created successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
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
            Log::error('Lỗi khi lấy lịch sử booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy dữ liệu booking: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getBookingHistoryByPhone(Request $request)
    {
        try {
            $phones = $request->input('phones', []); // mảng phone

            if (empty($phones)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vui lòng truyền số điện thoại',
                ], 400);
            }

            // Lấy tất cả customer_id từ bảng customer có phone nằm trong mảng
            $customerIds = Customer::whereIn('customer_phone', $phones)
                ->pluck('customer_id');

            if ($customerIds->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                    'message' => 'Không tìm thấy khách hàng với số điện thoại này',
                ], 200);
            }

            $bookings = BookingHotel::whereIn('customer_id', $customerIds)
                ->with('roomTypeInfo')
                ->orderBy('booking_id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $bookings,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy lịch sử booking theo phone: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy dữ liệu booking: ' . $e->getMessage(),
            ], 500);
        }
    }
    //lay chi tiet booking theo booking_id
   public function getBookingDetail($bookingID)
{
    try {
        // Lấy tất cả chi tiết phòng trong booking
        $bookingDetails = BookingHotelDetail::where('booking_id', $bookingID)
            ->get()
            ->map(function ($detail) {
                // Nếu gia_dich_vu > 0 thì lấy danh sách dịch vụ
                if ($detail->gia_dich_vu > 0) {
                    $detail->services = BookingHotelService::with('serviceInfo')
                        ->where('booking_detail_id', $detail->booking_detail_id)
                        ->get();
                } else {
                    $detail->services = collect(); // trả về Collection rỗng
                }
                return $detail;
            });

        return response()->json([
            'status' => 'success',
            'data' => $bookingDetails,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không thể lấy chi tiết booking: ' . $e->getMessage(),
        ], 500);
    }
}




    /**
     * Xóa lịch sử booking
     */
    public function deleteBookingHistory(Request $request, $id)
    {
        try {
            $sub = JWTAuth::parseToken()->getPayload()->get('sub');
            $booking = BookingHotel::where('booking_id', $id)->where('customer_id', $sub)->first();

            if (!$booking) {
                Log::warning('Đơn đặt phòng không tồn tại', ['booking_id' => $id, 'customer_id' => $sub]);
                return response()->json(['message' => 'Đơn đặt phòng không tồn tại'], 404);
            }

            if ($booking->status == 'cancelled') {
                Log::warning('Đơn đã hủy, không thể hủy lại', ['booking_id' => $id]);
                return response()->json(['message' => 'Đơn đặt phòng đã được hủy'], 400);
            }

            $now = Carbon::today();
            $checkInDate = Carbon::parse($booking->check_in_date);

            if ($checkInDate->isToday()) {
                Log::warning('Không thể hủy đặt phòng cho ngày hiện tại', ['booking_id' => $id]);
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

            // Gửi email thông báo yêu cầu hủy
            $customer = Customer::find($sub);
            if ($customer && $customer->customer_email) {
                $additionalInfo = [
                    'cancellation_reason' => $request->input('cancellation_reason', 'Không có lý do cụ thể'),
                    'refund_amount' => $refundAmount
                ];
                Mail::to($customer->customer_email)->send(new BookingStatusUpdated($booking, 'pending_cancel', $additionalInfo));
                Log::info('Đã đẩy email thông báo yêu cầu hủy vào send', [
                    'booking_id' => $booking->booking_id,
                    'customer_email' => $customer->customer_email
                ]);
            } else {
                Log::warning('Không thể gửi email vì khách hàng không có email', [
                    'booking_id' => $booking->booking_id,
                    'customer_id' => $sub
                ]);
            }

            Log::info('Tạo yêu cầu hủy thành công', ['cancel_id' => $cancel->cancel_id, 'booking_id' => $booking->booking_id]);

            return response()->json([
                'message' => 'Yêu cầu hủy đơn đặt phòng đã được gửi, đang chờ xác nhận',
                'refund_amount' => $refundAmount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo yêu cầu hủy: ' . $e->getMessage(), [
                'booking_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Lỗi khi gửi yêu cầu hủy',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xác nhận hủy booking
     */
    public function confirmCancelBooking(Request $request, $cancel_id)
    {
        try {
            $cancel = CancelBooking::where('cancel_id', $cancel_id)->first();
            if (!$cancel) {
                Log::warning('Không tìm thấy yêu cầu hủy', ['cancel_id' => $cancel_id]);
                return response()->json([
                    'error' => 'Không tìm thấy yêu cầu hủy',
                    'cancel_id' => $cancel_id
                ], 404);
            }

            if ($cancel->status !== 'requested') {
                Log::warning('Yêu cầu hủy đã được xử lý', [
                    'cancel_id' => $cancel_id,
                    'status' => $cancel->status
                ]);
                return response()->json([
                    'error' => 'Yêu cầu hủy không thể xác nhận vì đã được xử lý',
                    'status' => $cancel->status
                ], 400);
            }

            $booking = BookingHotel::where('booking_id', $cancel->booking_id)->first();
            if (!$booking) {
                Log::warning('Đơn đặt phòng không tồn tại', ['booking_id' => $cancel->booking_id]);
                return response()->json([
                    'error' => 'Đơn đặt phòng không tồn tại',
                    'booking_id' => $cancel->booking_id
                ], 404);
            }

            $newStatus = $request->input('status', 'processed');
            if (!in_array($newStatus, ['processed', 'failed'])) {
                Log::warning('Trạng thái không hợp lệ', ['status' => $newStatus]);
                return response()->json([
                    'error' => 'Trạng thái không hợp lệ',
                    'status' => $newStatus
                ], 400);
            }

            DB::beginTransaction();

            $cancel->status = $newStatus;
            $cancel->refund_bank = $request->input('refund_bank');
            $cancel->refund_account_number = $request->input('refund_account_number');
            $cancel->refund_account_name = $request->input('refund_account_name');
            $cancel->save();

            $booking->status = 'cancelled';
            $booking->save();

            BookingHotelDetail::where('booking_id', $cancel->booking_id)
                ->update(['status' => 'cancelled']);

            $customer = Customer::find($booking->customer_id);
            if ($customer && $customer->customer_email) {
                $additionalInfo = [
                    'cancellation_reason' => $cancel->cancellation_reason,
                    'refund_amount' => $cancel->refund_amount,
                    'refund_bank' => $cancel->refund_bank,
                    'refund_account_number' => $cancel->refund_account_number,
                    'refund_account_name' => $cancel->refund_account_name,
                ];
                Mail::to($customer->customer_email)->send(new BookingStatusUpdated($booking, 'cancelled', $additionalInfo));
                Log::info('Đã đẩy email thông báo xác nhận hủy vào send', [
                    'booking_id' => $booking->booking_id,
                    'customer_email' => $customer->customer_email
                ]);
            } else {
                Log::warning('Không thể gửi email vì khách hàng không có email', [
                    'booking_id' => $booking->booking_id,
                    'customer_id' => $booking->customer_id
                ]);
            }

            DB::commit();

            Log::info('Xác nhận hủy thành công', ['cancel_id' => $cancel_id, 'booking_id' => $cancel->booking_id]);

            return response()->json([
                'message' => 'Xác nhận hủy đặt phòng thành công',
                'data' => [
                    'cancel_id' => $cancel->cancel_id,
                    'booking_id' => $cancel->booking_id,
                    'status' => $cancel->status
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xác nhận hủy: ' . $e->getMessage(), [
                'cancel_id' => $cancel_id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Lỗi server, vui lòng thử lại sau',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thông tin hủy booking
     */
    public function getCancelInfo($booking_id)
    {
        try {
            $cancel = CancelBooking::where('booking_id', $booking_id)->first();
            if (!$cancel) {
                Log::warning('Không tìm thấy bản ghi hủy cho booking_id', ['booking_id' => $booking_id]);
                return response()->json(['message' => 'Không tìm thấy thông tin hủy cho đặt phòng này'], 404);
            }

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

    /**
     * Hiển thị thông tin hủy booking
     */
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

    /**
     * Cập nhật thông tin ngân hàng cho yêu cầu hủy
     */
    public function updateBankInfo(Request $request, $bookingId)
    {
        $request->validate([
            'refund_bank' => 'required|string|max:100',
            'refund_account_number' => 'required|string|max:100',
            'refund_account_name' => 'required|string|max:150'
        ]);

        try {
            $cancel = CancelBooking::where('booking_id', $bookingId)->first();
            if (!$cancel) {
                Log::warning('Không tìm thấy đơn hủy', ['booking_id' => $bookingId]);
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

            Log::info('Cập nhật thông tin ngân hàng thành công', [
                'booking_id' => $bookingId,
                'cancel_id' => $cancel->cancel_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thông tin ngân hàng thành công.',
                'data' => [
                    'refund_bank' => $cancel->refund_bank,
                    'refund_account_number' => $cancel->refund_account_number,
                    'refund_account_name' => $cancel->refund_account_name,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật thông tin ngân hàng: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi cập nhật thông tin ngân hàng',
                'details' => $e->getMessage()
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
                Log::warning('Không thể tạo liên kết thanh toán PayOS', ['response' => $response]);
                return response()->json(['error' => 'Không thể tạo liên kết thanh toán.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi PayOS: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
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
            $activeAt = $request->query('active_at');

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
                'booking_hotel.note',
                'booking_hotel.adult',
                'booking_hotel.child',
                'booking_hotel.total_rooms',
                'booking_hotel.additional_fee',
            ])
                ->leftJoin('customers', 'booking_hotel.customer_id', '=', 'customers.customer_id')
                ->with([
                    'customer' => function ($query) {
                        $query->select('customer_id', 'customer_name', 'customer_phone', 'customer_email', 'customer_id_number');
                    },
                    'details' => function ($query) {
                        $query->select('booking_hotel_detail.booking_detail_id', 'booking_hotel_detail.booking_id', 'booking_hotel_detail.room_type', 'booking_hotel_detail.total_price', 'booking_hotel_detail.room_id', 'booking_hotel_detail.gia_phong', 'booking_hotel_detail.gia_dich_vu')
                            ->with([
                                'roomType' => function ($q) {
                                    $q->select('type_id', 'type_name');
                                },
                                'room' => function ($q) {
                                    $q->select('room_id', 'room_name');
                                }
                            ]);
                    }
                ]);

            if ($status) {
                $statuses = is_array($status) ? $status : [$status];
                $query->whereIn('booking_hotel.status', $statuses);
            } else {
                $query->whereIn('booking_hotel.status', ['pending_confirmation', 'confirmed', 'pending_cancel', 'confirmed_not_assigned']);
            }

            if ($activeAt) {
                try {
                    $filterDateTime = Carbon::parse($activeAt)->format('Y-m-d H:i:s');
                    $query->whereRaw("CONCAT(booking_hotel.check_in_date, ' ', booking_hotel.check_in_time) <= ?", [$filterDateTime]);
                    $query->whereRaw("CONCAT(booking_hotel.check_out_date, ' ', booking_hotel.check_out_time) >= ?", [$filterDateTime]);
                } catch (\Exception $e) {
                    Log::warning('Invalid datetime format for active_at filter: ' . $activeAt);
                }
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('booking_hotel.booking_id', 'like', "%$search%")
                        ->orWhere('booking_hotel.orderCode', 'like', "%$search%")
                        ->orWhere('customers.customer_name', 'like', "%$search%")
                        ->orWhere('customers.customer_phone', 'like', "%$search%")
                        ->orWhere('customers.customer_id_number', 'like', "%$search%");
                });
            }

            if ($fromDate) {
                $query->where('booking_hotel.check_in_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->where('booking_hotel.check_out_date', '<=', $toDate);
            }

            $allowedSortColumns = ['booking_id', 'check_in_date', 'check_out_date', 'total_price'];
            $sortColumn = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'booking_id';
            $sortDirection = $sortOrder === 'asc' ? 'asc' : 'desc';
            $query->orderBy("booking_hotel.$sortColumn", $sortDirection);

            $bookings = $query->paginate($perPage, ['*'], 'page', $page);

            $bookings->getCollection()->transform(function ($booking) {
                $booking->details->each(function ($detail) {
                    $detail->type_name = $detail->roomType ? $detail->roomType->type_name : 'N/A';
                    $detail->room_name = $detail->room ? $detail->room->room_name : 'Chưa xếp';
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
                    $paymentStatusDisplay = $this->formatPaymentStatus($booking->payment_status);
                }
                $booking->payment_status_display = $paymentStatusDisplay;

                $booking->check_out_datetime = $booking->check_out_date && $booking->check_out_time
                    ? Carbon::createFromFormat('Y-m-d H:i:s', $booking->check_out_date . ' ' . $booking->check_out_time)->format('d/m/Y H:i')
                    : ($booking->check_out_date ? Carbon::parse($booking->check_out_date)->format('d/m/Y') : 'Chưa xác định');

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

            $earlierPendingBookings = BookingHotel::where('status', 'pending_confirmation')
                ->where('created_at', '<', $booking->created_at)
                ->where('booking_id', '!=', $bookingId)
                ->where(function ($query) use ($booking) {
                    $query->whereBetween('check_in_date', [$booking->check_in_date, $booking->check_out_date])
                        ->orWhereBetween('check_out_date', [$booking->check_in_date, $booking->check_out_date])
                        ->orWhere(function ($q) use ($booking) {
                            $q->where('check_in_date', '<=', $booking->check_in_date)
                                ->where('check_out_date', '>=', $booking->check_out_date);
                        });
                })
                ->first();

            if ($earlierPendingBookings) {
                Log::warning('Không thể xác nhận booking vì có booking trước đó đang chờ xác nhận', [
                    'booking_id' => $bookingId,
                    'earlier_booking_id' => $earlierPendingBookings->booking_id
                ]);
                return response()->json([
                    'error' => "Không thể xác nhận booking này (#{$bookingId}) vì có booking trước đó (#{$earlierPendingBookings->booking_id}) đang chờ xác nhận. Vui lòng xác nhận booking trước đó trước."
                ], 400);
            }

            $bookingDetails = BookingHotelDetail::where('booking_id', $bookingId)->get();
            $checkInDate = $booking->check_in_date;
            $checkOutDate = $booking->check_out_date;

            $roomTypeCounts = $bookingDetails->groupBy('room_type')->map->count();

            foreach ($roomTypeCounts as $roomTypeId => $requiredRooms) {
                $totalRooms = Room::where('type_id', $roomTypeId)->count();

                $bookedRoomIds = BookingHotelDetail::whereHas('booking', function ($query) use ($checkInDate, $checkOutDate, $bookingId) {
                    $query->where('booking_id', '!=', $bookingId)
                        ->whereIn('status', ['confirmed_not_assigned', 'confirmed'])
                        ->where(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                                ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                                ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                                    $q->where('check_in_date', '<=', $checkInDate)
                                        ->where('check_out_date', '>=', $checkOutDate);
                                });
                        });
                })
                    ->where('room_type', $roomTypeId)
                    ->pluck('room_id')
                    ->filter()
                    ->unique()
                    ->count();

                $confirmedBookings = BookingHotelDetail::whereHas('booking', function ($query) use ($checkInDate, $checkOutDate, $bookingId) {
                    $query->where('booking_id', '!=', $bookingId)
                        ->where('status', 'confirmed_not_assigned')
                        ->where(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                                ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                                ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                                    $q->where('check_in_date', '<=', $checkInDate)
                                        ->where('check_out_date', '>=', $checkOutDate);
                                });
                        });
                })
                    ->where('room_type', $roomTypeId)
                    ->whereNull('room_id')
                    ->count();

                $totalUsedRooms = $bookedRoomIds + $confirmedBookings;
                $availableRooms = $totalRooms - $totalUsedRooms;

                if ($requiredRooms > $availableRooms) {
                    $roomType = RoomType::find($roomTypeId);
                    Log::warning('Không đủ phòng để xác nhận booking', [
                        'booking_id' => $bookingId,
                        'room_type' => $roomTypeId,
                        'required_rooms' => $requiredRooms,
                        'available_rooms' => $availableRooms,
                        'check_in_date' => $checkInDate,
                        'check_out_date' => $checkOutDate
                    ]);
                    return response()->json([
                        'error' => "Không đủ phòng loại '{$roomType->type_name}' trong khoảng thời gian từ " .
                            Carbon::parse($checkInDate)->format('d/m/Y') . " đến " .
                            Carbon::parse($checkOutDate)->format('d/m/Y') . ". Cần {$requiredRooms} phòng nhưng chỉ còn {$availableRooms} phòng trống."
                    ], 400);
                }
            }

            $booking->status = 'confirmed_not_assigned';
            $booking->save();

            $customer = Customer::find($booking->customer_id);
            if ($customer && $customer->customer_email) {
                $additionalInfo = [
                    'check_in_date' => Carbon::parse($booking->check_in_date)->format('d/m/Y'),
                    'check_out_date' => Carbon::parse($booking->check_out_date)->format('d/m/Y'),
                ];
                Mail::to($customer->customer_email)->send(new BookingStatusUpdated($booking, 'confirmed_not_assigned', $additionalInfo));
                Log::info('Đã đẩy email thông báo xác nhận vào send', [
                    'booking_id' => $booking->booking_id,
                    'customer_email' => $customer->customer_email
                ]);
            } else {
                Log::warning('Không thể gửi email vì khách hàng không có email', [
                    'booking_id' => $booking->booking_id,
                    'customer_id' => $booking->customer_id
                ]);
            }

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

            DB::beginTransaction();

            $booking->status = 'confirmed';
            $booking->save();

            $customer = Customer::find($booking->customer_id);
            if ($customer && $customer->customer_email) {
                $bookingDetails = BookingHotelDetail::where('booking_id', $booking->booking_id)
                    ->with('room', 'roomType')
                    ->get();
                $rooms = $bookingDetails->map(function ($detail) {
                    return [
                        'room_name' => $detail->room ? $detail->room->room_name : 'N/A',
                        'room_type' => $detail->roomType ? $detail->roomType->type_name : 'N/A',
                    ];
                })->toArray();
                $additionalInfo = [
                    'rooms' => $rooms,
                    'check_in_date' => Carbon::parse($booking->check_in_date)->format('d/m/Y'),
                    'check_out_date' => Carbon::parse($booking->check_out_date)->format('d/m/Y'),
                ];
                Mail::to($customer->customer_email)->send(new BookingStatusUpdated($booking, 'confirmed', $additionalInfo));
                Log::info('Đã đẩy email thông báo hoàn tất xác nhận vào send', [
                    'booking_id' => $booking->booking_id,
                    'customer_email' => $customer->customer_email
                ]);
            } else {
                Log::warning('Không thể gửi email vì khách hàng không có email', [
                    'booking_id' => $booking->booking_id,
                    'customer_id' => $booking->customer_id
                ]);
            }

            DB::commit();

            Log::info('Hoàn tất xác nhận booking thành công', [
                'booking_id' => $bookingId,
                'new_status' => 'confirmed'
            ]);

            return response()->json([
                'message' => 'Hoàn tất xác nhận thành công',
                'booking' => $booking
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
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
            if (!in_array($booking->status, ['pending_confirmation', 'confirmed_not_assigned', 'confirmed'])) {
                Log::warning('Không thể hủy booking vì trạng thái không hợp lệ', [
                    'booking_id' => $bookingId,
                    'status' => $booking->status
                ]);
                return response()->json([
                    'error' => 'Chỉ có thể hủy các đơn đang chờ xác nhận, đã xác nhận chưa xếp phòng hoặc đã xác nhận.'
                ], 400);
            }

            // Kiểm tra xem booking đã có yêu cầu hủy được xử lý chưa
            $existingCancel = CancelBooking::where('booking_id', $bookingId)
                ->where('status', 'processed')
                ->first();
            if ($existingCancel) {
                Log::warning('Booking đã được hủy trước đó', [
                    'booking_id' => $bookingId,
                    'cancel_id' => $existingCancel->cancel_id
                ]);
                return response()->json([
                    'error' => 'Đơn đặt phòng đã được hủy trước đó.'
                ], 400);
            }

            $booking->status = 'cancelled';
            $booking->save();

            BookingHotelDetail::where('booking_id', $bookingId)
                ->update(['status' => 'cancelled']);

            $cancel = CancelBooking::create([
                'booking_id' => $booking->booking_id,
                'customer_id' => $booking->customer_id,
                'cancellation_reason' => 'Hủy bởi Admin: ' . $request->input('reason'),
                'refund_amount' => 0, // Hoặc tính toán dựa trên chính sách hoàn tiền nếu có
                'status' => 'processed',
            ]);

            $customer = Customer::find($booking->customer_id);
            if ($customer && $customer->customer_email) {
                $additionalInfo = [
                    'reason' => $request->input('reason'),
                    'check_in_date' => $booking->check_in_date ? Carbon::parse($booking->check_in_date)->format('d/m/Y') : 'N/A',
                    'check_out_date' => $booking->check_out_date ? Carbon::parse($booking->check_out_date)->format('d/m/Y') : 'N/A',
                ];
                Mail::to($customer->customer_email)->send(new BookingStatusUpdated($booking, 'cancelled', $additionalInfo));
                Log::info('Đã đẩy email thông báo hủy bởi admin vào send', [
                    'booking_id' => $booking->booking_id,
                    'customer_email' => $customer->customer_email
                ]);
            } else {
                Log::warning('Không thể gửi email vì khách hàng không có email', [
                    'booking_id' => $booking->booking_id,
                    'customer_id' => $booking->customer_id
                ]);
            }

            DB::commit();

            Log::info('Admin đã hủy đặt phòng thành công', [
                'booking_id' => $bookingId,
                'cancel_id' => $cancel->cancel_id,
                'reason' => $request->input('reason'),
            ]);

            return response()->json([
                'message' => 'Hủy đặt phòng thành công!',
                'cancel_id' => $cancel->cancel_id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi Admin hủy đặt phòng: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Không thể hủy đặt phòng, đã có lỗi xảy ra.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xếp phòng ngẫu nhiên cho tất cả chi tiết booking
     */
    public function assignRandomRooms(Request $request, $bookingId)
    {
        $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        try {
            $booking = BookingHotel::findOrFail($bookingId);
            if ($booking->status !== 'confirmed_not_assigned') {
                Log::warning('Không thể xếp phòng vì booking không ở trạng thái confirmed_not_assigned', [
                    'booking_id' => $bookingId,
                    'status' => $booking->status
                ]);
                return response()->json([
                    'error' => 'Chỉ có thể xếp phòng khi booking ở trạng thái đã xác nhận chưa xếp phòng'
                ], 400);
            }

            $checkInDate = $request->input('check_in_date');
            $checkOutDate = $request->input('check_out_date');

            $bookingDetails = BookingHotelDetail::where('booking_id', $bookingId)
                ->whereNull('room_id')
                ->get();

            if ($bookingDetails->isEmpty()) {
                Log::info('Không có chi tiết booking nào cần xếp phòng', [
                    'booking_id' => $bookingId
                ]);
                return response()->json([
                    'message' => 'Tất cả phòng đã được xếp hoặc không có chi tiết booking nào'
                ], 200);
            }

            DB::beginTransaction();

            $assignedRooms = [];
            foreach ($bookingDetails as $bookingDetail) {
                $occupiedRoomIds = BookingHotelDetail::whereHas('booking', function ($query) use ($checkInDate, $checkOutDate) {
                    $query->whereIn('status', ['confirmed_not_assigned', 'confirmed'])
                        ->where(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                                ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                                ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                                    $q->where('check_in_date', '<=', $checkInDate)
                                        ->where('check_out_date', '>=', $checkOutDate);
                                });
                        });
                })
                    ->whereNotNull('room_id')
                    ->pluck('room_id');

                $availableRooms = Room::where('type_id', $bookingDetail->room_type)
                    ->whereNotIn('room_id', $occupiedRoomIds)
                    ->inRandomOrder()
                    ->first();

                if (!$availableRooms) {
                    DB::rollBack();
                    Log::warning('Không còn phòng trống để xếp cho chi tiết booking', [
                        'booking_detail_id' => $bookingDetail->booking_detail_id,
                        'room_type' => $bookingDetail->room_type,
                        'check_in_date' => $checkInDate,
                        'check_out_date' => $checkOutDate
                    ]);
                    return response()->json([
                        'error' => "Không còn phòng trống loại '{$bookingDetail->roomType->type_name}' để xếp trong khoảng thời gian từ {$checkInDate} đến {$checkOutDate}"
                    ], 400);
                }

                $bookingDetail->room_id = $availableRooms->room_id;
                $bookingDetail->save();

                $assignedRooms[] = [
                    'room_name' => $availableRooms->room_name,
                    'room_type' => $bookingDetail->roomType ? $bookingDetail->roomType->type_name : 'N/A',
                ];

                Log::info('Xếp phòng ngẫu nhiên thành công', [
                    'booking_detail_id' => $bookingDetail->booking_detail_id,
                    'room_id' => $availableRooms->room_id
                ]);
            }

            DB::commit();

            Log::info('Xếp phòng ngẫu nhiên thành công cho booking', [
                'booking_id' => $bookingId,
                'assigned_rooms' => $assignedRooms
            ]);

            return response()->json([
                'message' => 'Xếp phòng ngẫu nhiên thành công',
                'booking_id' => $bookingId,
                'assigned_rooms' => $assignedRooms
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xếp phòng ngẫu nhiên: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Không thể xếp phòng ngẫu nhiên',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy lịch sử booking (completed, cancelled)
     */
    public function getHistoryBookings(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);
            $sortBy = $request->query('sort_by', 'booking_id');
            $sortOrder = $request->query('sort_order', 'desc');
            $search = $request->query('search');
            $status = $request->query('status', ['completed', 'cancelled']);
            $date = $request->query('date');

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
                'booking_hotel.note',
                'booking_hotel.adult',
                'booking_hotel.child',
                'booking_hotel.total_rooms',
                'booking_hotel.additional_fee',
            ])
                ->leftJoin('customers', 'booking_hotel.customer_id', '=', 'customers.customer_id')
                ->with([
                    'customer' => function ($query) {
                        $query->select('customer_id', 'customer_name', 'customer_phone', 'customer_email', 'customer_id_number');
                    },
                    'details' => function ($query) {
                        $query->select(
                            'booking_hotel_detail.booking_detail_id',
                            'booking_hotel_detail.booking_id',
                            'booking_hotel_detail.room_type',
                            'booking_hotel_detail.room_id',
                            'booking_hotel_detail.gia_phong',
                            'booking_hotel_detail.gia_dich_vu',
                            'booking_hotel_detail.total_price',
                            'booking_hotel_detail.status'
                        )
                            ->with([
                                'roomType' => function ($q) {
                                    $q->select('type_id', 'type_name');
                                },
                                'room' => function ($q) {
                                    $q->select('room_id', 'room_name');
                                }
                            ]);
                    }
                ]);

            $statuses = is_array($status) ? $status : [$status];
            $query->whereIn('booking_hotel.status', $statuses);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('booking_hotel.booking_id', 'like', "%$search%")
                        ->orWhere('booking_hotel.orderCode', 'like', "%$search%")
                        ->orWhere('customers.customer_name', 'like', "%$search%")
                        ->orWhere('customers.customer_phone', 'like', "%$search%")
                        ->orWhere('customers.customer_id_number', 'like', "%$search%");
                });
            }

            if ($date) {
                $query->whereDate('booking_hotel.check_out_date', '=', $date);
            }

            $allowedSortColumns = ['booking_id', 'check_in_date', 'check_out_date', 'total_price'];
            $sortColumn = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'booking_id';
            $sortDirection = $sortOrder === 'asc' ? 'asc' : 'desc';
            $query->orderBy("booking_hotel.$sortColumn", $sortDirection);

            $bookings = $query->paginate($perPage, ['*'], 'page', $page);

            $bookings->getCollection()->transform(function ($booking) {
                $booking->check_in = $booking->check_in_date && $booking->check_in_time
                    ? Carbon::createFromFormat('Y-m-d H:i:s', $booking->check_in_date . ' ' . $booking->check_in_time)->format('Y-m-d H:i:s')
                    : $booking->check_in_date;
                $booking->check_out = $booking->check_out_date && $booking->check_out_time
                    ? Carbon::createFromFormat('Y-m-d H:i:s', $booking->check_out_date . ' ' . $booking->check_out_time)->format('Y-m-d H:i:s')
                    : $booking->check_out_date;

                $room_price = $booking->details->sum('gia_phong');
                $service_price = $booking->details->sum('gia_dich_vu');
                $surcharge = $booking->additional_fee ?? 0;
                $total_paid = $booking->total_price;

                $booking->room_name = $booking->details->first() && $booking->details->first()->room
                    ? $booking->details->first()->room->room_name
                    : 'N/A';
                $booking->room_price = $room_price;
                $booking->service_price = $service_price;
                $booking->surcharge = $surcharge;
                $booking->total_paid = $total_paid;

                $booking->payment_status_display = $this->formatPaymentStatus($booking->payment_status);
                $booking->status_display = [
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                ][$booking->status] ?? $booking->status;

                $booking->payment_method_display = [
                    'cash' => 'Tiền mặt',
                    'bank_transfer' => 'Chuyển khoản',
                    'credit_card' => 'Thẻ tín dụng',
                    'momo' => 'Ví Momo',
                    'at_hotel' => 'Tại khách sạn',
                    'thanh_toan_ngay' => 'Thanh toán ngay',
                    'thanh_toan_qr' => 'Thanh toán QR',
                ][$booking->payment_method] ?? $booking->payment_method;

                return $booking;
            });

            Log::info('Lấy danh sách lịch sử booking thành công', [
                'per_page' => $perPage,
                'page' => $page,
                'total' => $bookings->total(),
            ]);

            return response()->json([
                'data' => $bookings->items(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách lịch sử booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'params' => $request->all()
            ]);
            return response()->json([
                'error' => 'Không thể lấy danh sách lịch sử booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
