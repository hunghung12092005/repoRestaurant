<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingHotel;
use App\Models\News;
use App\Models\NewsComment;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AdminDashboardController extends Controller
{
    // THAY ĐỔI TÊN BẢNG CHI TIẾT ĐẶT PHÒNG Ở ĐÂY CHO ĐÚNG VỚI CSDL CỦA BẠN
    private $bookingDetailsTable = 'booking_hotel_detail'; // <--- SỬA TÊN BẢNG Ở ĐÂY!!!

    public function getSystemOverview()
    {
        try {
            return response()->json([
                'statWidgets' => $this->getStatWidgetData(),
                'bookingsByRoomTypeChart' => $this->getBookingsByRoomTypeChartData(),
                'contentActivityChart' => $this->getContentActivityChartData(),
                'latestNews' => $this->getLatestNews(),
                'latestComments' => $this->getLatestComments(),
                'recentBookings' => $this->getRecentBookings(),
            ]);
        } catch (Exception $e) {
            Log::error('Lỗi nghiêm trọng trong getSystemOverview: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return response()->json([
                'message' => 'Đã xảy ra lỗi ở server khi lấy dữ liệu tổng quan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getStatWidgetData()
    {
        $today = Carbon::today();
        $totalRooms = Room::count();

        // Sử dụng biến tên bảng đã định nghĩa ở trên
        $occupiedRoomsToday = BookingHotel::join($this->bookingDetailsTable, 'booking_hotel.booking_id', '=', $this->bookingDetailsTable . '.booking_id')
            ->where('booking_hotel.check_in_date', '<=', $today)
            ->where('booking_hotel.check_out_date', '>', $today)
            ->whereIn('booking_hotel.status', ['confirmed', 'checked_in'])
            ->distinct($this->bookingDetailsTable . '.room_id')
            ->count($this->bookingDetailsTable . '.room_id');
        
        return [
            'news' => ['value' => News::count()],
            'comments' => ['value' => NewsComment::count()],
            'roomTypes' => ['value' => RoomType::count()],
            'roomStatus' => [
                'occupied' => $occupiedRoomsToday,
                'available' => $totalRooms - $occupiedRoomsToday,
                'total' => $totalRooms,
            ]
        ];
    }

    private function getBookingsByRoomTypeChartData()
    {
        $today = Carbon::today();

        // Sử dụng biến tên bảng đã định nghĩa ở trên
        $data = DB::table('booking_hotel')
            ->join($this->bookingDetailsTable, 'booking_hotel.booking_id', '=', $this->bookingDetailsTable . '.booking_id')
            ->join('rooms', $this->bookingDetailsTable . '.room_id', '=', 'rooms.room_id')
            ->join('room_types', 'rooms.type_id', '=', 'room_types.type_id')
            ->where('booking_hotel.check_in_date', '<=', $today)
            ->where('booking_hotel.check_out_date', '>', $today)
            ->whereIn('booking_hotel.status', ['confirmed', 'checked_in'])
            ->select('room_types.type_name', DB::raw('COUNT(DISTINCT rooms.room_id) as count'))
            ->groupBy('room_types.type_name')
            ->pluck('count', 'type_name')
            ->all();

        return [
            'labels' => array_keys($data),
            'data' => array_values($data),
        ];
    }

    private function getContentActivityChartData()
    {
        $labels = [];
        $datasets = [
            ['label' => 'Tin tức mới', 'data' => []],
            ['label' => 'Bình luận mới', 'data' => []],
            ['label' => 'Đặt phòng mới', 'data' => []],
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $this->convertMonthToVietnamese($date->format('m'), $date->format('Y'));
            
            $datasets[0]['data'][] = News::whereYear('publish_date', $date->year)->whereMonth('publish_date', $date->month)->count();
            $datasets[1]['data'][] = NewsComment::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count();
            $datasets[2]['data'][] = BookingHotel::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count();
        }

        return compact('labels', 'datasets');
    }

    private function convertMonthToVietnamese($month, $year) {
        return 'Tháng ' . (int)$month . ' ' . $year;
    }

    private function getLatestNews() {
        return News::with('author:id,name')->latest('publish_date')->take(5)->get(['id', 'title', 'publish_date', 'author_id']);
    }

    private function getLatestComments() {
        return NewsComment::with('user:id,name')->latest()->take(5)->get(['id', 'user_id', 'content', 'created_at']);
    }
    
    // Hàm này có thể cũng sai tên bảng, tạm thời comment lại để kiểm tra
    private function getRecentBookings() {
        // return BookingHotel::with('customer:customer_id,name')->latest()->take(5)->get(['booking_id', 'customer_id', 'check_in_date', 'status']);
        return []; // Tạm thời trả về mảng rỗng để không gây lỗi
    }
}