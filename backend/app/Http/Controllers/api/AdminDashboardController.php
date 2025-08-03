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
        
        $revenueThisMonth = DB::table('booking_room_status')
            ->join('booking_hotel', 'booking_room_status.booking_id', '=', 'booking_hotel.booking_id')
            ->where('booking_hotel.status', 'completed')
            ->where('booking_room_status.created_at', '>=', $startOfMonth)
            ->sum('booking_room_status.total_paid');

        $bookingsThisMonth = BookingHotel::where('created_at', '>=', $startOfMonth)->count();
        $cancellationsThisMonth = CancelBooking::where('cancellation_date', '>=', $startOfMonth)->count();
        $totalRooms = Room::count();
        $occupiedRoomsThisMonth = BookingHotel::join($this->bookingDetailsTable, 'booking_hotel.booking_id', '=', $this->bookingDetailsTable . '.booking_id')
            ->where('check_in_date', '<=', $endOfMonth)
            ->where('check_out_date', '>', $startOfMonth)
            ->whereIn('booking_hotel.status', ['confirmed', 'checked_in', 'completed'])
            ->distinct($this->bookingDetailsTable . '.room_id')
            ->count($this->bookingDetailsTable . '.room_id');
        $availableRoomsThisMonth = $totalRooms > $occupiedRoomsThisMonth ? $totalRooms - $occupiedRoomsThisMonth : 0;
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
        $newsData = [];
        $commentsData = [];
        $occupiedRoomsData = [];

        // **LOGIC MỚI - ĐẢM BẢO CHÍNH XÁC**
        // Bắt đầu từ tháng đầu tiên trong chuỗi 6 tháng (5 tháng trước)
        $currentMonth = Carbon::now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            // Lấy thông tin tháng hiện tại trong vòng lặp
            $date = $currentMonth->copy();
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $labels[] = 'Tháng ' . $date->format('n/Y');
            
            $newsData[] = News::whereYear('publish_date', $date->year)->whereMonth('publish_date', $date->month)->count();
            $commentsData[] = NewsComment::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count();
            
            $occupiedRoomsData[] = BookingHotel::join($this->bookingDetailsTable, 'booking_hotel.booking_id', '=', $this->bookingDetailsTable . '.booking_id')
                ->where('check_in_date', '<=', $endOfMonth)
                ->where('check_out_date', '>', $startOfMonth)
                ->whereIn('booking_hotel.status', ['confirmed', 'checked_in', 'completed'])
                ->distinct($this->bookingDetailsTable . '.room_id')
                ->count($this->bookingDetailsTable . '.room_id');

            // Chuyển sang tháng tiếp theo cho vòng lặp sau
            $currentMonth->addMonth();
        }

        $datasets = [
            ['label' => 'Tin tức mới', 'data' => $newsData],
            ['label' => 'Bình luận mới', 'data' => $commentsData],
            ['label' => 'Số phòng có khách', 'data' => $occupiedRoomsData],
        ];

        return compact('labels', 'datasets');
    }

    private function getRevenueChartData()
    {
        $labels = [];
        $revenueData = [];
        
        // **LOGIC MỚI - ĐẢM BẢO CHÍNH XÁC**
        // Bắt đầu từ tháng đầu tiên trong chuỗi 6 tháng (5 tháng trước)
        $currentMonth = Carbon::now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            // Lấy thông tin tháng hiện tại trong vòng lặp
            $date = $currentMonth->copy();
            $labels[] = 'Tháng ' . $date->format('n/Y');

            $revenueData[] = DB::table('booking_room_status')
                ->join('booking_hotel', 'booking_room_status.booking_id', '=', 'booking_hotel.booking_id')
                ->where('booking_hotel.status', 'completed')
                ->whereYear('booking_room_status.created_at', $date->year)
                ->whereMonth('booking_room_status.created_at', $date->month)
                ->sum('booking_room_status.total_paid');

            // Chuyển sang tháng tiếp theo cho vòng lặp sau
            $currentMonth->addMonth();
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