<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $menus = Menu::with('category')->get()->map(function ($menu) {
                return [
                    'menu_id' => $menu->menu_id,
                    'menu_name' => $menu->menu_name,
                    'category_id' => $menu->category_id,
                    'category_name' => $menu->category ? $menu->category->category_name : '',
                    'price' => $menu->price,
                    'description' => $menu->description,
                    'created_at' => $menu->created_at,
                    'updated_at' => $menu->updated_at,
                ];
            });
            Log::info('Danh sách món ăn:', $menus->toArray());
            return response()->json(['data' => $menus], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách món ăn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi lấy danh sách món ăn', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'menu_name' => 'required|string|max:100',
            'category_id' => 'required|integer|exists:menu_categories,category_id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            Log::info('Dữ liệu gửi lên:', $request->all());
            $menu = Menu::create($request->only([
                'menu_name',
                'category_id',
                'price',
                'description',
            ]));
            $menu->load('category');
            $response = [
                'menu_id' => $menu->menu_id,
                'menu_name' => $menu->menu_name,
                'category_id' => $menu->category_id,
                'category_name' => $menu->category ? $menu->category->category_name : '',
                'price' => $menu->price,
                'description' => $menu->description,
                'created_at' => $menu->created_at,
                'updated_at' => $menu->updated_at,
            ];
            return response()->json(['data' => $response, 'message' => 'Thêm món ăn thành công'], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm món ăn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm món ăn', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $menu = Menu::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'menu_name' => 'required|string|max:100',
                'category_id' => 'required|integer|exists:menu_categories,category_id',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
            }

            $menu->update($request->only([
                'menu_name',
                'category_id',
                'price',
                'description',
            ]));
            $menu->load('category');
            $response = [
                'menu_id' => $menu->menu_id,
                'menu_name' => $menu->menu_name,
                'category_id' => $menu->category_id,
                'category_name' => $menu->category ? $menu->category->category_name : '',
                'price' => $menu->price,
                'description' => $menu->description,
                'created_at' => $menu->created_at,
                'updated_at' => $menu->updated_at,
            ];
            return response()->json(['data' => $response, 'message' => 'Cập nhật món ăn thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật món ăn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật món ăn', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $menu = Menu::findOrFail($id);
            $menu->delete();
            return response()->json(['message' => 'Xóa món ăn thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa món ăn: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xóa món ăn', 'error' => $e->getMessage()], 500);
        }
    }
}