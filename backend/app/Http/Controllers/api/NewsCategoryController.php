<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // [THAY ĐỔI CHÍNH Ở ĐÂY]
        // Sử dụng withCount('news') để Laravel tự động thêm một cột 'news_count'
        // vào mỗi kết quả. Đây là cách làm rất hiệu quả.
        $categories = NewsCategory::withCount('news')
                                  ->orderBy('name')
                                  ->get();
        
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:news_categories',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = NewsCategory::create($request->all());
        // Sau khi tạo, tải lại với news_count để trả về dữ liệu nhất quán
        $category->loadCount('news'); 
        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NewsCategory  $newsCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(NewsCategory $newsCategory)
    {
        // Thêm news_count vào cả khi xem chi tiết một danh mục
        $newsCategory->loadCount('news');
        return response()->json($newsCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NewsCategory  $newsCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, NewsCategory $newsCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:news_categories,name,' . $newsCategory->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newsCategory->update($request->all());
        // Tải lại với news_count để trả về dữ liệu nhất quán sau khi cập nhật
        $newsCategory->loadCount('news');
        return response()->json($newsCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NewsCategory  $newsCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(NewsCategory $newsCategory)
    {
        // Kiểm tra xem danh mục có bài viết nào không
        if ($newsCategory->news()->count() > 0) {
            return response()->json([
                'message' => 'Không thể xóa danh mục vì vẫn còn bài viết. Vui lòng chuyển các bài viết sang danh mục khác trước.'
            ], 409); // 409 Conflict
        }
        
        $newsCategory->delete();
        return response()->json(null, 204);
    }
}