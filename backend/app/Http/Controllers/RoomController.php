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
            $query = Room::with('roomType')->orderBy('floor_number', 'asc');
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);

            if ($perPage === 'all') {
                $rooms = $query->get();
                $total = $rooms->count();
                $currentPage = 1;
            } else {
                $rooms = $query->paginate($perPage, ['*'], 'page', $page);
                $total = $rooms->total();
                $currentPage = $rooms->currentPage();
            }

            return response()->json([
                'status' => true,
                'data' => $rooms->items(),
                'total' => $total,
                'current_page' => $currentPage,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Fetch rooms error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lấy danh sách phòng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Store room request:', $request->all());
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:255|unique:rooms,room_name',
            'type_id' => 'required|exists:room_types,type_id',
            'floor_number' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,pending_cancel',
            'description' => 'string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['room_name', 'type_id', 'floor_number', 'status', 'description']);
            $data['description'] = $data['description'] ?: null;

            $room = Room::create($data);
            $room->load('roomType');

            Log::info('Room created:', $room->toArray());
            return response()->json(['success' => true, 'data' => $room], 201);
        } catch (\Exception $e) {
            Log::error('Store room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function show($room_id)
    {
        try {
            $room = Room::with('roomType')->findOrFail($room_id);
            return response()->json(['success' => true, 'data' => $room], 200);
        } catch (\Exception $e) {
            Log::error('Show room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Phòng không tồn tại.'], 404);
        }
    }

    public function update(Request $request, $room_id)
    {
        Log::info('Update room request:', ['room_id' => $room_id, 'data' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:255|unique:rooms,room_name,' . $room_id . ',room_id',
            'type_id' => 'required|exists:room_types,type_id',
            'floor_number' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,pending_cancel',
            'description' => 'string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $room = Room::findOrFail($room_id);
            $data = $request->only(['room_name', 'type_id', 'floor_number', 'status', 'description']);
            $data['description'] = $data['description'] ?: null;

            $room->update($data);
            $room->load('roomType');

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
