<?php

namespace App\Http\Controllers\api;

use App\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Lấy thông báo cho chuông (dropdown).
     * LOGIC MỚI HOÀN CHỈNH:
     * 1. Lấy TẤT CẢ thông báo CHƯA ĐỌC (không giới hạn, bất kể ngày nào).
     * 2. Lấy TẤT CẢ thông báo của HÔM NAY (cả đọc và chưa đọc).
     * -> Gộp lại và loại bỏ trùng lặp.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // 1. Lấy tất cả thông báo chưa đọc
        $unreadNotifications = $user->notifications()->whereNull('read_at')->get();
        
        // 2. Lấy tất cả thông báo của hôm nay
        $todayNotifications = $user->notifications()->whereDate('created_at', Carbon::today())->get();
        
        // 3. Gộp 2 danh sách lại và loại bỏ các bản ghi trùng lặp (dựa trên 'id')
        $notifications = $unreadNotifications->merge($todayNotifications)->unique('id');
        
        // 4. Sắp xếp lại danh sách cuối cùng theo thời gian mới nhất
        $sortedNotifications = $notifications->sortByDesc('created_at');

        return response()->json(['status' => true, 'data' => $sortedNotifications->values()->all()]);
    }
    
    /**
     * Lấy tất cả thông báo cho trang "Tất cả thông báo".
     */
    public function getAllNotifications(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $query = $user->notifications()->latest();

        if ($request->has('date') && $request->date) {
            try {
                $query->whereDate('created_at', Carbon::parse($request->date)->toDateString());
            } catch (\Exception $e) {}
        }

        $notifications = $query->paginate(20);
        return response()->json(['status' => true, 'data' => $notifications]);
    }

    /**
     * Đánh dấu một thông báo là đã đọc.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['status' => true, 'message' => 'Đã đánh dấu đã đọc.']);
        }
        return response()->json(['status' => false, 'message' => 'Không tìm thấy thông báo.'], 404);
    }

    /**
     * Đánh dấu TẤT CẢ thông báo là đã đọc.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['status' => true, 'message' => 'Đã đánh dấu tất cả là đã đọc.']);
    }

    /**
     * Đếm số thông báo chưa đọc.
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()->notifications()->whereNull('read_at')->count();
        return response()->json(['status' => true, 'unread_count' => $count]);
    }
}