<?php

namespace App\Http\Controllers;

use App\Models\BookingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingHistoryController extends Controller
{
    /**
     * Lấy danh sách lịch sử booking
     * GET /api/booking-histories
     */
    public function index(Request $request)
    {
        try {
            $query = BookingHistory::with(['room', 'customer']);

            // Lọc theo ngày
            if ($request->has('date')) {
                $date = $request->input('date');
                $query->whereDate('check_in', '<=', $date)
                      ->whereDate('check_out', '>=', $date);
            }

            $bookings = $query->get()->map(function ($booking) {
                return [
                    'status_id' => $booking->status_id,
                    'booking_id' => $booking->booking_id,
                    'customer' => $booking->customer ? [
                        'customer_name' => $booking->customer->customer_name,
                        'customer_phone' => $booking->customer->customer_phone,
                        'customer_email' => $booking->customer->customer_email,
                        'address' => $booking->customer->address
                    ] : null,
                    'room' => $booking->room ? [
                        'room_name' => $booking->room->room_name,
                        'type_name' => $booking->room->type_name,
                        'floor_number' => $booking->room->floor_number
                    ] : null,
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                    'room_price' => $booking->room_price,
                    'service_price' => $booking->service_price,
                    'total_paid' => $booking->total_paid,
                    'surcharge' => $booking->surcharge,
                    'surcharge_reason' => $booking->surcharge_reason
                ];
            });

            return response()->json($bookings, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách lịch sử booking: ' . $e->getMessage());
            return response()->json(['message' => 'Không thể lấy danh sách lịch sử booking.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy chi tiết booking
     * GET /api/booking-histories/{status_id}
     */
    public function show($status_id, Request $request)
    {
        try {
            $booking = BookingHistory::with(['room', 'customer'])
                ->findOrFail($status_id);

            $data = [
                'status_id' => $booking->status_id,
                'booking_id' => $booking->booking_id,
                'customer' => $booking->customer ? [
                    'customer_name' => $booking->customer->customer_name,
                    'customer_phone' => $booking->customer->customer_phone,
                    'customer_email' => $booking->customer->customer_email,
                    'address' => $booking->customer->address
                ] : null,
                'room' => $booking->room ? [
                    'room_name' => $booking->room->room_name,
                    'type_name' => $booking->room->type_name,
                    'floor_number' => $booking->room->floor_number
                ] : null,
                'check_in' => $booking->check_in,
                'check_out' => $booking->check_out,
                'room_price' => $booking->room_price,
                'service_price' => $booking->service_price,
                'total_paid' => $booking->total_paid,
                'surcharge' => $booking->surcharge,
                'surcharge_reason' => $booking->surcharge_reason
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết booking: ' . $e->getMessage());
            return response()->json(['message' => 'Không tìm thấy booking.', 'error' => $e->getMessage()], 404);
        }
    }
}