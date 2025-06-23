<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsCategoryController extends Controller
{
    public function index()
    {
        return response()->json(NewsCategory::orderBy('name')->get());
    }

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
        return response()->json($category, 201);
    }

    public function show(NewsCategory $newsCategory)
    {
        return response()->json($newsCategory);
    }

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
        return response()->json($newsCategory);
    }

    public function destroy(NewsCategory $newsCategory)
    {
        // Bạn có thể thêm logic để xử lý các tin tức liên quan trước khi xóa
        // Ví dụ: set category_id của chúng thành null
        $newsCategory->news()->update(['category_id' => null]);
        
        $newsCategory->delete();
        return response()->json(null, 204);
    }
}