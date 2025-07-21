<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingHotel;
use App\Models\CancelBooking;
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
    private $bookingDetailsTable = 'booking_hotel_detail';

    public function getSystemOverview()
    {
        try {
            return response()->json([
                'statWidgets' => $this->getStatWidgetData(),
                'revenueChart' => $this->getRevenueChartData(),
                'contentActivityChart' => $this->getContentActivityChartData(),
                'bookingsByRoomTypeChart' => $this->getBookingsByRoomTypeChartData(),
                'latestNews' => $this->getLatestNews(),
                'latestComments' => $this->getLatestComments(),
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
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // 1. Doanh thu tháng này
        $revenueThisMonth = DB::table('booking_room_status')
            ->join('booking_hotel', 'booking_room_status.booking_id', '=', 'booking_hotel.booking_id')
            ->where('booking_hotel.status', 'completed')
            ->where('booking_room_status.created_at', '>=', $startOfMonth)
            ->sum('booking_room_status.total_paid');

        // 2. Lượt đặt phòng tháng này
        $bookingsThisMonth = BookingHotel::where('created_at', '>=', $startOfMonth)->count();

        // 3. Lượt hủy tháng này
        $cancellationsThisMonth = CancelBooking::where('cancellation_date', '>=', $startOfMonth)->count();

        // 4. Tình trạng phòng tháng này
        $totalRooms = Room::count();
        $occupiedRoomsThisMonth = BookingHotel::join($this->bookingDetailsTable, 'booking_hotel.booking_id', '=', $this->bookingDetailsTable . '.booking_id')
            ->where('check_in_date', '<=', $endOfMonth) // Bắt đầu trước khi tháng kết thúc
            ->where('check_out_date', '>', $startOfMonth) // Và kết thúc sau khi tháng bắt đầu
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->distinct($this->bookingDetailsTable . '.room_id')
            ->count($this->bookingDetailsTable . '.room_id');
        $availableRoomsThisMonth = $totalRooms > $occupiedRoomsThisMonth ? $totalRooms - $occupiedRoomsThisMonth : 0;
        
        // 5. Tổng loại phòng
        $totalRoomTypes = RoomType::count();

        return [
            'revenueThisMonth' => ['value' => $revenueThisMonth],
            'bookingsThisMonth' => ['value' => $bookingsThisMonth],
            'cancellationsThisMonth' => ['value' => $cancellationsThisMonth],
            'roomStatusMonthly' => [
                'occupied' => $occupiedRoomsThisMonth, 
                'available' => $availableRoomsThisMonth, 
                'total' => $totalRooms
            ],
            'totalRoomTypes' => ['value' => $totalRoomTypes],
        ];
    }

    private function getContentActivityChartData()
    {
        $labels = [];
        $datasets = [
            ['label' => 'Tin tức mới', 'data' => []],
            ['label' => 'Bình luận mới', 'data' => []],
            ['label' => 'Số phòng có khách', 'data' => []], // Đổi tên & logic
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $labels[] = 'Tháng ' . $date->format('n/Y');
            
            $datasets[0]['data'][] = News::whereYear('publish_date', $date->year)->whereMonth('publish_date', $date->month)->count();
            $datasets[1]['data'][] = NewsComment::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count();
            
            // LOGIC MỚI: Đếm số phòng duy nhất có khách trong từng tháng
            $datasets[2]['data'][] = BookingHotel::join($this->bookingDetailsTable, 'booking_hotel.booking_id', '=', $this->bookingDetailsTable . '.booking_id')
                ->where('check_in_date', '<=', $endOfMonth)
                ->where('check_out_date', '>', $startOfMonth)
                ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
                ->distinct($this->bookingDetailsTable . '.room_id')
                ->count($this->bookingDetailsTable . '.room_id');
        }
        return compact('labels', 'datasets');
    }

    private function getRevenueChartData()
    {
        $labels = [];
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = 'Tháng ' . $date->format('n/Y');
            $revenueData[] = DB::table('booking_room_status')
                ->join('booking_hotel', 'booking_room_status.booking_id', '=', 'booking_hotel.booking_id')
                ->where('booking_hotel.status', 'completed')
                ->whereYear('booking_room_status.created_at', $date->year)
                ->whereMonth('booking_room_status.created_at', $date->month)
                ->sum('booking_room_status.total_paid');
        }
        return ['labels' => $labels, 'datasets' => [['label' => 'Doanh thu', 'data' => $revenueData]]];
    }

    private function getBookingsByRoomTypeChartData()
    {
        $today = Carbon::today();
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
        return ['labels' => array_keys($data), 'data' => array_values($data)];
    }
    
    private function getLatestNews() {
        return News::with('author:id,name')->latest('publish_date')->take(5)->get(['id', 'title', 'publish_date', 'author_id']);
    }

    private function getLatestComments() {
        return NewsComment::with(['user:id,name', 'news:id,title'])->latest()->take(5)->get(['id', 'user_id', 'news_id', 'content', 'created_at']);
    }
}