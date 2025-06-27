<?php

namespace App\Http\Controllers;

use App\Models\BookingHotel;
use App\Models\BookingHotelDetail;
use App\Models\BookingHotelService;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Http\Request;
use League\OAuth1\Client\Server\Server;

class BookingHotelController extends Controller
{
    // public function storeBooking(Request $request)
    // {
    //     try {
    //         // Validate the incoming request data
    //         $bookingDetails = $request->validate([
    //             'check_in_date' => 'required|date',
    //             'check_out_date' => 'required|date',
    //             'type_id' => 'required',
    //             'total_rooms' => 'required|integer',
    //             'so_phong_dich_vu' => 'required|integer',
    //              'services_id' => 'nullable|array',
    //             'total_price' => 'required|numeric',
    //             'phone' => 'required',
    //             'fullName' => 'required|string',
    //             'paymentMethod' => 'required|string',
    //             'booking_type' => 'required|string',
    //             'pricing_type' => 'required|string',
    //             'note' => 'nullable|string',
    //             'gia_1phong' => 'required|numeric',
    //             'gia_1phong_dich_vu' => 'required|numeric',
    //         ]);
    //         //check xem còn phòng trống không
    //         $availableRooms = Room::where('status', 'available')
    //             ->where('type_id', $bookingDetails['type_id'])
    //             ->whereDoesntHave('bookings', function ($query) use ($bookingDetails) {
    //                 $query->where(function ($query) use ($bookingDetails) {
    //                     $query->whereBetween('check_in_date', [$bookingDetails['check_in_date'], $bookingDetails['check_out_date']])
    //                         ->orWhereBetween('check_out_date', [$bookingDetails['check_in_date'], $bookingDetails['check_out_date']])
    //                         ->orWhere(function ($query) use ($bookingDetails) {
    //                             $query->where('check_in_date', '<=', $bookingDetails['check_in_date'])
    //                                 ->where('check_out_date', '>=', $bookingDetails['check_out_date']);
    //                         });
    //                 });
    //             })
    //             ->take($bookingDetails['total_rooms'])
    //             ->pluck('room_id');
    //         //return response()->json(['availableRooms' => $availableRooms]);
    //         // Kiểm tra xem có đủ phòng không
    //         if ($availableRooms->count() < $bookingDetails['total_rooms']) {
    //             return response()->json(['error' => 'HXH THÔNG BÁO SỐ LƯỢNG PHÒNG BẠN CHỌN ĐANG NHIỀU HƠN SỐ LƯỢNG PHÒNG KHÁCH SẠN CÒN TRỐNG'], 400);
    //         }
    //         // Lưu thông tin booking chính
    //         $booking = BookingHotel::create([
    //             'customer_id' => 1, // Thay đổi theo logic thực tế
    //             'booking_type' => $bookingDetails['booking_type'],
    //             'pricing_type' => $bookingDetails['pricing_type'],
    //             'check_in_date' => $bookingDetails['check_in_date'],
    //             'check_out_date' => $bookingDetails['check_out_date'],
    //             'total_rooms' => $bookingDetails['total_rooms'],
    //             'total_price' => $bookingDetails['total_price'],
    //             'payment_status' => 'pending',
    //             'status' => 'pending_confirmation',
    //             'note' => $bookingDetails['note'],
    //         ]);

    //         // Lấy danh sách phòng từ cơ sở dữ liệu



    //         // Lưu thông tin chi tiết cho từng phòng
    //         foreach ($availableRooms as $i => $roomId) {
    //             $isServiceUsed = $i < $bookingDetails['so_phong_dich_vu'];
    //             $totalPrice = $isServiceUsed ? $bookingDetails['gia_1phong_dich_vu'] : $bookingDetails['gia_1phong'];

    //             $bookingDetail = BookingHotelDetail::create([
    //                 'booking_id' => $booking->id,
    //                 //'room_id' => $roomId,
    //                 'room_id' => null,
    //                 'total_price' => $totalPrice,
    //                 'note' => $bookingDetails['note'],
    //             ]);

    //             // Lưu dịch vụ nếu có
    //             if ($isServiceUsed) {
    //                 foreach ($bookingDetails['services_id'] as $serviceId) {
    //                     // Lấy thông tin dịch vụ từ bảng services
    //                     $service = Service::find($serviceId);

    //                     // Kiểm tra xem dịch vụ có tồn tại không
    //                     if ($service) {
    //                         BookingHotelService::create([
    //                             'booking_detail_id' => $bookingDetail->id,
    //                             'service_id' => $serviceId,
    //                             'service_price' => $service->price, // Sử dụng giá từ bảng services
    //                         ]);
    //                     } else {
    //                         // Xử lý nếu dịch vụ không tồn tại (nếu cần)
    //                         // Ví dụ: trả về lỗi hoặc log thông báo
    //                     }
    //                 }
    //             }
    //         }

    //         return response()->json(['message' => 'Booking created successfully!']);
    //     } catch (\Exception $e) {
    //         // Trả về thông tin lỗi chi tiết
    //         return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    //     }
    // }
    public function storeBooking(Request $request)
    {
        try {
            // Validate the incoming request data
            $bookingDetails = $request->validate([
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date',
                'type_id' => 'required|integer',
                'gia_phong' => 'required|numeric',
                'total_rooms' => 'required|integer',
                'room_services' => 'required|array',
                'total_price' => 'required|numeric',
                'phone' => 'required',
                'fullName' => 'required|string',
                'paymentMethod' => 'required|string',
                'booking_type' => 'required|string',
                'note' => 'nullable|string',
            ]);
    
            // Lưu thông tin booking chính
            $booking = BookingHotel::create([
                'customer_id' => 1, // Thay đổi theo logic thực tế
                'booking_type' => $bookingDetails['booking_type'],
                'check_in_date' => $bookingDetails['check_in_date'],
                'check_out_date' => $bookingDetails['check_out_date'],
                'total_rooms' => $bookingDetails['total_rooms'],
                'total_price' => $bookingDetails['total_price'],
                'payment_status' => 'pending',
                'status' => 'pending_confirmation',
                'note' => $bookingDetails['note'],
            ]);
    
            // Lưu thông tin chi tiết cho từng phòng
            for ($i = 0; $i < $bookingDetails['total_rooms']; $i++) {
                BookingHotelDetail::create([
                    'booking_id' => $booking->booking_id,
                    'room_id' => null, // Để trống nếu chưa có thông tin về phòng
                    'gia_phong' => $bookingDetails['gia_phong'],
                    'total_price' => $bookingDetails['gia_phong'],
                    'note' => $bookingDetails['note'],
                ]);
            }
    
            // Tạo mảng để lưu số lượng phòng sử dụng dịch vụ
            $roomsUsingService = [];
    
            // Cập nhật số lượng phòng sử dụng dịch vụ
            foreach ($bookingDetails['room_services'] as $service) {
                foreach ($service['services_id'] as $serviceId) {
                    if (!isset($roomsUsingService[$serviceId])) {
                        $roomsUsingService[$serviceId] = 0;
                    }
                    $roomsUsingService[$serviceId]++;
                }
            }
    
            // Lưu thông tin số lượng phòng dùng dịch vụ vào booking
            $booking->rooms_using_service = json_encode($roomsUsingService);
            $booking->save();
    
            return response()->json(['message' => 'Booking created successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
