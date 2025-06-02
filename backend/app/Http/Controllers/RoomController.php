<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $rooms = Room::with('roomType')->get()->map(function ($room) {
                return [
                    'room_id' => $room->room_id,
                    'room_name' => $room->room_name,
                    'type_id' => $room->type_id,
                    'type_name' => $room->roomType ? $room->roomType->type_name : '',
                    'capacity' => $room->capacity,
                    'price' => $room->price,
                    'status' => $room->status,
                    'description' => $room->description,
                    'created_at' => $room->created_at,
                    'updated_at' => $room->updated_at,
                ];
            });
            Log::info('Danh sách phòng:', $rooms->toArray()); // Ghi log
            return response()->json(['data' => $rooms], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách phòng: ' . $e->getMessage()); // Ghi log
            return response()->json(['message' => 'Lỗi khi lấy danh sách phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:255',
            'type_id' => 'required|integer|exists:room_types,type_id',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|string|in:Trống,Đã đặt,Bảo trì',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            Log::info('Dữ liệu gửi lên:', $request->all()); // Ghi log
            $room = Room::create($request->only([
                'room_name',
                'type_id',
                'capacity',
                'price',
                'status',
                'description',
            ]));
            $room->load('roomType');
            $response = [
                'room_id' => $room->room_id,
                'room_name' => $room->room_name,
                'type_id' => $room->type_id,
                'type_name' => $room->roomType ? $room->roomType->type_name : '',
                'capacity' => $room->capacity,
                'price' => $room->price,
                'status' => $room->status,
                'description' => $room->description,
                'created_at' => $room->created_at,
                'updated_at' => $room->updated_at,
            ];
            return response()->json(['data' => $response, 'message' => 'Thêm phòng thành công'], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm phòng: ' . $e->getMessage()); // Ghi log
            return response()->json(['message' => 'Lỗi khi thêm phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'room_name' => 'required|string|max:255',
                'type_id' => 'required|integer|exists:room_types,type_id',
                'capacity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'status' => 'required|string|in:Trống,Đã đặt,Bảo trì',
                'description' => 'nullable|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
            }

            $room->update($request->only([
                'room_name',
                'type_id',
                'capacity',
                'price',
                'status',
                'description',
            ]));
            $room->load('roomType');
            $response = [
                'room_id' => $room->room_id,
                'room_name' => $room->room_name,
                'type_id' => $room->type_id,
                'type_name' => $room->roomType ? $room->roomType->type_name : '',
                'capacity' => $room->capacity,
                'price' => $room->price,
                'status' => $room->status,
                'description' => $room->description,
                'created_at' => $room->created_at,
                'updated_at' => $room->updated_at,
            ];
            return response()->json(['data' => $response, 'message' => 'Cập nhật phòng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật phòng: ' . $e->getMessage()); // Ghi log
            return response()->json(['message' => 'Lỗi khi cập nhật phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);
            $room->delete();
            return response()->json(['message' => 'Xóa phòng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa phòng: ' . $e->getMessage()); // Ghi log
            return response()->json(['message' => 'Lỗi khi xóa phòng', 'error' => $e->getMessage()], 500);
        }
    }
}
