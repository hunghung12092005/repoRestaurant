<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\BookingHotel;

class CustomerController extends Controller
{
    /**
     * Lấy danh sách khách hàng có phân trang và tìm kiếm.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('customer_name', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_phone', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_email', 'like', "%{$searchTerm}%")
                  ->orWhere('address', 'like', "%{$searchTerm}%")
                  ->orWhere('customer_id_number', 'like', "%{$searchTerm}%");
            });
        }

        $query->orderBy('created_at', 'desc');
        $customers = $query->paginate(10);

        return response()->json($customers);
    }

    /**
     * Lấy lịch sử đặt phòng của một khách hàng cụ thể.
     */
    public function getBookings($customerId)
    {
        $bookings = BookingHotel::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['data' => $bookings]);
    }
}