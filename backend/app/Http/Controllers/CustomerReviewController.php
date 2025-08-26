<?php

namespace App\Http\Controllers;

use App\Models\BookingHotel;
use App\Models\Customer;
use App\Models\CustomerReview;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    public function index()
{
    $reviews = CustomerReview::with('customer')
        ->latest()
        ->paginate(10); // phân trang 10 bản ghi

    return response()->json($reviews);
}

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'booking_id'     => 'required|integer',
            //'customerPhone'  => 'required|string',
            'hotel_service'  => 'required|integer|min:1|max:10',
            'staff'          => 'required|integer|min:1|max:10',
            'room'           => 'required|integer|min:1|max:10',
            'stars'          => 'required|integer|min:1|max:5',
            'comment'        => 'nullable|string'
        ]);

        // Tìm customer theo phone
        // $customer = Customer::where('customer_phone', $request->customerPhone)->first();
        // // return[
        // //     'customer' => $customer
        // // ];
        // if (!$customer) {
        //     return response()->json([
        //         'message' => 'Khách hàng không tồn tại'
        //     ], 404);
        // }

        // Lưu review
        $review = CustomerReview::updateOrCreate(
            ['booking_id' => $request->booking_id], // điều kiện tìm
            [
                // 'customer_id'    => $customer->customer_id,
                'room_rating'    => $request->room,
                'service_rating' => $request->hotel_service,
                'staff_rating' => $request->staff,
                'star_rating'    => $request->stars,
                'comment'        => $request->comment
            ]
        );

        return response()->json([
            'message' => 'Đánh giá đã được lưu thành công',
            'data'    => $review
        ], 201);
    }
    public function getBookingDetail($id)
{
    try {
        $booking = BookingHotel::with(['roomTypeInfo', 'customer']) // load luôn customer
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $booking,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy thông tin booking.',
        ], 404);
    }
}


}
