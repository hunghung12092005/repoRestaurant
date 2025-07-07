<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
     public function index(Request $request)
    {
        $query = News::with(['category', 'author'])->latest('publish_date');

        // Lọc theo trạng thái cho người dùng public.
        if (!$request->has('from_admin')) {
            $query->where('status', true);
        }

        // Lọc theo từ khóa tìm kiếm
        if ($request->has('q') && $request->input('q')) {
            $searchQuery = $request->input('q');
            $query->where('title', 'like', '%' . $searchQuery . '%')
                  ->orWhereHas('category', function ($q) use ($searchQuery) {
                      $q->where('name', 'like', '%' . $searchQuery . '%');
                  });
        }
        
        // Thêm bộ lọc theo danh mục
        if ($request->has('category_id') && $request->input('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $perPage = $request->input('per_page', 10);
        $news = $query->paginate($perPage);
        
        return response()->json($news);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:news_categories,id',
            'author_id'   => 'required|integer|exists:users,id',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'summary'     => 'nullable|string',
            'tags'        => 'nullable|string|max:255',
            'status'      => 'required|boolean',
            'is_pinned'   => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['publish_date'] = now();

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/news_thumbnails'), $filename);
            $data['thumbnail'] = $filename;
        }

        $news = News::create($data);
        $news->load(['category', 'author']);
        return response()->json($news, 201);
    }

    public function show(Request $request,News $news)
    {
        if (!$news->status && !$request->has('from_admin')) {
            return response()->json(['message' => 'News not found'], 404);
        }

        if (!$request->has('from_admin')) {
            $news->views += 1;
            $news->save();
        }
        $news->load(['category', 'author', 'comments.user']);
        return response()->json($news);
    }

    public function update(Request $request, News $news)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:news_categories,id',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'summary'     => 'nullable|string',
            'tags'        => 'nullable|string|max:255',
            'status'      => 'required|boolean',
            'is_pinned'   => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('thumbnail')) {
            if ($news->thumbnail && File::exists(public_path('images/news_thumbnails/' . $news->thumbnail))) {
                File::delete(public_path('images/news_thumbnails/' . $news->thumbnail));
            }
            $file = $request->file('thumbnail');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/news_thumbnails'), $filename);
            $data['thumbnail'] = $filename;
        }

        $news->update($data);
        $news->load(['category', 'author']);
        return response()->json($news);
    }

    public function destroy(News $news)
    {
        if ($news->thumbnail && File::exists(public_path('images/news_thumbnails/' . $news->thumbnail))) {
            File::delete(public_path('images/news_thumbnails/' . $news->thumbnail));
        }
        $news->delete();
        return response()->json(null, 204);
    }

    /**
     * [PHƯƠNG THỨC MỚI] Lấy các bài viết được ghim.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPinned(Request $request)
    {
        $pinnedNews = News::where('is_pinned', true)
                            ->where('status', true) // Chỉ lấy các bài ghim đang được hiển thị
                            ->with(['author', 'category'])
                            ->latest('publish_date')
                            ->take(5) // Lấy tối đa 5 bài
                            ->get();

        return response()->json($pinnedNews);
    }
}