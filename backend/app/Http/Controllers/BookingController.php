<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['room', 'table', 'menu'])->get();
        return response()->json(['data' => $bookings], 200);
    }

    public function show($id)
    {
        $booking = Booking::with(['room', 'table', 'menu'])->where('booking_id', $id)->firstOrFail();
        return response()->json(['data' => $booking], 200);
    }

    public function store(Request $request)
    {
        Log::info('Store booking request', $request->all());

        $rules = [
            'booking_type' => 'required|in:room,table',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|regex:/^\d{10,11}$/',
            'quantity' => 'required|integer|min:1',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i:s',
            'status' => 'nullable|in:pending,confirmed,cancelled',
            'menu_id' => 'nullable|exists:menus,menu_id',
            'note' => 'nullable|string',
        ];

        if ($request->booking_type === 'room') {
            $rules['check_in_date'] = 'required|date|after_or_equal:today';
            $rules['check_out_date'] = 'required|date|after:check_in_date';
            $rules['room_id'] = 'required|exists:rooms,room_id';
            $rules['table_id'] = 'nullable';
        } elseif ($request->booking_type === 'table') {
            $rules['table_id'] = 'required|exists:tables,table_id';
            $rules['room_id'] = 'nullable';
            $rules['check_in_date'] = 'nullable';
            $rules['check_out_date'] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->booking_type === 'room') {
                $room = Room::findOrFail($request->room_id);
                if ($room->status !== 'Trống') {
                    return response()->json(['message' => 'Phòng đã được đặt hoặc đang chờ'], 422);
                }
                if ($room->capacity < $request->quantity) {
                    return response()->json(['message' => 'Số lượng khách vượt quá sức chứa phòng'], 422);
                }
            } elseif ($request->booking_type === 'table') {
                $table = Table::findOrFail($request->table_id);
                if ($table->status !== 'Trống') {
                    return response()->json(['message' => 'Bàn đã được đặt hoặc đang chờ'], 422);
                }
                if ($table->capacity < $request->quantity) {
                    return response()->json(['message' => 'Số lượng khách vượt quá sức chứa bàn'], 422);
                }
            }

            $booking = Booking::create([
                'booking_type' => $request->booking_type,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'quantity' => $request->quantity,
                'room_id' => $request->room_id,
                'table_id' => $request->table_id,
                'menu_id' => $request->menu_id,
                'note' => $request->note,
                'status' => $request->status ?? 'pending'
            ]);

            if ($request->booking_type === 'room' && $request->room_id) {
                $room->update(['status' => 'Chờ xác nhận']);
            } elseif ($request->booking_type === 'table' && $request->table_id) {
                $table->update(['status' => 'Chờ xác nhận']);
            }

            Log::info('Booking created', ['booking_id' => $booking->booking_id]);

            return response()->json([
                'message' => 'Booking created successfully',
                'booking_id' => $booking->booking_id,
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create booking', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Lưu booking thất bại: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update booking request', array_merge($request->all(), ['id' => $id]));

        $booking = Booking::findOrFail($id);

        $rules = [
            'booking_type' => 'required|in:room,table',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|regex:/^\d{10,11}$/',
            'quantity' => 'required|integer|min:1',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i:s',
            'status' => 'nullable|in:pending,confirmed,cancelled',
            'menu_id' => 'nullable|exists:menus,menu_id',
            'note' => 'nullable|string',
        ];

        if ($request->booking_type === 'room') {
            $rules['check_in_date'] = 'required|date|after_or_equal:today';
            $rules['check_out_date'] = 'required|date|after:check_in_date';
            $rules['room_id'] = 'required|exists:rooms,room_id';
            $rules['table_id'] = 'nullable';
        } elseif ($request->booking_type === 'table') {
            $rules['table_id'] = 'required|exists:tables,table_id';
            $rules['room_id'] = 'nullable';
            $rules['check_in_date'] = 'nullable';
            $rules['check_out_date'] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->booking_type === 'room' && $request->room_id && $booking->room_id != $request->room_id) {
                $room = Room::findOrFail($request->room_id);
                if ($room->status !== 'Trống') {
                    return response()->json(['message' => 'Phòng đã được đặt hoặc không tồn tại'], 422);
                }
                if ($room->capacity < $request->quantity) {
                    return response()->json(['message' => 'Số lượng khách vượt quá sức chứa phòng'], 422);
                }
            } elseif ($request->booking_type === 'table' && $request->table_id && $booking->table_id != $request->table_id) {
                $table = Table::findOrFail($request->table_id);
                if ($table->status !== 'Trống') {
                    return response()->json(['message' => 'Bàn đã được đặt hoặc không tồn tại'], 422);
                }
                if ($table->capacity < $request->quantity) {
                    return response()->json(['message' => 'Số lượng khách vượt quá sức chứa bàn'], 422);
                }
            }

            $booking->update([
                'booking_type' => $request->booking_type,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'quantity' => $request->quantity,
                'room_id' => $request->room_id,
                'table_id' => $request->table_id,
                'menu_id' => $request->menu_id,
                'note' => $request->note,
                'status' => $request->status ?? $booking->status
            ]);

            if ($request->booking_type === 'room' && $request->room_id && $booking->room_id != $request->room_id) {
                Room::find($request->room_id)->update(['status' => 'Chờ xác nhận']);
                if ($booking->room_id) {
                    Room::find($booking->room_id)->update(['status' => 'Trống']);
                }
            } elseif ($request->booking_type === 'table' && $request->table_id && $booking->table_id != $request->table_id) {
                Table::find($request->table_id)->update(['status' => 'Chờ xác nhận']);
                if ($booking->table_id) {
                    Table::find($booking->table_id)->update(['status' => 'Trống']);
                }
            }

            Log::info('Booking updated', ['booking_id' => $booking->booking_id]);

            return response()->json([
                'message' => 'Booking updated successfully',
                'booking_id' => $booking->booking_id,
                'data' => $booking
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update booking', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Cập nhật booking thất bại: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $booking = Booking::findOrFail($id);

            // Không cần cập nhật trạng thái room/table vì đã xử lý trong confirm (cancel)
            $booking->delete();

            Log::info('Booking deleted', ['booking_id' => $id]);

            return response()->json([
                'message' => 'Booking deleted successfully',
                'booking_id' => $id
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete booking', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Xóa booking thất bại: ' . $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:confirmed,cancelled'
            ]);

            if ($validator->fails()) {
                Log::warning('Dữ liệu không hợp lệ khi cập nhật trạng thái', [
                    'booking_id' => $id,
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $booking = Booking::findOrFail($id);
            Log::info('Cập nhật trạng thái booking', [
                'booking_id' => $id,
                'current_status' => $booking->status,
                'new_status' => $request->input('status')
            ]);

            $booking->status = $request->input('status');
            $booking->save();

            if ($request->input('status') === 'confirmed') {
                if ($booking->booking_type === 'room' && $booking->room_id) {
                    $room = Room::find($booking->room_id);
                    if ($room) {
                        Log::info('Cập nhật trạng thái phòng', [
                            'room_id' => $room->id,
                            'current_status' => $room->status,
                            'new_status' => 'Đã đặt'
                        ]);
                        $room->status = 'Đã đặt';
                        $room->save();
                    } else {
                        Log::warning('Không tìm thấy phòng', ['room_id' => $booking->room_id]);
                    }
                } elseif ($booking->booking_type === 'table' && $booking->table_id) {
                    $table = Table::find($booking->table_id);
                    if ($table) {
                        Log::info('Cập nhật trạng thái bàn', [
                            'table_id' => $table->id,
                            'current_status' => $table->status,
                            'new_status' => 'Đã đặt'
                        ]);
                        $table->status = 'Đã đặt';
                        $table->save();
                    } else {
                        Log::warning('Không tìm thấy bàn', ['table_id' => $booking->table_id]);
                    }
                }
            } elseif ($request->input('status') === 'cancelled') {
                if ($booking->booking_type === 'room' && $booking->room_id) {
                    $room = Room::find($booking->room_id);
                    if ($room) {
                        Log::info('Cập nhật trạng thái phòng khi hủy', [
                            'room_id' => $room->id,
                            'current_status' => $room->status,
                            'new_status' => 'Trống'
                        ]);
                        $room->status = 'Trống';
                        $room->save();
                    } else {
                        Log::warning('Không tìm thấy phòng', ['room_id' => $booking->room_id]);
                    }
                } elseif ($booking->booking_type === 'table' && $booking->table_id) {
                    $table = Table::find($booking->table_id);
                    if ($table) {
                        Log::info('Cập nhật trạng thái bàn khi hủy', [
                            'table_id' => $table->id,
                            'current_status' => $table->status,
                            'new_status' => 'Trống'
                        ]);
                        $table->status = 'Trống';
                        $table->save();
                    } else {
                        Log::warning('Không tìm thấy bàn', ['table_id' => $booking->table_id]);
                    }
                }
            }

            return response()->json([
                'message' => 'Cập nhật trạng thái booking thành công',
                'data' => $booking
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật trạng thái booking', [
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Cập nhật trạng thái thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}