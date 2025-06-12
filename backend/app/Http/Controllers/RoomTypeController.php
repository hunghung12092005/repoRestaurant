<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomTypeController extends Controller
{
    public function index()
    {
        try {
            $roomTypes = RoomType::all();
            return response()->json(['data' => $roomTypes], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi lấy danh sách loại phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:100|unique:room_types',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $roomType = RoomType::create($request->all());
            return response()->json(['data' => $roomType, 'message' => 'Thêm loại phòng thành công'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi thêm loại phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $roomType = RoomType::find($id);
        if (!$roomType) {
            return response()->json(['message' => 'Không tìm thấy loại phòng'], 404);
        }

        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:100|unique:room_types,type_name,' . $id . ',type_id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $roomType->update($request->all());
            return response()->json(['data' => $roomType, 'message' => 'Cập nhật loại phòng thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi cập nhật loại phòng', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $roomType = RoomType::find($id);
        if (!$roomType) {
            return response()->json(['message' => 'Không tìm thấy loại phòng'], 404);
        }

        try {
            $roomType->delete();
            return response()->json(['message' => 'Xóa loại phòng thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa loại phòng', 'error' => $e->getMessage()], 500);
        }
    }
}
