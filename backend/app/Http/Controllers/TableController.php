<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TableController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $tables = Table::with('tableType')->get()->map(function ($table) {
                return [
                    'table_id' => $table->table_id,
                    'table_name' => $table->table_name,
                    'type_id' => $table->type_id,
                    'type_name' => $table->tableType ? $table->tableType->type_name : '',
                    'capacity' => $table->capacity,
                    'status' => $table->status,
                    'location' => $table->location,
                    'description' => $table->description,
                    'created_at' => $table->created_at,
                    'updated_at' => $table->updated_at,
                ];
            });
            Log::info('Danh sách bàn:', $tables->toArray());
            return response()->json(['data' => $tables], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách bàn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi lấy danh sách bàn', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'table_name' => 'required|string|max:100',
            'type_id' => 'required|integer|exists:table_types,type_id',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|string|in:Trống,Đã đặt,Bảo trì',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            Log::info('Dữ liệu gửi lên:', $request->all());
            $table = Table::create($request->only([
                'table_name',
                'type_id',
                'capacity',
                'status',
                'location',
                'description',
            ]));
            $table->load('tableType');
            $response = [
                'table_id' => $table->table_id,
                'table_name' => $table->table_name,
                'type_id' => $table->type_id,
                'type_name' => $table->tableType ? $table->tableType->type_name : '',
                'capacity' => $table->capacity,
                'status' => $table->status,
                'location' => $table->location,
                'description' => $table->description,
                'created_at' => $table->created_at,
                'updated_at' => $table->updated_at,
            ];
            return response()->json(['data' => $response, 'message' => 'Thêm bàn thành công'], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm bàn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm bàn', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $table = Table::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'table_name' => 'required|string|max:100',
                'type_id' => 'required|integer|exists:table_types,type_id',
                'capacity' => 'required|integer|min:1',
                'status' => 'required|string|in:Trống,Đã đặt,Bảo trì',
                'location' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
            }

            $table->update($request->only([
                'table_name',
                'type_id',
                'capacity',
                'status',
                'location',
                'description',
            ]));
            $table->load('tableType');
            $response = [
                'table_id' => $table->table_id,
                'table_name' => $table->table_name,
                'type_id' => $table->type_id,
                'type_name' => $table->tableType ? $table->tableType->type_name : '',
                'capacity' => $table->capacity,
                'status' => $table->status,
                'location' => $table->location,
                'description' => $table->description,
                'created_at' => $table->created_at,
                'updated_at' => $table->updated_at,
            ];
            return response()->json(['data' => $response, 'message' => 'Cập nhật bàn thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật bàn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật bàn', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $table = Table::findOrFail($id);
            $table->delete();
            return response()->json(['message' => 'Xóa bàn thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa bàn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xóa bàn', 'error' => $e->getMessage()], 500);
        }
    }
}
