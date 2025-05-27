<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

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
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|regex:/^\d{10,11}$/', // Đồng bộ với frontend
                'booking_date' => 'required|date',
                'booking_time' => 'required|date_format:H:i', // Validate định dạng giờ (HH:mm)
                'quantity' => 'required|integer|min:1',
                'room_id' => 'nullable|exists:rooms,room_id',
                'table_id' => 'nullable|exists:tables,table_id',
                'menu_id' => 'nullable|exists:menus,menu_id',
                'note' => 'nullable|string',
                'booking_type' => 'nullable|string',
            ]);

            $booking = Booking::create($validated);
            $booking->load(['room', 'table', 'menu']);
            return response()->json(['data' => $booking], 201);
        }

        public function update(Request $request, $id)
        {
            $booking = Booking::where('booking_id', $id)->firstOrFail();
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|regex:/^\d{10,11}$/', // Đồng bộ với frontend
                'booking_date' => 'required|date',
                'booking_time' => 'required|date_format:H:i', // Validate định dạng giờ (HH:mm)
                'quantity' => 'required|integer|min:1',
                'room_id' => 'nullable|exists:rooms,room_id',
                'table_id' => 'nullable|exists:tables,table_id',
                'menu_id' => 'nullable|exists:menus,menu_id',
                'note' => 'nullable|string',
                'booking_type' => 'nullable|string',
            ]);

            $booking->update($validated);
            $booking->load(['room', 'table', 'menu']);
            return response()->json(['data' => $booking]);
        }
    public function destroy($id)
    {
        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->delete();
        return response()->json(null, 204);
    }
}
