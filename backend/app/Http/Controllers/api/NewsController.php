<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with(['author', 'category'])->get();
        return response()->json($news);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string',
            'thumbnail' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:news_categories,category_id',
            'status' => 'in:0,1',
            'tags' => 'nullable|string|max:255',
            'is_pinned' => 'boolean',
        ]);

        $news = News::create([
            'title' => $request->title,
            'summary' => $request->summary,
            'content' => $request->content,
            'thumbnail' => $request->thumbnail,
            'author_id' => Auth::id(),
            'category_id' => $request->category_id,
            'status' => $request->status ?? 1,
            'tags' => $request->tags,
            'is_pinned' => $request->is_pinned ?? false,
        ]);

        return response()->json($news, 201);
    }

    public function show($id)
    {
        $news = News::with(['author', 'category', 'comments.user'])->findOrFail($id);
        return response()->json($news);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string',
            'thumbnail' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:news_categories,category_id',
            'status' => 'in:0,1',
            'tags' => 'nullable|string|max:255',
            'is_pinned' => 'boolean',
        ]);

        $news->update($request->only([
            'title',
            'summary',
            'content',
            'thumbnail',
            'category_id',
            'status',
            'tags',
            'is_pinned',
        ]));

        return response()->json($news);
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();
        return response()->json(null, 204);
    }
}