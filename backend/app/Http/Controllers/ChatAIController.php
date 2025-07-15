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
}
