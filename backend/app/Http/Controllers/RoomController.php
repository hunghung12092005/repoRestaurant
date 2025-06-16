<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $rooms = Room::with('roomType')->paginate($perPage);
            $data = $rooms->map(function ($room) {
                $room->amenity_names = $room->amenities ? array_column($room->amenities, 'amenity_name') : [];
                $room->service_names = $room->services ? array_column($room->services, 'service_name') : [];
                return $room;
            });
            Log::info('Fetched rooms:', ['data' => $data->toArray()]);
            return response()->json([
                'success' => true,
                'data' => $data->toArray(),
                'current_page' => $rooms->currentPage(),
                'total' => $rooms->total(),
                'per_page' => $rooms->perPage(),
                'last_page' => $rooms->lastPage()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Index rooms error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Store room request:', $request->all());
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:50',
            'type_id' => 'required|exists:room_types,type_id',
            'floor_number' => 'required|integer|min:1',
            'status' => 'required|in:Trống,Đã đặt,Chờ xác nhận,Bảo trì,Đang dọn dẹp',
            'maintenance_status' => 'required|in:Hoạt động,Đang bảo trì',
            'description' => 'nullable|string',
            'amenities' => 'array',
            'services' => 'array'
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['room_name', 'type_id', 'floor_number', 'status', 'maintenance_status', 'description']);
            $data['amenities'] = $request->amenities ? array_map('intval', $request->amenities) : [];
            $data['services'] = $request->services ? array_map('intval', $request->services) : [];
            $room = Room::create($data);
            Log::info('Room created:', $room->toArray());
            return response()->json(['success' => true, 'data' => $room], 201);
        } catch (\Exception $e) {
            Log::error('Store room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $room_id)
    {
        Log::info('Update room request:', ['room_id' => $room_id, 'data' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:50',
            'type_id' => 'required|exists:room_types,type_id',
            'floor_number' => 'required|integer|min:1',
            'status' => 'required|in:Trống,Đã đặt,Chờ xác nhận,Bảo trì,Đang dọn dẹp',
            'maintenance_status' => 'required|in:Hoạt động,Đang bảo trì',
            'description' => 'nullable|string',
            'amenities' => 'array',
            'services' => 'array'
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $room = Room::findOrFail($room_id);
            $data = $request->only(['room_name', 'type_id', 'floor_number', 'status', 'maintenance_status', 'description']);
            $data['amenities'] = $request->amenities ? array_map('intval', $request->amenities) : [];
            $data['services'] = $request->services ? array_map('intval', $request->services) : [];
            $room->update($data);
            Log::info('Room updated:', $room->toArray());
            return response()->json(['success' => true, 'data' => $room], 200);
        } catch (\Exception $e) {
            Log::error('Update room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($room_id)
    {
        Log::info('Delete room request:', ['room_id' => $room_id]);
        try {
            $room = Room::findOrFail($room_id);
            $room->delete();
            Log::info('Room deleted:', ['room_id' => $room_id]);
            return response()->json(['success' => true, 'message' => 'Xóa phòng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Delete room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}