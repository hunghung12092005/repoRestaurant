<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
// Bỏ Illuminate\Support\Facades\Auth; vì không dùng nữa
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
     public function index(Request $request)
    {
        $query = News::with(['category', 'author'])->latest('publish_date');

        // Lọc theo từ khóa tìm kiếm
        if ($request->has('q') && $request->input('q')) {
            $searchQuery = $request->input('q');
            $query->where('title', 'like', '%' . $searchQuery . '%')
                  ->orWhereHas('category', function ($q) use ($searchQuery) {
                      $q->where('name', 'like', '%' . $searchQuery . '%');
                  });
        }
        
        // [THAY ĐỔI] Thêm bộ lọc theo danh mục
        if ($request->has('category_id') && $request->input('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $news = $query->paginate(10);
        return response()->json($news);
    }

    public function store(Request $request)
    {
        // **THAY ĐỔI: Thêm author_id vào validator**
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:news_categories,id',
            'author_id'   => 'required|integer|exists:users,id', // Yêu cầu author_id và phải tồn tại trong bảng users
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
        // **THAY ĐỔI: author_id đã có trong $data, không cần gán từ Auth::id() nữa**
        // $data['author_id'] = Auth::id(); // Bỏ dòng này
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
        if (!$request->has('from_admin')) {
            $news->views += 1;
            $news->save();
        }
        $news->load(['category', 'author', 'comments.user']);
        return response()->json($news);
    }

    public function update(Request $request, News $news)
    {
        // Validator không cần author_id vì chúng ta không cho phép thay đổi tác giả
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
        // **THAY ĐỔI: Bỏ dòng gán author_id để giữ nguyên tác giả gốc**
        // $data['author_id'] = null; // Bỏ dòng này

        if ($request->hasFile('thumbnail')) {
            if ($news->thumbnail && File::exists(public_path('images/news_thumbnails/' . $news->thumbnail))) {
                File::delete(public_path('images/news_thumbnails/' . $news->thumbnail));
            }
            $file = $request->file('thumbnail');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/news_thumbnails'), $filename);
            $data['thumbnail'] = $filename;
        }
        // Bỏ else vì nếu không có file mới, ta không cần gán lại thumbnail
        // else {
        //     $data['thumbnail'] = $news->thumbnail; 
        // }

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
}