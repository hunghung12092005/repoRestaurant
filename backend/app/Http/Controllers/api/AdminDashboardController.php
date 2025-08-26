<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingHotel;
use App\Models\CancelBooking;
use App\Models\News;
use App\Models\NewsComment;
use App\Models\CustomerReview;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminDashboardController extends Controller
{
    private $bookingDetailsTable = 'booking_hotel_detail';

    /**
     * Lấy dữ liệu tổng quan cho trang dashboard.
     * Dữ liệu được tính toán dựa trên khoảng thời gian được cung cấp.
     */
    public function getSystemOverview(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->getDateRangeFromRequest($request);

            return response()->json([
                'kpis' => $this->getKpiData($startDate, $endDate),
                'reviewStats' => $this->getReviewStats($startDate, $endDate),
                'revenueAndOccupancyTrend' => $this->getRevenueAndOccupancyTrendData(),
                'revenueByRoomTypeChart' => $this->getRevenueByRoomTypeChartData($startDate, $endDate),
                'todaysActivity' => $this->getTodaysActivityData(),
                'latestNews' => $this->getLatestNews(),
                'latestComments' => $this->getLatestComments(),
                'filterRange' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Lỗi nghiêm trọng trong getSystemOverview: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Đã xảy ra lỗi server khi lấy dữ liệu tổng quan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xuất dữ liệu tổng quan ra file PDF.
     * Sử dụng cùng logic ngày tháng với getSystemOverview để đảm bảo tính nhất quán.
     */
    public function exportPdf(Request $request)
    {
        try {
            list($startDate, $endDate) = $this->getDateRangeFromRequest($request);

            $data = [
                'kpis' => $this->getKpiData($startDate, $endDate),
                'reviewStats' => $this->getReviewStats($startDate, $endDate),
                'revenueByRoomTypeChart' => $this->getRevenueByRoomTypeChartData($startDate, $endDate),
                'filterRange' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ]
            ];
            
            $pdf = PDF::loadView('reports.dashboard', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');

            $fileName = 'bao-cao-thang-' . $startDate->format('m-Y') . '.pdf';

            return $pdf->download($fileName);

        } catch (Exception $e) {
            Log::error('Lỗi khi xuất PDF: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Không thể tạo báo cáo PDF.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xác thực và lấy khoảng thời gian từ request.
     * Đây là hàm trung tâm để xử lý logic ngày tháng một cách linh hoạt.
     */
    private function getDateRangeFromRequest(Request $request): array
    {
        $validated = $request->validate([
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
        ]);

        $startDate = Carbon::parse($validated['startDate'])->startOfDay();
        // Lấy ngày cuối tháng được yêu cầu từ frontend
        $requestedEndDate = Carbon::parse($validated['endDate'])->endOfDay();
        $today = Carbon::now()->endOfDay();

        // LOGIC MỚI:
        // Nếu ngày cuối tháng được yêu cầu nằm trong tương lai (so với ngày hôm nay),
        // thì chúng ta sẽ giới hạn ngày kết thúc là ngày hôm nay.
        // Điều này xử lý cả trường hợp chọn tháng hiện tại và tháng tương lai.
        if ($requestedEndDate->isAfter($today)) {
            $endDate = $today;
        } else {
            // Nếu không, đó là một tháng trong quá khứ, giữ nguyên ngày cuối tháng.
            $endDate = $requestedEndDate;
        }

        return [$startDate, $endDate];
    }

    /**
     * Tính toán các chỉ số KPI chính.
     */
    private function getKpiData(Carbon $startDate, Carbon $endDate)
    {
        $totalRooms = Room::count();
        $daysInPeriod = $startDate->diffInDays($endDate) + 1;
        $totalAvailableRoomNights = $totalRooms > 0 ? $totalRooms * $daysInPeriod : 0;

        $totalRevenue = DB::table('booking_hotel')
            ->join('booking_room_status', 'booking_hotel.booking_id', '=', 'booking_room_status.booking_id')
            ->whereIn('booking_hotel.status', ['completed', 'checked_in'])
            ->whereBetween('booking_room_status.created_at', [$startDate, $endDate])
            ->sum('booking_room_status.total_paid');
            
        $bookingsInPeriod = DB::table('booking_hotel')
            ->whereIn('status', ['completed', 'checked_in'])
            ->where('check_in_date', '<', $endDate->copy()->addDay()) // Dùng < ngày hôm sau để bao gồm cả ngày cuối
            ->where('check_out_date', '>', $startDate)
            ->get();
            
        $totalOccupiedRoomNights = 0;
        foreach ($bookingsInPeriod as $booking) {
            $checkIn = Carbon::parse($booking->check_in_date);
            $checkOut = Carbon::parse($booking->check_out_date);
            
            // Xác định khoảng thời gian giao nhau giữa đặt phòng và khoảng báo cáo
            $effectiveStart = $checkIn->greaterThan($startDate) ? $checkIn : $startDate;
            $effectiveEnd = $checkOut->lessThan($endDate) ? $checkOut : $endDate;
            
            if ($effectiveEnd->isAfter($effectiveStart)) {
                $nights = $effectiveStart->diffInDays($effectiveEnd);
                $totalOccupiedRoomNights += $nights * $booking->total_rooms;
            }
        }

        $occupancyRate = $totalAvailableRoomNights > 0 ? ($totalOccupiedRoomNights / $totalAvailableRoomNights) * 100 : 0;
        $adr = $totalOccupiedRoomNights > 0 ? $totalRevenue / $totalOccupiedRoomNights : 0;
        
        return [
            'totalRevenue' => $totalRevenue,
            'occupancyRate' => $occupancyRate,
            'adr' => $adr,
            'bookingsCount' => BookingHotel::whereBetween('created_at', [$startDate, $endDate])->count(),
            'cancellationsCount' => CancelBooking::whereBetween('cancellation_date', [$startDate, $endDate])->count(),
            'totalRoomTypes' => RoomType::count(),
            'totalRooms' => $totalRooms,
        ];
    }

    /**
     * Lấy thống kê về đánh giá của khách hàng.
     */
    private function getReviewStats(Carbon $startDate, Carbon $endDate)
    {
        $query = CustomerReview::whereBetween('created_at', [$startDate, $endDate]);
        $averageRating = (clone $query)->avg('star_rating');
        $totalReviews = (clone $query)->count();
        $distribution = (clone $query)
            ->select('star_rating', DB::raw('count(*) as count'))
            ->groupBy('star_rating')
            ->orderBy('star_rating', 'desc')
            ->pluck('count', 'star_rating');
        $chartLabels = ['5 Sao', '4 Sao', '3 Sao', '2 Sao', '1 Sao'];
        $chartData = [
            $distribution->get(5, 0), $distribution->get(4, 0),
            $distribution->get(3, 0), $distribution->get(2, 0),
            $distribution->get(1, 0),
        ];
        return [
            'averageRating' => $averageRating,
            'totalReviews' => $totalReviews,
            'distributionChart' => ['labels' => $chartLabels, 'data' => $chartData]
        ];
    }
    
    /**
     * Lấy dữ liệu xu hướng doanh thu và tỷ lệ lấp đầy trong 12 tháng qua.
     */
    private function getRevenueAndOccupancyTrendData()
    {
        $labels = []; $revenueData = []; $occupancyData = [];
        $totalRooms = Room::count();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            $labels[] = 'T' . $date->format('n/Y');
            $revenueData[] = DB::table('booking_room_status')
                ->join('booking_hotel', 'booking_room_status.booking_id', '=', 'booking_hotel.booking_id')
                ->where('booking_hotel.status', 'completed')
                ->whereBetween('booking_room_status.created_at', [$startOfMonth, $endOfMonth])
                ->sum('booking_room_status.total_paid');
            $daysInMonth = $date->daysInMonth;
            $totalAvailableRoomNights = $totalRooms * $daysInMonth;
            $bookingsInMonth = DB::table('booking_hotel')
                ->whereIn('status', ['completed', 'checked_in'])
                ->where('check_in_date', '<=', $endOfMonth)
                ->where('check_out_date', '>', $startOfMonth)->get();
            $totalOccupiedRoomNights = 0;
            foreach ($bookingsInMonth as $booking) {
                 $checkIn = Carbon::parse($booking->check_in_date);
                 $checkOut = Carbon::parse($booking->check_out_date);
                 $start = $checkIn->greaterThan($startOfMonth) ? $checkIn : $startOfMonth;
                 $end = $checkOut->lessThan($endOfMonth) ? $checkOut : $endOfMonth;
                 $totalOccupiedRoomNights += $start->diffInDays($end) * $booking->total_rooms;
            }
            $occupancyData[] = $totalAvailableRoomNights > 0 ? ($totalOccupiedRoomNights / $totalAvailableRoomNights) * 100 : 0;
        }
        return compact('labels', 'revenueData', 'occupancyData');
    }

    /**
     * Lấy dữ liệu doanh thu theo từng loại phòng.
     */
    private function getRevenueByRoomTypeChartData(Carbon $startDate, Carbon $endDate)
    {
        $data = DB::table('booking_hotel')
            ->join($this->bookingDetailsTable, 'booking_hotel.booking_id', '=', $this->bookingDetailsTable . '.booking_id')
            ->join('rooms', $this->bookingDetailsTable . '.room_id', '=', 'rooms.room_id')
            ->join('room_types', 'rooms.type_id', '=', 'room_types.type_id')
            ->whereIn('booking_hotel.status', ['completed', 'checked_in'])
            ->where('booking_hotel.check_in_date', '<', $endDate->copy()->addDay())
            ->where('booking_hotel.check_out_date', '>', $startDate)
            ->select('room_types.type_name', DB::raw('SUM(booking_hotel.total_price / booking_hotel.total_rooms) as revenue'))
            ->groupBy('room_types.type_name')->orderBy('revenue', 'desc')
            ->pluck('revenue', 'type_name')->all();
        return ['labels' => array_keys($data), 'data' => array_values($data)];
    }
    
    /**
     * Lấy hoạt động check-in/check-out trong ngày.
     */
    private function getTodaysActivityData() {
        $today = Carbon::today();
        return [
            'checkIns' => BookingHotel::with('customer:customer_id,customer_name')->whereDate('check_in_date', $today)->whereIn('status', ['confirmed'])->get(['booking_id', 'customer_id', 'check_in_time']),
            'checkOuts' => BookingHotel::with('customer:customer_id,customer_name')->whereDate('check_out_date', $today)->whereIn('status', ['checked_in'])->get(['booking_id', 'customer_id', 'check_out_time']),
        ];
    }
    
    /**
     * Lấy 5 tin tức mới nhất.
     */
    private function getLatestNews() {
        return News::with('author:id,name')->latest('publish_date')->take(5)->get(['id', 'title', 'publish_date', 'author_id']);
    }

    /**
     * Lấy 5 bình luận mới nhất.
     */
    private function getLatestComments() {
        return NewsComment::with(['user:id,name', 'news:id,title'])->latest()->take(5)->get(['id', 'user_id', 'news_id', 'content', 'created_at']);
    }
}