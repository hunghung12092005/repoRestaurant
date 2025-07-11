<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingHotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Lấy tất cả dữ liệu thống kê cho trang dashboard.
     */
    public function getStats()
    {
        return response()->json([
            'statsCards' => $this->getStatsCardData(),
            'roomAvailability' => $this->getRoomAvailabilityData(),
            'reservationChart' => $this->getReservationChartData(),
        ]);
    }

    /**
     * Tính toán phần trăm thay đổi.
     * @param float $current
     * @param float $previous
     * @return float
     */
    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            // Nếu giá trị trước đó là 0, không thể chia, trả về 100% nếu hiện tại > 0
            return $current > 0 ? 100.0 : 0.0;
        }
        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Dữ liệu cho các thẻ thống kê.
     */
    private function getStatsCardData()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // 1. New Booking
        $newBookingsToday = BookingHotel::whereDate('created_at', $today)->count();
        $newBookingsYesterday = BookingHotel::whereDate('created_at', $yesterday)->count();
        $newBookingChange = $this->calculatePercentageChange($newBookingsToday, $newBookingsYesterday);

        // 2. Available Rooms
        $totalRooms = Room::count();
        $availableRooms = Room::where('status', 'available')->count();
        // Giả sử "thay đổi" ở đây là tỷ lệ phòng trống trên tổng số phòng
        $availableRoomsChange = $totalRooms > 0 ? ($availableRooms / $totalRooms) * 100 : 0;

        // 3. Revenue
        $revenueToday = BookingHotel::whereDate('created_at', $today)
            ->whereIn('payment_status', ['paid', 'completed']) // Chỉ tính doanh thu đã thanh toán
            ->sum('total_price');
        $revenueYesterday = BookingHotel::whereDate('created_at', $yesterday)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->sum('total_price');
        $revenueChange = $this->calculatePercentageChange($revenueToday, $revenueYesterday);

        // 4. Checkout
        $checkoutsToday = BookingHotel::whereDate('check_out_date', $today)
             ->where('status', '!=', 'cancelled') // Loại bỏ các booking đã hủy
             ->count();
        $checkoutsYesterday = BookingHotel::whereDate('check_out_date', $yesterday)
             ->where('status', '!=', 'cancelled')
             ->count();
        $checkOutChange = $this->calculatePercentageChange($checkoutsToday, $checkoutsYesterday);

        return [
            'newBooking' => ['value' => $newBookingsToday, 'change' => round($newBookingChange, 1)],
            'availableRooms' => ['value' => $availableRooms, 'change' => round($availableRoomsChange, 1)],
            'revenue' => ['value' => $revenueToday, 'change' => round($revenueChange, 1)],
            'checkOut' => ['value' => $checkoutsToday, 'change' => round($checkOutChange, 1)],
        ];
    }

    /**
     * Dữ liệu cho biểu đồ tròn Room Availability.
     */
    private function getRoomAvailabilityData()
    {
        $stats = Room::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        // Mặc định các giá trị để đảm bảo chúng luôn tồn tại
        $defaults = ['occupied' => 0, 'reserved' => 0, 'available' => 0, 'maintenance' => 0, 'cleaning' => 0];
        $stats = array_merge($defaults, $stats);
        
        // Nhóm các trạng thái "Không sẵn sàng"
        $notReadyCount = ($stats['maintenance'] ?? 0) + ($stats['cleaning'] ?? 0);

        return [
            'labels' => ['Occupied', 'Reserved', 'Available', 'Not Ready'],
            'data' => [
                $stats['occupied'],
                $stats['reserved'],
                $stats['available'],
                $notReadyCount
            ]
        ];
    }

    /**
     * Dữ liệu cho biểu đồ cột Reservation.
     */
    private function getReservationChartData()
    {
        $labels = [];
        $bookedData = [];
        $canceledData = [];
        $endDate = Carbon::today();

        for ($i = 7; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');

            // Số lượng đặt phòng được TẠO vào ngày này
            $bookedData[] = BookingHotel::whereDate('created_at', $date)->count();
            
            // Số lượng đặt phòng được HỦY vào ngày này
            // Giả sử khi hủy, status đổi thành 'cancelled' và updated_at được cập nhật
            $canceledData[] = BookingHotel::where('status', 'cancelled')
                ->whereDate('updated_at', $date)
                ->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Booked',
                    'data' => $bookedData,
                    'backgroundColor' => '#6a0dad',
                    'borderRadius' => 8,
                    'barThickness' => 25,
                    'stack' => 'Stack 0'
                ],
                [
                    'label' => 'Canceled',
                    'data' => $canceledData,
                    'backgroundColor' => '#f4a261',
                    'borderRadius' => 8,
                    'barThickness' => 25,
                    'stack' => 'Stack 0'
                ]
            ]
        ];
    }
}