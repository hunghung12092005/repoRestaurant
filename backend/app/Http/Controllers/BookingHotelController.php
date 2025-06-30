<?php

namespace App\Http\Controllers;

use App\Models\BookingHotel;
use App\Models\BookingHotelDetail;
use App\Models\BookingHotelService;
use App\Models\Room;
use App\Models\Service;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingHotelController extends Controller
{
    public function getBookings(Request $request)
    {
        try {
            $status = $request->query('status', 'pending_confirmation');
            $bookings = BookingHotel::with(['customer', 'details.room.roomType'])
                ->where('status', $status)
                ->get()
                ->map(function ($booking) {
                    $firstDetail = $booking->details->first();
                    $booking->type_id = $firstDetail ? ($firstDetail->room ? $firstDetail->room->type_id : $booking->type_id) : null;
                    $booking->so_phong_dich_vu = $booking->so_phong_dich_vu ?? 0;
                    return $booking;
                });

            Log::info('Số lượng booking lấy được: ' . $bookings->count());

            return response()->json($bookings);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể lấy danh sách booking: ' . $e->getMessage()], 500);
        }
    }

    public function getBookingDetails($bookingId)
    {
        try {
            $details = BookingHotelDetail::with(['room', 'room.roomType'])
                ->where('booking_id', $bookingId)
                ->get()
                ->map(function ($detail) {
                    $roomType = $detail->room ? $detail->room->roomType : null;
                    $booking = BookingHotel::find($detail->booking_id);
                    $checkInDate = $booking ? $booking->check_in_date : now()->toDateString();
                    $price = $roomType ? $roomType->prices()
                        ->where('start_date', '<=', $checkInDate)
                        ->where('end_date', '>=', $checkInDate)
                        ->orderBy('priority', 'desc')
                        ->first() : null;
                    $gia_phong = $price ? $price->price_per_night : ($detail->gia_phong ?? 0);

                    Log::info('BookingDetail - booking_detail_id: ' . $detail->booking_detail_id . ', roomType: ' . ($roomType ? $roomType->type_name : 'null') . ', gia_phong: ' . $gia_phong);

                    return [
                        'booking_detail_id' => $detail->booking_detail_id,
                        'room_id' => $detail->room_id,
                        'initial_room_type' => $roomType ? $roomType->type_name : 'Chưa chọn loại phòng',
                        'gia_phong' => $gia_phong,
                        'room' => $detail->room,
                        'note' => $detail->note,
                    ];
                });

            return response()->json($details);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết booking (bookingId: ' . $bookingId . '): ' . $e->getMessage());
            return response()->json(['error' => 'Không thể lấy chi tiết booking: ' . $e->getMessage()], 500);
        }
    }

    public function getBookingServices($bookingId)
    {
        try {
            $services = BookingHotelService::whereIn('booking_detail_id', function ($query) use ($bookingId) {
                $query->select('booking_detail_id')
                      ->from('booking_hotel_detail')
                      ->where('booking_id', $bookingId);
            })->get();

            return response()->json($services);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể lấy dịch vụ booking: ' . $e->getMessage()], 500);
        }
    }

    public function getAvailableRooms(Request $request)
    {
        try {
            $checkIn = $request->query('check_in');
            $checkOut = $request->query('check_out');
            $typeId = $request->query('type_id'); // Lọc theo type_id

            $bookedRoomIds = BookingHotelDetail::whereIn('booking_id', function ($query) use ($checkIn, $checkOut) {
                $query->select('booking_id')
                      ->from('booking_hotel')
                      ->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>', $checkIn)
                      ->where('status', 'booked');
            })->whereNotNull('room_id')->pluck('room_id');

            $rooms = Room::with('roomType')
                ->whereNotIn('room_id', $bookedRoomIds)
                ->where('status', 'available')
                ->when($typeId, function ($query) use ($typeId) {
                    return $query->where('type_id', $typeId);
                })
                ->select('room_id as id', 'room_name as name', 'type_id')
                ->get();

            return response()->json($rooms);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể lấy danh sách phòng trống: ' . $e->getMessage()], 500);
        }
    }

    public function assignRoom($bookingDetailId, Request $request)
    {
        try {
            $request->validate([
                'room_id' => 'required|integer|exists:rooms,room_id',
            ]);

            $detail = BookingHotelDetail::findOrFail($bookingDetailId);
            $detail->room_id = $request->room_id;
            $detail->save();

            $room = Room::findOrFail($request->room_id);
            $room->status = 'occupied';
            $room->save();

            return response()->json(['message' => 'Xếp phòng thành công']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể xếp phòng: ' . $e->getMessage()], 500);
        }
    }

    public function confirmBooking($bookingId, Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|in:booked,cancelled',
            ]);

            $booking = BookingHotel::findOrFail($bookingId);
            $booking->status = $request->status;
            $booking->save();

            return response()->json(['message' => 'Cập nhật trạng thái thành công']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể xác nhận booking: ' . $e->getMessage()], 500);
        }
    }

    public function storeBooking(Request $request)
    {
        try {
            $bookingDetails = $request->validate([
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date',
                'type_id' => 'required|exists:room_types,type_id',
                'total_rooms' => 'required|integer|min:1',
                'so_phong_dich_vu' => 'required|integer|min:0',
                'services_id' => 'nullable|array',
                'services_id.*' => 'exists:services,id',
                'total_price' => 'required|numeric|min:0',
                'phone' => 'required|string',
                'fullName' => 'required|string',
                'paymentMethod' => 'required|string',
                'booking_type' => 'required|in:online,offline',
                'pricing_type' => 'required|in:nightly,weekly,monthly',
                'note' => 'nullable|string',
                'gia_1phong' => 'required|numeric|min:0',
                'gia_1phong_dich_vu' => 'required|numeric|min:0',
            ]);

            $customer = Customer::firstOrCreate(
                ['customer_phone' => $bookingDetails['phone']],
                [
                    'customer_name' => $bookingDetails['fullName'],
                    'customer_email' => null,
                    'address' => null,
                    'room_id' => null,
                ]
            );

            $booking = BookingHotel::create([
                'customer_id' => $customer->customer_id,
                'booking_type' => $bookingDetails['booking_type'],
                'pricing_type' => $bookingDetails['pricing_type'],
                'check_in_date' => $bookingDetails['check_in_date'],
                'check_out_date' => $bookingDetails['check_out_date'],
                'total_rooms' => $bookingDetails['total_rooms'],
                'so_phong_dich_vu' => $bookingDetails['so_phong_dich_vu'], // Lưu số phòng dùng dịch vụ
                'total_price' => $bookingDetails['total_price'],
                'payment_status' => 'pending',
                'status' => 'pending_confirmation',
                'note' => $bookingDetails['note'],
            ]);

            for ($i = 0; $i < $bookingDetails['total_rooms']; $i++) {
                $isServiceUsed = $i < $bookingDetails['so_phong_dich_vu'];
                $giaPhong = $isServiceUsed ? $bookingDetails['gia_1phong_dich_vu'] : $bookingDetails['gia_1phong'];

                $bookingDetail = BookingHotelDetail::create([
                    'booking_id' => $booking->booking_id,
                    'room_id' => null,
                    'gia_phong' => $giaPhong,
                    'total_price' => $giaPhong,
                    'note' => $bookingDetails['note'],
                ]);

                if ($isServiceUsed && !empty($bookingDetails['services_id'])) {
                    foreach ($bookingDetails['services_id'] as $serviceId) {
                        $service = Service::find($serviceId);
                        if ($service) {
                            BookingHotelService::create([
                                'booking_detail_id' => $bookingDetail->booking_detail_id,
                                'service_id' => $serviceId,
                                'service_price' => $service->price,
                            ]);
                        }
                    }
                }
            }

            return response()->json(['message' => 'Booking created successfully!'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
