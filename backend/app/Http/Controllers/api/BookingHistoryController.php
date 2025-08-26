<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BookingHistory;
use App\Models\BookingHotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingHistoryController extends Controller
{
    /**
     * Lấy danh sách lịch sử booking
     */
    public function index(Request $request)
    {
        try {
            $query = BookingHotel::with([
                'customer',
                'historyRecords' => function ($q) {
                    $q->with([
                        'room.roomType',
                        'bookingDetail.services.service'
                    ]);
                },
            ])
            ->whereIn('status', ['completed'])
            ->orderByDesc('booking_id');

            if ($request->filled('date')) {
                $date = $request->input('date');
                $query->whereHas('historyRecords', function ($q) use ($date) {
                    $q->whereDate('check_out', $date);
                });
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_id_number', 'like', "%{$search}%");
                });
            }

            $perPage = $request->input('per_page', 10);
            $bookings = $query->paginate($perPage);

            return response()->json([
                'data' => $bookings->getCollection()->map(function ($booking) {
                    return [
                        'booking_id'    => $booking->booking_id,
                        'status'        => $booking->status,
                        'payment_method'=> $booking->payment_method,
                        'payment_status'=> $booking->payment_status,
                        'total_price'   => $booking->total_price,
                        'check_in_date' => $booking->check_in_date,
                        'customer'      => $booking->customer ? [
                            'customer_name' => $booking->customer->customer_name,
                            'customer_phone' => $booking->customer->customer_phone,
                            'customer_email' => $booking->customer->customer_email,
                            'customer_id_number' => $booking->customer->customer_id_number,
                        ] : null,
                        'used_rooms'    => $booking->historyRecords->map(function ($history) {
                    
                            $servicesForThisRoom = [];
                            if ($history->bookingDetail && $history->bookingDetail->services) {
                                $servicesForThisRoom = $history->bookingDetail->services->map(function($bookingService) {
                                    if (!$bookingService->service) {
                                        return null;
                                    }
                                    $quantity = $bookingService->quantity ?? 1;
                                    return [
                                        'service_name' => $bookingService->service->service_name,
                                        'quantity'     => $quantity,
                                        'price'        => $bookingService->service->price,
                                        'total'        => $quantity * $bookingService->service->price,
                                    ];
                                })->filter()->values()->all(); 
                            }

                            return [
                                'status_id'        => $history->status_id,
                                'room_name'        => $history->room->room_name ?? 'N/A',
                                'type_name'        => $history->room->roomType->type_name ?? 'N/A',
                                'check_in'         => $history->check_in,
                                'check_out'        => $history->check_out,
                                'room_price'       => $history->room_price,
                                'service_price'    => $history->service_price, 
                                'surcharge'        => $history->surcharge,
                                'surcharge_reason' => $history->surcharge_reason,
                                'total_paid'       => $history->total_paid, 
                                'used_services'    => $servicesForThisRoom, 
                            ];
                        }),
                    ];
                }),
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'total'        => $bookings->total(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách lịch sử booking: ' . $e->getMessage() . ' tại dòng ' . $e->getLine() . ' trong file ' . $e->getFile());
            return response()->json([
                'message' => 'Không thể lấy danh sách lịch sử booking. Vui lòng kiểm tra log.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy chi tiết booking
     */
    public function show($status_id, Request $request)
    {
        try {
            $booking = BookingHistory::with([
                'room' => function ($q) {
                    $q->select('rooms.room_id', 'rooms.room_name', 'rooms.floor_number', 'rooms.status', 'rooms.type_id')
                      ->with(['roomType' => function ($q) {
                          $q->select('type_id', 'type_name');
                      }]);
                },
                'customer',
                'booking',
                'bookingDetail'
            ])->findOrFail($status_id);

            return response()->json([
                'status_id' => $booking->status_id,
                'booking_id' => $booking->booking_id,
                'booking_detail_id' => $booking->booking_detail_id,
                'customer' => $booking->customer ? [
                    'customer_id' => $booking->customer->customer_id,
                    'customer_name' => $booking->customer->customer_name,
                    'customer_phone' => $booking->customer->customer_phone,
                    'customer_email' => $booking->customer->customer_email,
                    'address' => $booking->customer->address,
                    'customer_id_number' => $booking->customer->customer_id_number,
                    'birthday' => $booking->customer->birthday,
                ] : null,
                'room' => $booking->room ? [
                    'room_id' => $booking->room->room_id,
                    'room_name' => $booking->room->room_name,
                    'type_name' => $booking->room->roomType ? $booking->room->roomType->type_name : 'N/A',
                    'floor_number' => $booking->room->floor_number
                ] : null,
                'booking' => $booking->booking ? [
                    'payment_method' => $booking->booking->payment_method,
                    'payment_status' => $booking->booking->payment_status,
                    'booking_type' => $booking->booking->booking_type,
                    'adult' => $booking->booking->adult,
                    'child' => $booking->booking->child,
                    'total_rooms' => $booking->booking->total_rooms,
                    'total_price' => $booking->booking->total_price,
                    'additional_fee' => $booking->booking->additional_fee,
                    'status' => $booking->booking->status,
                    'note' => $booking->booking->note,
                ] : null,
                'booking_detail' => $booking->bookingDetail ? [
                    'room_type' => $booking->bookingDetail->room_type,
                    'gia_phong' => $booking->bookingDetail->gia_phong,
                    'gia_dich_vu' => $booking->bookingDetail->gia_dich_vu,
                    'total_price' => $booking->bookingDetail->total_price,
                    'note' => $booking->bookingDetail->note,
                ] : null,
                'check_in' => $booking->check_in,
                'check_out' => $booking->check_out,
                'room_price' => $booking->room_price,
                'service_price' => $booking->service_price,
                'surcharge' => $booking->surcharge,
                'surcharge_reason' => $booking->surcharge_reason,
                'total_paid' => $booking->total_paid,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết booking: ' . $e->getMessage());
            return response()->json([
                'message' => 'Không tìm thấy booking.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}