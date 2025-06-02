<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MenuCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categories = MenuCategory::all();
            Log::info('Danh sách danh mục món ăn:', $categories->toArray());
            return response()->json(['data' => $categories], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách danh mục món ăn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi lấy danh sách danh mục món ăn', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            Log::info('Dữ liệu gửi lên:', $request->all());
            $category = MenuCategory::create($request->only(['category_name', 'description']));
            return response()->json(['data' => $category, 'message' => 'Thêm danh mục món ăn thành công'], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm danh mục món ăn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm danh mục món ăn', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $category = MenuCategory::findOrFail($id);
            $category->update($request->only(['category_name', 'description']));
            return response()->json(['data' => $category, 'message' => 'Cập nhật danh mục món ăn thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật danh mục món ăn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật danh mục món ăn', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $category = MenuCategory::findOrFail($id);
            if ($category->menus()->exists()) {
                return response()->json(['message' => 'Không thể xóa danh mục vì có món ăn đang sử dụng'], 422);
            }
            $category->delete();
            return response()->json(['message' => 'Xóa danh mục món ăn thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa danh mục món ăn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xóa danh mục món ăn', 'error' => $e->getMessage()], 500);
        }
    }
}