<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function index()
    {
        try {
            $rooms = Room::with(['roomType', 'season'])->get();
            Log::info('Fetched rooms: ' . $rooms->count());
            return response()->json(['data' => $rooms], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching rooms: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi lấy danh sách phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:100|unique:rooms',
            'type_id' => 'required|exists:room_types,type_id',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:Trống,Đã đặt,Chờ xác nhận,Bảo trì,Đang dọn dẹp',
            'season_id' => 'required|exists:seasons,season_id',
            'amenities' => 'required|array',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $room = Room::create($request->all());
            return response()->json(['data' => $room, 'message' => 'Thêm phòng thành công'], 201);
        } catch (\Exception $e) {
            Log::error('Error storing room: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json(['message' => 'Không tìm thấy phòng'], 404);
        }

        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:100|unique:rooms,room_name,' . $id . ',room_id',
            'type_id' => 'required|exists:room_types,type_id',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:Trống,Đã đặt,Chờ xác nhận,Bảo trì,Đang dọn dẹp',
            'season_id' => 'required|exists:seasons,season_id',
            'amenities' => 'required|array',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $room->update($request->all());
            return response()->json(['data' => $room, 'message' => 'Cập nhật phòng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating room: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json(['message' => 'Không tìm thấy phòng'], 404);
        }

        try {
            $room->delete();
            return response()->json(['message' => 'Xóa phòng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting room: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xóa phòng', 'error' => $e->getMessage()], 500);
        }
    }
}
