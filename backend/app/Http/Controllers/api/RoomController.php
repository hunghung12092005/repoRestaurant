<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Lấy danh sách phòng.
     */
     public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $searchTerm = $request->query('search');
        $typeId = $request->query('type_id');
        $query = Room::with('roomType');
        $isSearchingOrFiltering = $searchTerm || $typeId;
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('room_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('floor_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('roomType', function ($subQuery) use ($searchTerm) {
                      $subQuery->where('type_name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        if ($typeId) {
            $query->where('type_id', $typeId);
        }
        $query->latest('created_at');
        if ($isSearchingOrFiltering) {
            $rooms = $query->get();
            return response()->json([
                'data' => $rooms,
                'total' => $rooms->count(),
                'is_paginated' => false,
            ]);
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'is_paginated' => true,
        ]);
    }

    /**
     * Lưu một phòng mới.
     */
    public function store(Request $request)
    {
        Log::info('Store room request:', $request->all());
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:255|unique:rooms,room_name',
            'type_id' => 'required|exists:room_types,type_id',
            'floor_number' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $room = Room::create($request->all());
            $room->load('roomType');
            Log::info('Room created:', $room->toArray());
            return response()->json(['success' => true, 'data' => $room], 201);
        } catch (\Exception $e) {
            Log::error('Store room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi máy chủ khi tạo phòng.'], 500);
        }
    }

    /**
     * Hiển thị thông tin một phòng.
     */
    public function show($room_id)
    {
        try {
            $room = Room::with('roomType')->findOrFail($room_id);
            return response()->json(['success' => true, 'data' => $room], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Phòng không tồn tại.'], 404);
        }
    }

    /**
     * Cập nhật thông tin phòng.
     */
    public function update(Request $request, $room_id)
    {
        Log::info('Update room request:', ['room_id' => $room_id, 'data' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:255|unique:rooms,room_name,' . $room_id . ',room_id',
            'type_id' => 'required|exists:room_types,type_id',
            'floor_number' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $room = Room::findOrFail($room_id);
            $room->update($request->all());
            $room->load('roomType');
            Log::info('Room updated:', $room->toArray());
            return response()->json(['success' => true, 'data' => $room], 200);
        } catch (\Exception $e) {
            Log::error('Update room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi máy chủ khi cập nhật phòng.'], 500);
        }
    }

    /**
     * Xóa một phòng.
     */
    public function destroy($room_id)
    {
        Log::info('Delete room request:', ['room_id' => $room_id]);
        try {
            $room = Room::findOrFail($room_id);
            $room->delete();
            Log::info('Room deleted:', ['room_id' => $room_id]);
            return response()->json(['success' => true, 'message' => 'Xóa phòng thành công'], 200);
        } catch (\Illuminate\Database\QueryException $e) {
             Log::error('Delete room error (QueryException): ' . $e->getMessage());
             // Bắt lỗi khóa ngoại
            if ($e->errorInfo[1] == 1451) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa phòng này vì đang có dữ liệu liên quan (ví dụ: đang được đặt).'], 409);
            }
             return response()->json(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu.'], 500);
        } catch (\Exception $e) {
            Log::error('Delete room error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi máy chủ khi xóa phòng.'], 500);
        }
    }
}