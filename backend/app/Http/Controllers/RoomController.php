<?php
    namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // Lấy danh sách phòng
    public function index()
    {
        $rooms = Room::all();
        return response()->json($rooms);
    }

    // Thêm phòng mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_name'   => 'required|string|max:255',
            'room_type'   => 'required|string|max:100',
            'capacity'    => 'required|integer|min:1',
            'price'       => 'required|numeric|min:0',
            'status'      => 'required|string|in:Trống,Đã đặt,Bảo trì',
            'description' => 'nullable|string',
        ]);

        $room = Room::create($validated);
        return response()->json($room, 201);
    }

    // Cập nhật phòng
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'room_name'   => 'required|string|max:255',
            'room_type'   => 'required|string|max:100',
            'capacity'    => 'required|integer|min:1',
            'price'       => 'required|numeric|min:0',
            'status'      => 'required|string|in:Trống,Đã đặt,Bảo trì',
            'description' => 'nullable|string',
        ]);

        $room->update($validated);
        return response()->json($room);
    }

    // Xóa phòng
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return response()->json(['message' => 'Xóa phòng thành công']);
    }
}

?>
