<?php

namespace App\Http\Controllers;

use App\Models\BookingHotelDetail;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class ChatAIController extends Controller
{
    public function checkAvailability(Request $request)
    {
        // Lấy ngày hiện tại
        $checkInDate = Carbon::now()->format('Y-m-d');

        // Lấy ngày mai
        $checkOutDate = Carbon::now()->addDay()->format('Y-m-d');
        // Lấy tất cả các loại phòng
        $roomTypes = RoomType::all();
        $availability = [];

        foreach ($roomTypes as $roomType) {
            // Lấy tất cả các phòng thuộc loại này
            $rooms = Room::where('type_id', $roomType->type_id)->get();
            //return response()->json(['rooms' => $rooms, 'roomType' => $roomType]);
            // Lấy các room_id đã được đặt trong khoảng thời gian này
            $bookedRoomIds = BookingHotelDetail::whereHas('booking', function ($query) use ($checkInDate, $checkOutDate) {
                $query->whereIn('status', ['confirmed', 'completed']) // ✅ thêm điều kiện này
                    ->where(function ($query) use ($checkInDate, $checkOutDate) {
                        $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                            ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate]);
                    });
            })->pluck('room_id');


            // Tính số lượng phòng còn trống
            $availableRoomsCount = $rooms->whereNotIn('room_id', $bookedRoomIds)->count();

            $availability[] = [
                'room_type' => $roomType->type_id,
                'room_name' => $roomType->type_name,
                //'prices' => $roomType->prices,
                'available_rooms' => $availableRoomsCount
            ];
        }

        return response()->json($availability);
    }
    public function hotelInfo()
    {
        $data = DB::table('hotel_infos')->select('title', 'content')->get();
        return response()->json($data);
    }
    //admin
     public function index()
    {
        return DB::table('hotel_infos')->orderByDesc('id')->get();
    }

    public function store(Request $request)
    {
        DB::table('hotel_infos')->insert([
            'title' => $request->title,
            'content' => $request->content,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['message' => 'Đã thêm mới'], 201);
    }

    public function update(Request $request, $id)
    {
        DB::table('hotel_infos')->where('id', $id)->update([
            'title' => $request->title,
            'content' => $request->content,
            'updated_at' => now()
        ]);
        return response()->json(['message' => 'Đã cập nhật']);
    }

    public function destroy($id)
    {
        DB::table('hotel_infos')->where('id', $id)->delete();
        return response()->json(['message' => 'Đã xoá']);
    }
     public function hotelLinks()
    {
        return response()->json([
            [
                'title' => 'HXH Royal',
                'price' => '690.000đ / đêm',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRQjHZUfs4fOM83j681LzHZeGvyp6DyLrbOPana164-uruC8SBvwKG6ivxtWf8BvCOZ6q4&usqp=CAU',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ],
            [
                'title' => 'HXH Royal 1',
                'price' => '690.000đ / đêm',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQQRkhre2vWNe8iIEMTJaun6rm2eIuPXJBUBvNxNgAXXAUHOZtxn9Q5BAKdz3nXn5eck6E&usqp=CAU',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ],
            [
                'title' => 'HXH VIP Suite',
                'price' => '1.380.000đ / đêm',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRQjHZUfs4fOM83j681LzHZeGvyp6DyLrbOPana164-uruC8SBvwKG6ivxtWf8BvCOZ6q4&usqp=CAU',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ],
            [
                'title' => 'Phòng đôi view đẹp',
                'price' => '1.380.000đ / đêm',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQQRkhre2vWNe8iIEMTJaun6rm2eIuPXJBUBvNxNgAXXAUHOZtxn9Q5BAKdz3nXn5eck6E&usqp=CAU',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ],
            [
                'title' => 'HXH Executive Suite',
                'price' => '1.380.000đ / đêm',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRQjHZUfs4fOM83j681LzHZeGvyp6DyLrbOPana164-uruC8SBvwKG6ivxtWf8BvCOZ6q4&usqp=CAU',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ],
            [
                'title' => 'HXH Deluxe Twin',
                'price' => '5.100.000đ / đêm',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQQRkhre2vWNe8iIEMTJaun6rm2eIuPXJBUBvNxNgAXXAUHOZtxn9Q5BAKdz3nXn5eck6E&usqp=CAU',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ],
            [
                'title' => 'Liên hệ đặt phòng',
                'price' => 'HXH Hotel',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS0TFUElCZcHO51kWoiNKkotqlI6TMA6vLjMw&s',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ],
            [
                'title' => 'Liên hệ hủy đặt phòng',
                'price' => 'HXH Hotel',
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTFa_DpqDZQ4Tc2rGrx9RhEW8ApuNnp-JAqrA&s',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ],
            [
                'title' => 'Nhân viên hỗ trợ ',
                'price' => 'HXH Hotel',
                'image' => 'https://www.hoteljob.vn/files/TRUNG/anh%20thang%207/bi-quyet-de-tro-thanh-nhan-vien-khach-san-chuyen-nghiep-02.jpg',
                'url' => 'http://127.0.0.1:5173/booking_hotel'
            ]
        ]);
    }
}
