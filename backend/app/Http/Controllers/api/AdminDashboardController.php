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
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Endpoint chính để lấy tất cả dữ liệu tổng quan cho trang dashboard mới.
     */
    public function getSystemOverview()
    {
        return response()->json([
            'statWidgets' => $this->getStatWidgetData(),
            'bookingsByRoomTypeChart' => $this->getBookingsByRoomTypeChartData(),
            'contentActivityChart' => $this->getContentActivityChartData(),
            'latestNews' => $this->getLatestNews(),
            'latestComments' => $this->getLatestComments(),
            'recentBookings' => $this->getRecentBookings(),
        ]);
    }

    /**
     * Dữ liệu cho 4 thẻ thống kê nhanh.
     */
    private function getStatWidgetData()
    {
        $totalNews = News::count();
        $totalComments = NewsComment::count();
        $totalRoomTypes = RoomType::count();

        $occupiedRooms = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();
        $totalRooms = Room::count();

        return [
            'news' => ['value' => $totalNews],
            'comments' => ['value' => $totalComments],
            'roomTypes' => ['value' => $totalRoomTypes],
            'roomStatus' => [
                'occupied' => $occupiedRooms,
                'available' => $availableRooms,
                'total' => $totalRooms,
            ]
        ];
    }

    /**
     * Dữ liệu cho biểu đồ tròn: Số lượng phòng đang có khách theo từng loại phòng.
     */
    private function getBookingsByRoomTypeChartData()
    {
        $data = Room::where('status', 'occupied')
            ->join('room_types', 'rooms.type_id', '=', 'room_types.type_id')
            ->select('room_types.type_name', DB::raw('count(rooms.room_id) as count'))
            ->groupBy('room_types.type_name')
            ->pluck('count', 'type_name')
            ->all();

        return [
            'labels' => array_keys($data),
            'data' => array_values($data),
        ];
    }

    /**
     * Dữ liệu cho biểu đồ đường: Số lượng tin tức và bình luận theo tháng (6 tháng gần nhất).
     */
    private function getContentActivityChartData()
    {
        $labels = [];
        $newsData = [];
        $commentsData = [];
        $bookingsData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthYear = $date->format('m/Y'); // Lấy tháng/năm (VD: 07/2025)
            $labels[] = $this->convertMonthToVietnamese($date->format('m'), $date->format('Y')); // Chuyển sang tiếng Việt

            $newsData[] = News::whereYear('publish_date', $date->year)
                ->whereMonth('publish_date', $date->month)
                ->count();

            $commentsData[] = NewsComment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $bookingsData[] = BookingHotel::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Tin tức mới', 'data' => $newsData],
                ['label' => 'Bình luận mới', 'data' => $commentsData],
                ['label' => 'Đặt phòng mới', 'data' => $bookingsData], // Thêm dữ liệu đặt phòng
            ]
        ];
    }

    private function convertMonthToVietnamese($month, $year)
    {
        $months = [
            '01' => 'Tháng 1',
            '02' => 'Tháng 2',
            '03' => 'Tháng 3',
            '04' => 'Tháng 4',
            '05' => 'Tháng 5',
            '06' => 'Tháng 6',
            '07' => 'Tháng 7',
            '08' => 'Tháng 8',
            '09' => 'Tháng 9',
            '10' => 'Tháng 10',
            '11' => 'Tháng 11',
            '12' => 'Tháng 12',
        ];
        return $months[$month] . ' ' . $year;
    }

    /**
     * Dữ liệu cho bảng: 5 tin tức mới nhất.
     */
    private function getLatestNews()
    {
        return News::with('author') // Giả sử model News có quan hệ 'author' đến User
            ->latest('publish_date')
            ->take(5)
            ->get(['id', 'title', 'publish_date', 'author_id', 'status']);
    }

    /**
     * Dữ liệu cho bảng: 5 bình luận mới nhất.
     */
    private function getLatestComments()
    {
        // Giả sử model NewsComment có quan hệ 'user' và 'news'
        return NewsComment::with(['user', 'news:id,title'])
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'news_id', 'content', 'created_at']);
    }

    /**
     * Dữ liệu cho bảng: 5 lượt đặt phòng gần đây.
     */
    /**
     * Dữ liệu cho bảng: 5 lượt đặt phòng gần đây.
     */
    /**
     * Dữ liệu cho bảng: 5 lượt đặt phòng gần đây.
     */
    private function getRecentBookings()
    {
        return BookingHotel::with(['customer', 'details.roomType']) // Sử dụng 'roomType' thay vì 'roomTypeInfo'
            ->latest('created_at')
            ->take(5)
            ->get(['booking_id', 'customer_id', 'check_in_date', 'check_out_date', 'status']);
    }
}
