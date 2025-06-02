<?php

namespace App\Http\Controllers;

use App\Models\TableType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TableTypeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $tableTypes = TableType::all();
            Log::info('Danh sách loại bàn:', $tableTypes->toArray());
            return response()->json(['data' => $tableTypes], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách loại bàn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi lấy danh sách loại bàn', 'error' => $e->getMessage()], 500);
        }
    }

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
            Log::info('Dữ liệu gửi lên:', $request->all());
            $tableType = TableType::create($request->only(['type_name', 'description']));
            return response()->json(['data' => $tableType, 'message' => 'Thêm loại bàn thành công'], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm loại bàn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm loại bàn', 'error' => $e->getMessage()], 500);
        }
    }

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
            $tableType = TableType::findOrFail($id);
            $tableType->update($request->only(['type_name', 'description']));
            return response()->json(['data' => $tableType, 'message' => 'Cập nhật loại bàn thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật loại bàn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật loại bàn', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $tableType = TableType::findOrFail($id);
            if ($tableType->tables()->exists()) {
                return response()->json(['message' => 'Không thể xóa loại bàn vì có bàn đang sử dụng'], 422);
            }
            $tableType->delete();
            return response()->json(['message' => 'Xóa loại bàn thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa loại bàn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xóa loại bàn', 'error' => $e->getMessage()], 500);
        }
    }
}
