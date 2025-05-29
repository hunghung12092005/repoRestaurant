<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['room', 'table', 'menu'])->get();
        return response()->json(['data' => $bookings]);
    }

    public function show($id)
    {
        $booking = Booking::with(['room', 'table', 'menu'])->where('booking_id', $id)->firstOrFail();
        return response()->json(['data' => $booking]);
    }

  public function store(Request $request)
    {
        Log::info('Store booking request', $request->all());

        // Define base validation rules
        $rules = [
            'type' => 'required|in:room,table',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|regex:/^\d{10,11}$/',
            'quantity' => 'required|integer|min:1',
            'status' => 'nullable|in:pending,confirmed,cancelled',
            'menu_id' => 'nullable|exists:menus,menu_id',
            'note' => 'nullable|string',
        ];

        // Add conditional rules based on type
        if ($request->type === 'room') {
            Log::info('Validating room booking');
            $rules['check_in_date'] = 'required|date';
            $rules['check_out_date'] = 'required|date|after:check_in_date';
            $rules['room_id'] = 'required|exists:rooms,room_id';
            $rules['table_id'] = 'nullable';
            $rules['booking_date'] = 'nullable';
            $rules['booking_time'] = 'nullable';
        } elseif ($request->type === 'table') {
            Log::info('Validating table booking');
            $rules['booking_date'] = 'required|date';
            $rules['booking_time'] = 'required';
            $rules['table_id'] = 'required|exists:tables,table_id';
            $rules['room_id'] = 'nullable';
            $rules['check_in_date'] = 'nullable';
            $rules['check_out_date'] = 'nullable';
        } else {
            Log::error('Invalid booking type', ['type' => $request->type]);
            return response()->json(['message' => 'Loại booking không hợp lệ'], 422);
        }

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create the booking
            $booking = Booking::create($request->only([
                'type',
                'customer_name',
                'customer_phone',
                'check_in_date',
                'check_out_date',
                'booking_date',
                'booking_time',
                'quantity',
                'room_id',
                'table_id',
                'menu_id',
                'note',
                'status',
            ]));

            Log::info('Booking created', ['booking_id' => $booking->id]);

            return response()->json([
                'message' => 'Booking created successfully',
                'booking_id' => $booking->id,
                'data' => $booking,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create booking', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Lưu booking thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update booking request', array_merge($request->all(), ['id' => $id]));

        $booking = Booking::findOrFail($id);

        // Same validation rules as store
        $rules = [
            'type' => 'required|in:room,table',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|regex:/^\d{10,11}$/',
            'quantity' => 'required|integer|min:1',
            'status' => 'nullable|in:pending,confirmed,cancelled',
            'menu_id' => 'nullable|exists:menus,menu_id',
            'note' => 'nullable|string',
        ];

        if ($request->type === 'room') {
            Log::info('Validating room booking update');
            $rules['check_in_date'] = 'required|date';
            $rules['check_out_date'] = 'required|date|after:check_in_date';
            $rules['room_id'] = 'required|exists:rooms,room_id';
            $rules['table_id'] = 'nullable';
            $rules['booking_date'] = 'nullable';
            $rules['booking_time'] = 'nullable';
        } elseif ($request->type === 'table') {
            Log::info('Validating table booking update');
            $rules['booking_date'] = 'required|date';
            $rules['booking_time'] = 'required';
            $rules['table_id'] = 'required|exists:tables,table_id';
            $rules['room_id'] = 'nullable';
            $rules['check_in_date'] = 'nullable';
            $rules['check_out_date'] = 'nullable';
        } else {
            Log::error('Invalid booking type', ['type' => $request->type]);
            return response()->json(['message' => 'Loại booking không hợp lệ'], 422);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Update the booking
            $booking->update($request->only([
                'type',
                'customer_name',
                'customer_phone',
                'check_in_date',
                'check_out_date',
                'booking_date',
                'booking_time',
                'quantity',
                'room_id',
                'table_id',
                'menu_id',
                'note',
                'status',
            ]));

            Log::info('Booking updated', ['booking_id' => $booking->id]);

            return response()->json([
                'message' => 'Booking updated successfully',
                'booking_id' => $booking->id,
                'data' => $booking,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update booking', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Lưu booking thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }
}
