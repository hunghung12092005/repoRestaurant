<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- THÊM DÒNG NÀY
use Illuminate\Support\Facades\Validator;

class NewsCommentController extends Controller
{
    // ... các phương thức khác không thay đổi ...
    public function index()
    {
        $comments = NewsComment::with(['user', 'news:id,title'])->latest()->paginate(15);
        return response()->json($comments);
    }

    public function destroy(NewsComment $comment)
    {
        $comment->delete();
        return response()->json(null, 204);
    }

    public function commentsByNewsId($newsId)
    {
        $comments = NewsComment::with('user')->where('news_id', $newsId)->latest()->get();
        return response()->json($comments);
    }
    
    public function store(Request $request, $newsId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment = NewsComment::create([
            'content' => $request->input('content'),
            'news_id' => $newsId,
            'user_id' => Auth::id() // Yêu cầu người dùng phải đăng nhập
        ]);
        
        $comment->load('user');

        return response()->json($comment, 201);
    }
}