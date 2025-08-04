<?php

use App\Http\Controllers\api\AmenityController;
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\OccupancyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\api\RoomController;
use App\Http\Controllers\api\RoomTypeController;
use App\Http\Controllers\api\PriceController;
use App\Http\Controllers\api\ServiceController;
use App\Http\Controllers\api\UsersController;
use App\Http\Controllers\api\NewsController;
use App\Http\Controllers\api\NewsCategoryController;
use App\Http\Controllers\api\NewsCommentController;
use App\Http\Controllers\api\BookingHotelController;
use App\Http\Controllers\api\AdminDashboardController;
use App\Http\Controllers\api\ContactController;
use App\Http\Controllers\api\BookingHistoryController;
use App\Http\Controllers\ChatAIController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\WebhookController;
use App\Models\BookingHistory;
use Illuminate\Support\Facades\Storage;

Route::get('/seasonal-pricing/current/{typeId}', [RoomController::class, 'getCurrentPricing']);

Route::get('/protected', [ApiLoginController::class, 'someProtectedRoute']);
Route::post('/login', [ApiLoginController::class, 'login']);
Route::post('/register', [ApiLoginController::class, 'register']);

Route::get('/occupancy/rooms', [OccupancyController::class, 'index']);
// Route::post('/customers', [OccupancyController::class, 'storeCustomer']); //thêm dữ liệu khách vào bảng customer
Route::get('/occupancy/by-date', [OccupancyController::class, 'getRoomsByDate']); //lọc theo ngày
Route::get('/rooms/{room_id}/customer', [OccupancyController::class, 'getCustomerByRoom']); //hiển thị thông tin khách
// Route::post('/rooms/{room_id}/checkout', [OccupancyController::class, 'checkoutRoom']); //checkout
Route::post('/booking-details/{booking_detail_id}/checkout', [OccupancyController::class, 'checkoutRoom']); //checkout theo booking detail
Route::get('/services', [OccupancyController::class, 'getServices']); //hiển thị dịch vụ
Route::post('/rooms/{room_id}/add-guest', [OccupancyController::class, 'addGuestToRoom']); //khi khách đặt phòng thì đổi trạng thái
Route::post('/rooms/preview-price', [OccupancyController::class, 'previewPrice']); //xem trước giá
Route::post('/rooms/{room_id}/extend', [OccupancyController::class, 'extendStay']); //gia hạn phòng
Route::post('/customers/{id}/update-name', [OccupancyController::class, 'updateCustomerName']);
Route::post('/bookings/{booking_id}/request-cancellation', [OccupancyController::class, 'requestCancellation']);
Route::post('/bookings/{booking_id}/confirm-cancellation', [OccupancyController::class, 'confirmCancellation']);
Route::get('/occupancy/future-bookings', [OccupancyController::class, 'getFutureBookings']);
Route::post('/occupancy/cancel-now', [OccupancyController::class, 'cancelNow']);

// Cập nhật thông tin khách hàng
Route::post('/contacts', [ContactController::class, 'store']);
Route::get('/admin/contacts', [ContactController::class, 'index']);
Route::delete('/admin/contacts/{contact}', [ContactController::class, 'destroy']);
Route::post('/admin/contacts/{contact}/reply', [ContactController::class, 'reply']);
Route::get('/admin/contacts/new', [ContactController::class, 'fetchNew']);

Route::apiResource('users', UsersController::class);
//news
Route::get('/news/pinned', [NewsController::class, 'getPinned']);
Route::apiResource('news', NewsController::class);
Route::apiResource('news-categories', NewsCategoryController::class);
// Comments
Route::get('/news/{newsId}/comments', [NewsCommentController::class, 'commentsByNewsId']);
Route::post('/news/{newsId}/comments', [NewsCommentController::class, 'store']);
Route::get('/news-comments', [NewsCommentController::class, 'index']);
Route::delete('/comments/{comment}', [NewsCommentController::class, 'destroy']);
Route::patch('/comments/{comment}/toggle-visibility', [NewsCommentController::class, 'toggleVisibility']);

Route::prefix('amenities')->group(function () {
    Route::get('/', [AmenityController::class, 'index']);
    Route::post('/', [AmenityController::class, 'store']);
    Route::put('/{amenity_id}', [AmenityController::class, 'update']);
    Route::delete('/{amenity_id}', [AmenityController::class, 'destroy']);
});

Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::get('/indexAllService', [ServiceController::class, 'indexAllService']);
    Route::post('/', [ServiceController::class, 'store']);
    Route::put('/{service_id}', [ServiceController::class, 'update']);
    Route::delete('/{service_id}', [ServiceController::class, 'destroy']);
});

