<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        // Middleware 'auth:api' đã kiểm tra rồi, nhưng chúng ta vẫn có thể check lại
        // Hoặc đơn giản là lấy user luôn.
        /** @var \App\Models\User $user */
        $user = Auth::guard('api')->user();

        if (!$user) {
            // Dòng này gần như không bao giờ xảy ra nếu middleware đã chạy đúng
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

         $notifications = $user->notifications()
                              ->whereDate('created_at', Carbon::today()) // <-- BƯỚC 2: Thêm điều kiện lọc theo ngày hiện tại
                              ->limit(15) // Vẫn giữ giới hạn để tránh quá tải
                              ->get();
        return response()->json(['status' => true, 'data' => $notifications]);
    }

    public function markAsRead($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['status' => true, 'message' => 'Đã đánh dấu đã đọc.']);
        }
        return response()->json(['status' => false, 'message' => 'Không tìm thấy thông báo.'], 404);
    }

    public function markAllAsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $user->unreadNotifications->markAsRead();

        return response()->json(['status' => true, 'message' => 'Đã đánh dấu tất cả là đã đọc.']);
    }
}