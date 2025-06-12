<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Table;
use App\Models\Menu;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use DateTime;

class BookingController extends Controller
{
    public function index()
    {
        try {
            $bookings = Booking::with(['room.season', 'table', 'menu'])->get();
            return response()->json(['data' => $bookings], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching bookings: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi lấy danh sách booking', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'booking_type' => 'required|in:room,table',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|regex:/^\d{10,11}$/',
                'check_in_date' => 'required_if:booking_type,room|nullable|date',
                'check_out_date' => 'required_if:booking_type,room|nullable|date|after:check_in_date',
                'booking_date' => 'required|date',
                'booking_time' => 'required|date_format:H:i:s',
                'quantity' => 'required|integer|min:1',
                'room_id' => 'required_if:booking_type,room|nullable|exists:rooms,room_id',
                'table_id' => 'required_if:booking_type,table|nullable|exists:tables,table_id',
                'menu_id' => 'nullable|exists:menus,menu_id',
                'note' => 'nullable|string',
                'status' => 'required|in:pending,confirmed,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
            }

            // Bắt đầu transaction
            DB::beginTransaction();

            // Tạo booking
            $booking = Booking::create($request->all());

            // Cập nhật trạng thái phòng/bàn
            if ($booking->booking_type === 'room' && $booking->room_id) {
                $room = Room::findOrFail($booking->room_id);
                if ($room->status !== 'Trống') {
                    DB::rollBack();
                    return response()->json(['message' => 'Phòng không ở trạng thái trống'], 422);
                }
                $room->status = $booking->status === 'pending' ? 'Chờ xác nhận' : 'Đã đặt';
                $room->save();
            } elseif ($booking->booking_type === 'table' && $booking->table_id) {
                $table = Table::findOrFail($booking->table_id);
                if ($table->status !== 'Trống') {
                    DB::rollBack();
                    return response()->json(['message' => 'Bàn không ở trạng thái trống'], 422);
                }
                $table->status = $booking->status === 'pending' ? 'Chờ xác nhận' : 'Đã đặt';
                $table->save();
            }

            // Commit transaction
            DB::commit();

            return response()->json(['data' => $booking, 'message' => 'Booking tạo thành công'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating booking: ' . $e->getMessage(), ['request' => $request->all(), 'exception' => $e]);
            return response()->json(['message' => 'Lỗi khi tạo booking', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            $validator = Validator::make($request->all(), [
                'booking_type' => 'required|in:room,table',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|regex:/^\d{10,11}$/',
                'check_in_date' => 'required_if:booking_type,room|nullable|date',
                'check_out_date' => 'required_if:booking_type,room|nullable|date|after:check_in_date',
                'booking_date' => 'required|date',
                'booking_time' => 'required|date_format:H:i:s',
                'quantity' => 'required|integer|min:1',
                'room_id' => 'required_if:booking_type,room|nullable|exists:rooms,room_id',
                'table_id' => 'required_if:booking_type,table|nullable|exists:tables,table_id',
                'menu_id' => 'nullable|exists:menus,menu_id',
                'note' => 'nullable|string',
                'status' => 'required|in:pending,confirmed,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
            }

            // Bắt đầu transaction
            DB::beginTransaction();

            // Lấy trạng thái cũ của booking
            $oldStatus = $booking->status;
            $oldRoomId = $booking->room_id;
            $oldTableId = $booking->table_id;

            // Cập nhật booking
            $booking->update($request->all());

            // Cập nhật trạng thái phòng/bàn
            if ($booking->booking_type === 'room' && $booking->room_id) {
                $room = Room::findOrFail($booking->room_id);
                if ($booking->status === 'confirmed') {
                    $room->status = 'Đã đặt';
                } elseif ($booking->status === 'cancelled') {
                    $room->status = 'Trống';
                } elseif ($booking->status === 'pending') {
                    $room->status = 'Chờ xác nhận';
                }
                $room->save();

                // Nếu thay đổi phòng, đặt lại trạng thái phòng cũ
                if ($oldRoomId && $oldRoomId !== $booking->room_id) {
                    $oldRoom = Room::findOrFail($oldRoomId);
                    $oldRoom->status = 'Trống';
                    $oldRoom->save();
                }
            } elseif ($booking->booking_type === 'table' && $booking->table_id) {
                $table = Table::findOrFail($booking->table_id);
                if ($booking->status === 'confirmed') {
                    $table->status = 'Đã đặt';
                } elseif ($booking->status === 'cancelled') {
                    $table->status = 'Trống';
                } elseif ($booking->status === 'pending') {
                    $table->status = 'Chờ xác nhận';
                }
                $table->save();

                // Nếu thay đổi bàn, đặt lại trạng thái bàn cũ
                if ($oldTableId && $oldTableId !== $booking->table_id) {
                    $oldTable = Table::findOrFail($oldTableId);
                    $oldTable->status = 'Trống';
                    $oldTable->save();
                }
            }

            // Nếu trạng thái cũ là confirmed hoặc pending và giờ là cancelled, đặt lại trạng thái phòng/bàn cũ
            if (($oldStatus === 'confirmed' || $oldStatus === 'pending') && $booking->status === 'cancelled') {
                if ($oldRoomId && !$booking->room_id) {
                    $oldRoom = Room::findOrFail($oldRoomId);
                    $oldRoom->status = 'Trống';
                    $oldRoom->save();
                } elseif ($oldTableId && !$booking->table_id) {
                    $oldTable = Table::findOrFail($oldTableId);
                    $oldTable->status = 'Trống';
                    $oldTable->save();
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json(['data' => $booking, 'message' => 'Booking cập nhật thành công'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Không tìm thấy booking'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating booking: ' . $e->getMessage(), ['booking_id' => $booking_id, 'request' => $request->all(), 'exception' => $e]);
            return response()->json(['message' => 'Lỗi khi cập nhật booking', 'error' => $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request, $booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:confirmed,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
            }

            // Bắt đầu transaction
            DB::beginTransaction();

            // Cập nhật trạng thái booking
            $booking->status = $request->status;
            $booking->save();

            // Cập nhật trạng thái phòng/bàn
            if ($booking->booking_type === 'room' && $booking->room_id) {
                $room = Room::findOrFail($booking->room_id);
                $room->status = $request->status === 'confirmed' ? 'Đã đặt' : 'Trống';
                $room->save();
            } elseif ($booking->booking_type === 'table' && $booking->table_id) {
                $table = Table::findOrFail($booking->table_id);
                $table->status = $request->status === 'confirmed' ? 'Đã đặt' : 'Trống';
                $table->save();
            }

            // Commit transaction
            DB::commit();

            return response()->json(['data' => $booking, 'message' => 'Cập nhật trạng thái booking thành công'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Không tìm thấy booking hoặc phòng/bàn'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming booking: ' . $e->getMessage(), ['booking_id' => $booking_id, 'request' => $request->all(), 'exception' => $e]);
            return response()->json(['message' => 'Lỗi khi cập nhật trạng thái booking', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);

            // Bắt đầu transaction
            DB::beginTransaction();

            // Cập nhật trạng thái phòng/bàn về Trống
            if ($booking->booking_type === 'room' && $booking->room_id) {
                $room = Room::findOrFail($booking->room_id);
                $room->status = 'Trống';
                $room->save();
            } elseif ($booking->booking_type === 'table' && $booking->table_id) {
                $table = Table::findOrFail($booking->table_id);
                $table->status = 'Trống';
                $table->save();
            }

            // Xóa booking
            $booking->delete();

            // Commit transaction
            DB::commit();

            return response()->json(['message' => 'Xóa booking thành công'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Không tìm thấy booking'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting booking: ' . $e->getMessage(), ['booking_id' => $booking_id, 'exception' => $e]);
            return response()->json(['message' => 'Lỗi khi xóa booking', 'error' => $e->getMessage()], 500);
        }
    }

    public function getFormData()
    {
        try {
            $rooms = Room::with(['roomType', 'season'])->get();
            $tables = Table::all();
            $menus = Menu::all();
            $seasons = Season::all();

            Log::info('Form data fetched:', [
                'rooms_count' => $rooms->count(),
                'tables_count' => $tables->count(),
                'menus_count' => $menus->count(),
                'seasons_count' => $seasons->count()
            ]);

            return response()->json([
                'data' => [
                    'rooms' => $rooms,
                    'tables' => $tables,
                    'menus' => $menus,
                    'seasons' => $seasons
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching form data: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi lấy dữ liệu form', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($booking_id)
    {
        try {
            $booking = Booking::with(['room.season', 'table', 'menu'])->findOrFail($booking_id);
            return response()->json(['data' => $booking], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy booking'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching booking: ' . $e->getMessage(), ['booking_id' => $booking_id, 'exception' => $e]);
            return response()->json(['message' => 'Lỗi khi lấy thông tin booking', 'error' => $e->getMessage()], 500);
        }
    }

    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_date' => 'required|date',
            'booking_time' => 'required|date_format:H:i:s',
            'room_id' => 'required_without:table_id|nullable|exists:rooms,room_id',
            'table_id' => 'required_without:room_id|nullable|exists:tables,table_id',
            'check_in_date' => 'required_if:room_id,!=,null|nullable|date|after_or_equal:booking_date',
            'check_out_date' => 'required_if:room_id,!=,null|nullable|date|after:check_in_date',
            'booking_id' => 'nullable|exists:bookings,booking_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $bookingDateTime = new DateTime($request->booking_date . ' ' . $request->booking_time);

            if ($request->room_id) {
                $overlappingBookings = Booking::where('room_id', $request->room_id)
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($request) {
                        $q->whereBetween('check_in_date', [$request->check_in_date, $request->check_out_date])
                          ->orWhereBetween('check_out_date', [$request->check_in_date, $request->check_out_date])
                          ->orWhere('check_in_date', '<=', $request->check_in_date)
                          ->where('check_out_date', '>=', $request->check_out_date);
                    })
                    ->where('booking_id', '!=', $request->booking_id)
                    ->exists();

                if ($overlappingBookings) {
                    return response()->json(['available' => false, 'message' => 'Phòng không khả dụng trong khoảng thời gian này'], 422);
                }

                $room = Room::findOrFail($request->room_id);
                if ($room->status !== 'Trống' && (!$request->booking_id || $room->status !== 'Chờ xác nhận')) {
                    return response()->json(['available' => false, 'message' => 'Phòng không ở trạng thái trống'], 422);
                }
            } elseif ($request->table_id) {
                $overlappingBookings = Booking::where('table_id', $request->table_id)
                    ->where('status', '!=', 'cancelled')
                    ->where('booking_date', $request->booking_date)
                    ->where('booking_time', $request->booking_time)
                    ->where('booking_id', '!=', $request->booking_id)
                    ->exists();

                if ($overlappingBookings) {
                    return response()->json(['available' => false, 'message' => 'Bàn không khả dụng tại thời điểm này'], 422);
                }

                $table = Table::findOrFail($request->table_id);
                if ($table->status !== 'Trống' && (!$request->booking_id || $table->status !== 'Chờ xác nhận')) {
                    return response()->json(['available' => false, 'message' => 'Bàn không ở trạng thái trống'], 422);
                }
            }

            return response()->json(['available' => true, 'message' => 'Trống để đặt'], 200);
        } catch (\Exception $e) {
            Log::error('Error checking availability: ' . $e->getMessage(), ['request' => $request->all(), 'exception' => $e]);
            return response()->json(['message' => 'Lỗi khi kiểm tra lịch trống', 'error' => $e->getMessage()], 500);
        }
    }
}
