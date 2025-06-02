<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RoomTypeController extends Controller
{
    /**
     * Lấy danh sách tất cả loại phòng
     */
    public function index(): JsonResponse
    {
        try {
            $roomTypes = RoomType::all();
            return response()->json(['data' => $roomTypes], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi lấy danh sách loại phòng', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Thêm loại phòng mới
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $roomType = RoomType::create($request->only(['type_name', 'description']));
            return response()->json(['data' => $roomType, 'message' => 'Thêm loại phòng thành công'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi thêm loại phòng', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật thông tin loại phòng
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $roomType = RoomType::findOrFail($id);
            $roomType->update($request->only(['type_name', 'description']));
            return response()->json(['data' => $roomType, 'message' => 'Cập nhật loại phòng thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi cập nhật loại phòng', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xóa loại phòng
     */
    public function destroy($id): JsonResponse
    {
        try {
            $roomType = RoomType::findOrFail($id);
            if ($roomType->rooms()->exists()) {
                return response()->json(['message' => 'Không thể xóa loại phòng vì có phòng đang sử dụng'], 422);
            }
            $roomType->delete();
            return response()->json(['message' => 'Xóa loại phòng thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa loại phòng', 'error' => $e->getMessage()], 500);
        }
    }
}
