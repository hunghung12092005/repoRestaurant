<?php

namespace App\Http\Controllers\api;

use App\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

    public function getAllNotifications(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }
        
        // Lấy tất cả thông báo, sắp xếp mới nhất lên đầu, và phân trang (20 mục/trang)
        $notifications = $user->notifications()->latest()->paginate(20);

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

    public function unreadCount(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        try {
            $count = $user->unreadNotifications()->count();
            return response()->json(['status' => true, 'unread_count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Không thể lấy số lượng thông báo.'], 500);
        }
    }

     public function pruneOldNotifications()
    {
        try {
            // Ngày giới hạn (7 ngày trước)
            $cutoffDate = Carbon::now()->subWeek();

            // Xóa các thông báo đã đọc và cũ hơn ngày giới hạn
            Notification::whereNotNull('read_at')
                        ->where('created_at', '<', $cutoffDate)
                        ->delete();
            
            // Trả về thành công nhưng không cần dữ liệu gì đặc biệt
            return response()->json(['status' => true, 'message' => 'Pruning process triggered.']);

        } catch (\Exception $e) {
            // Ghi lại lỗi nếu có, nhưng không làm crash ứng dụng
            Log::error('Lỗi khi dọn dẹp thông báo: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error during pruning.'], 500);
        }
    }
}