Route::prefix('room-types')->group(function () {
    Route::get('/', [RoomTypeController::class, 'index']);
    Route::post('/', [RoomTypeController::class, 'store']);
    Route::put('/{id}', [RoomTypeController::class, 'update']);
    Route::delete('/{id}', [RoomTypeController::class, 'destroy']);
    Route::get('/{type_id}', [RoomTypeController::class, 'show']);
});

Route::prefix('prices')->group(function () {
    Route::get('/', [PriceController::class, 'index']);
    Route::post('/', [PriceController::class, 'store']);
    //lấy giá ra client dựa vào ngày
    Route::post('/prices_client', [PriceController::class, 'getPrice']);
    Route::post('/', [PriceController::class, 'store']);
    Route::put('/{id}', [PriceController::class, 'update']);
    Route::delete('/{id}', [PriceController::class, 'destroy']);
});

Route::get('/admin/dashboard/overview', [AdminDashboardController::class, 'getSystemOverview']);

Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms', [RoomController::class, 'store']);
Route::put('/rooms/{id}', [RoomController::class, 'update']);
Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);

Route::post('/bookings', [BookingHotelController::class, 'storeBooking']);
Route::get('/bookings', [BookingHotelController::class, 'getBookings']);
Route::get('/booking-details/{bookingId}', [BookingHotelController::class, 'getBookingDetails']);
Route::get('/booking-services/{bookingId}', [BookingHotelController::class, 'getBookingServices']);
Route::get('/available-rooms', [BookingHotelController::class, 'getAvailableRooms']);
Route::post('/assign-room/{bookingDetailId}', [BookingHotelController::class, 'assignRoom']);
Route::patch('/bookings/{bookingId}', [BookingHotelController::class, 'confirmBooking']);
Route::get('/booking-cancel/{booking_id}', [BookingHotelController::class, 'getCancelInfo']);
Route::patch('/booking-cancel/{cancel_id}', [BookingHotelController::class, 'confirmCancelBooking']);
Route::patch('/bookings/{bookingId}/complete', [BookingHotelController::class, 'completeBooking']);

Route::get('/booking-histories', [BookingHistoryController::class, 'index']);
Route::get('/booking-histories/{status_id}', [BookingHistoryController::class, 'show']);

Route::post('/qr-login', [ApiLoginController::class, 'qrLogin']); // Thêm dòng này

//quên mk gửi mail
Route::post('/send-otp', [ApiLoginController::class, 'sendOtp']);
Route::post('/verify-otp', [ApiLoginController::class, 'verifyOtp']);
Route::post('/reset-password', [ApiLoginController::class, 'resetPassword']);
Route::post('/change-password', [ApiLoginController::class, 'changePassword']);
Route::post('/update-password', [ApiLoginController::class, 'updateProfile']);
//thêm booking từ client
Route::post('/booking-client', [BookingHotelController::class, 'storeBooking']);
//tao token jwt cho khách hàng
Route::post('/generate-token', [BookingHotelController::class, 'generateToken']);
//tra ve lich su cho khach hang
Route::get('/booking-history', [BookingHotelController::class, 'getBookingHistory']);
Route::delete('/booking-history/{id}', [BookingHotelController::class, 'deleteBookingHistory']);
Route::delete('/booking-history/{id}', [BookingHotelController::class, 'deleteBookingHistory']);
Route::get('/cancel-booking/{booking}', [BookingHotelController::class, 'showBookingCancel']);
Route::post('/cancel-booking/{booking}/bank-info', [BookingHotelController::class, 'updateBankInfo']);
//lay hang phong dua vao so nguoi
Route::post('/available-rooms', [BookingHotelController::class, 'getAvailableRooms']);
//thanh toan payOS
Route::post('/payos/checkout', [BookingHotelController::class, 'payos']);

//check xem có phòng trống hay không
Route::get('/check-availability', [BookingHotelController::class, 'checkAvailability']);
//api chat ai
Route::get('/chat-ai/check-availability', [ChatAIController::class, 'checkAvailability']);
Route::get('/chat-ai/hotel-info', [ChatAIController::class, 'hotelInfo']);
Route::get('/hotel-infos', [ChatAIController::class, 'index']);
Route::post('/hotel-infos', [ChatAIController::class, 'store']);
Route::put('/hotel-infos/{id}', [ChatAIController::class, 'update']);
Route::delete('/hotel-infos/{id}', [ChatAIController::class, 'destroy']);
Route::get('/chat-ai/hotel-links', [ChatAIController::class, 'hotelLinks']);

//gui anh socket
Route::post('/upload', [FileController::class, 'upload']);
//api webhook
Route::get('/webhook-url', [WebhookController::class, 'handleWebhook']);
//giam gia
Route::post('/discount', [CouponsController::class, 'getDiscountAmount'])->middleware('throttle:100,60');
Route::prefix('discount-codes')->controller(CouponsController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});
