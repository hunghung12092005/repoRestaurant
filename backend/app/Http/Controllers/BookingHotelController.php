<?php

namespace App\Http\Controllers;

use App\Models\BookingHotel;
use App\Models\BookingHotelDetail;
use App\Models\BookingHotelService;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use Illuminate\Http\Request;
use League\OAuth1\Client\Server\Server;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;
use PayOS\Exceptions\PayOSException;
use Tymon\JWTAuth\Exceptions\JWTException;

class BookingHotelController extends Controller
{
    //lay loai phong dua vao so nguoi
    public function getAvailableRooms(Request $request)
    {
        $request->validate([
            'number_of_guests' => 'required|integer|min:1',
        ]);

        $numberOfGuests = $request->input('number_of_guests');

        // Truy vấn hạng phòng phù hợp
        $availableRooms = RoomType::where('max_occupancy', '>=', $numberOfGuests)->get();

        return response()->json($availableRooms);
    }
    //tao token jwt
    public function generateToken(Request $request)
    {
        try {
            // Xác thực dữ liệu nhập vào
            $request->validate([
                //'email' => 'required|email',
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
            //dd($customer);
            $token = JWTAuth::fromUser($customer);


            return response()->json(['token' => $token]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create token',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
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
            ]);

            // Lấy token từ header
            $token = $request->header('Authorization');
            // return response()->json(['token' => $token]);
            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            // Xác thực token và lấy thông tin người dùng
            try {
                $user = JWTAuth::parseToken()->authenticate();
                // Kiểm tra nếu user là null và tìm kiếm trong bảng customers
                if (!$user) {
                    $sub = JWTAuth::parseToken()->getPayload()->get('sub');
                    $user = Customer::find($sub); // Thay đổi đây cho phù hợp với mô hình của bạn
                }
                if (!$user) {
                    return response()->json(['token' => false], 404);
                }
                // return response()->json(['token' => $user]);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Token is invalid'], 401);
            }

            // Lấy customer_id từ user
            $customerId = $user->customer_id;
            // return response()->json(['customerId' => $customerId]);
            // Lưu thông tin booking chính
            $booking = BookingHotel::create([
                'customer_id' => $customerId,
                'payment_method' => $bookingDetails['payment_method'],
                'booking_type' => $bookingDetails['booking_type'],
                'check_in_date' => $bookingDetails['check_in_date'],
                'check_out_date' => $bookingDetails['check_out_date'],
                'total_rooms' => $bookingDetails['total_rooms'],
                'total_price' => $bookingDetails['total_price'],
                'payment_status' => 'pending',
                'status' => 'pending_confirmation',
                'note' => $bookingDetails['note'],
            ]);
            // return response()->json(['booking' => $booking->booking_id]);
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
    public function getBookingHistory(Request $request)
    {
        try {
            $sub = JWTAuth::parseToken()->getPayload()->get('sub');

            $bookings = BookingHotel::where('customer_id', $sub)
                ->with('roomTypeInfo')
                ->orderBy('booking_id', 'desc') // Thêm dòng này để sắp xếp giảm dần theo booking_id
                ->get();

            // Lúc này, mỗi đối tượng booking trong collection $bookings
            // đã có sẵn một thuộc tính 'roomTypeInfo' chứa đầy đủ thông tin của loại phòng.
            // Bạn không cần phải 'map' lại để thêm 'room_type_name'
            // hoặc 'unset' roomTypeInfo nếu muốn giữ lại toàn bộ data.

            return response()->json([
                'status' => 'success',
                'data' => $bookings, // Trả về bookings trực tiếp, roomTypeInfo đã có sẵn bên trong
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy dữ liệu booking: ' . $e->getMessage(),
            ], 500);
        }
    }
    //payOS
   public function payos(Request $request)
    {
        // Xác thực dữ liệu từ client
        $request->validate([
            'amount' => 'required|numeric', // Giá trị tổng
            'items' => 'required|array', // Chắc chắn 'items' là một mảng
            'items.*.price' => 'required|numeric', // Giá sản phẩm
            // 'items.*.totalServiceCost' => 'required|numeric', // Tổng phí dịch vụ
        ]);

        $amount = $request->input('amount');
        $items = $request->input('items');

        // Tạo dữ liệu cho PayOS
        $data = [
            "orderCode" => intval(substr(strval(microtime(true) * 10000), -6)), // Mã đơn hàng duy nhất
            "amount" => $amount, // Tổng số tiền đã được truyền vào
            "description" => "Đặt hàng với " . count($items) . " sản phẩm", // Mô tả
            "items" => $items, // Dữ liệu item
            "returnUrl" => "http://127.0.0.1:5173/ThanksBooking", // URL trở về
            "cancelUrl" => "http://127.0.0.1:5173/" // URL hủy
        ];

        try {
            // Khởi tạo PayOS
            $payOSClientId = '078daf0e-baed-45f9-b2f9-20b79c89668e';
            $payOSApiKey = '8841f63c-c976-4b89-bf56-b769b035292b';
            $payOSChecksumKey = '266e7a5270d2b2b95f9e85c89215c258fa838acb23c3f62916d7e16ea0dcaf2d';

            $payOS = new PayOS($payOSClientId, $payOSApiKey, $payOSChecksumKey);

            // Gọi API PayOS để tạo link thanh toán
            $response = $payOS->createPaymentLink($data);

            // Kiểm tra phản hồi từ API
            if (isset($response['checkoutUrl'])) {
                // Chuyển hướng đến link thanh toán
                return response()->json(['checkoutUrl' => $response['checkoutUrl']], 200);
            } else {
                // Xử lý khi không có checkoutUrl
                return response()->json(['error' => 'Không thể tạo liên kết thanh toán.'], 500);
            }
        } catch (\Exception $e) {
            // Xử lý lỗi từ PayOS và hiện lỗi ra
            return response()->json([
                'error' => 'Lỗi PayOS: ' . $e->getMessage(),
                'code' => $e->getCode(), // Mã lỗi từ PayOS
            ], 500);
        } catch (\Exception $e) {
            // Xử lý các lỗi khác và hiện lỗi ra
            return response()->json(['error' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }
    
    public function getBookings(Request $request)
    {
        try {
            $status = $request->query('status', 'pending_confirmation');
            $bookings = BookingHotel::with(['customer', 'roomTypeInfo'])
                ->where('status', $status)
                ->get();

            return response()->json($bookings, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách booking: ' . $e->getMessage());
            return response()->json([
                'error' => 'Không thể lấy danh sách booking',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}