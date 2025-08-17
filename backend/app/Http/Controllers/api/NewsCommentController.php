<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use App\Models\News;
use App\Models\User;
use App\Models\Role;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\NewNotification;
use Illuminate\Support\Str;
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
        $comments = NewsComment::with('user:id,name')
            ->where('news_id', $newsId)
            ->where('is_visible', true) // <-- CHỈ LẤY BÌNH LUẬN ĐƯỢC PHÉP HIỂN THỊ
            ->latest()
            ->get();
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
        
        $news = News::find($newsId);
        $author = $news->user;

          $staffRoleIds = Role::where('name', '!=', 'client')->pluck('id');

            // 2. Tìm tất cả người dùng thuộc các vai trò đó
            $adminsAndStaff = User::whereIn('role_id', $staffRoleIds)->get();
        foreach ($adminsAndStaff as $user) {
            // Tạo bản ghi Notification trong database
            $notification = new Notification();
            $notification->id = Str::uuid();
            $notification->type = 'NEW_COMMENT';
            $notification->notifiable_type = User::class;
            $notification->notifiable_id = $user->id;
            $notification->subject_type = NewsComment::class;
            $notification->subject_id = $comment->id;
            $notification->data = [
                'message' => "{$comment->user->name} đã bình luận về bài viết \"{$news->title}\".",
                'link' => "/admin/news-comments"
            ];
            $notification->save();

            // Phát sóng sự kiện tới frontend
            broadcast(new NewNotification($notification))->toOthers();


        }

        return response()->json($comment, 201);
    }

    public function toggleVisibility(NewsComment $comment)
    {
        // Đảo ngược giá trị is_visible
        $comment->is_visible = !$comment->is_visible;
        $comment->save();

        // Trả về bình luận đã cập nhật
        return response()->json($comment);
    }
}