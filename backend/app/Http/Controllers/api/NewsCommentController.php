<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// Auth không còn cần thiết trong hàm store nữa
// use Illuminate\Support\Facades\Auth;

class NewsCommentController extends Controller
{
    // ... các phương thức khác không thay đổi ...
    public function index()
    {
        $comments = NewsComment::with(['user:id,name', 'news:id,title'])->latest()->paginate(15);
        return response()->json($comments);
    }

    public function destroy(NewsComment $comment)
    {
        $comment->delete();
        return response()->json(null, 204);
    }

    public function commentsByNewsId($newsId)
    {
        $comments = NewsComment::with('user:id,name')->where('news_id', $newsId)->latest()->get();
        return response()->json($comments);
    }
    
    /**
     * [THAY ĐỔI LỚN] Lưu một bình luận mới mà không cần middleware.
     * Nhận user_id từ request.
     */
    public function store(Request $request, $newsId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:3',
            // Thêm validation cho user_id: bắt buộc, phải là số, và phải tồn tại trong bảng users
            'user_id' => 'required|integer|exists:users,id', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tạo bình luận từ dữ liệu đã validate
        $comment = NewsComment::create([
            'content' => $request->input('content'),
            'news_id' => $newsId,
            'user_id' => $request->input('user_id') // Lấy user_id từ request
        ]);
        
        // Load thông tin người dùng vào bình luận vừa tạo để trả về cho client
        $comment->load('user:id,name');

        return response()->json($comment, 201);
    }
}