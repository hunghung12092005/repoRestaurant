<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File; // QUAN TRỌNG: Thêm Facade File
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    /**
     * Hiển thị danh sách tin tức với phân trang và tìm kiếm.
     */
    public function index(Request $request)
    {
        $query = News::with(['category', 'author'])->latest('publish_date');

        if ($request->has('q') && $request->input('q')) {
            $searchQuery = $request->input('q');
            $query->where('title', 'like', '%' . $searchQuery . '%');
            $query->orWhereHas('category', function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%');
            });
        }

        $news = $query->paginate(10);
        return response()->json($news);
    }

    /**
     * Lưu một tin tức mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
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
        $data['author_id'] = Auth::id();

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            // THAY ĐỔI: Dùng move() để lưu trực tiếp vào thư mục public
            $file->move(public_path('images/news_thumbnails'), $filename);
            $data['thumbnail'] = $filename;
        }

        $news = News::create($data);
        $news->load(['category', 'author']);

        return response()->json($news, 201);
    }

    /**
     * Hiển thị một tin tức cụ thể.
     */
    public function show(News $news)
{
    // Phương thức này đã có sẵn và public
    $news->load(['category', 'author', 'comments.user']); 
    return response()->json($news);
}

    /**
     * Cập nhật một tin tức đã có.
     */
    public function update(Request $request, News $news)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category_id' => 'required|exists:news_categories,id',
            'thumbnail'   => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', 
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
            // THAY ĐỔI: Dùng File::delete() để xóa ảnh cũ
            if ($news->thumbnail && File::exists(public_path('images/news_thumbnails/' . $news->thumbnail))) {
                File::delete(public_path('images/news_thumbnails/' . $news->thumbnail));
            }
            
            $file = $request->file('thumbnail');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            // THAY ĐỔI: Dùng move() để lưu ảnh mới
            $file->move(public_path('images/news_thumbnails'), $filename);
            $data['thumbnail'] = $filename;
        }

        $news->update($data);
        $news->load(['category', 'author']);
        return response()->json($news);
    }

    /**
     * Xóa một tin tức.
     */
    public function destroy(News $news)
    {
        // THAY ĐỔI: Dùng File::delete() để xóa ảnh liên quan
        if ($news->thumbnail && File::exists(public_path('images/news_thumbnails/' . $news->thumbnail))) {
            File::delete(public_path('images/news_thumbnails/' . $news->thumbnail));
        }
        
        $news->delete();
        return response()->json(null, 204);
    }
}