<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;

class NewsCategoryController extends Controller
{
    public function index()
    {
        $categories = NewsCategory::all();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $category = NewsCategory::create($request->only(['name', 'description']));
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = NewsCategory::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = NewsCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $category->update($request->only(['name', 'description']));
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = NewsCategory::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}