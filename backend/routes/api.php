<?php

use App\Http\Controllers\AmenityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\MenuItemController;
use App\Http\Controllers\api\ShopOnlineController;
use App\Http\Controllers\api\EmployeeController;
use App\Http\Controllers\api\DepartmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TableTypeController;

Route::apiResource('employees', EmployeeController::class);
Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/seasonal-pricing/current/{typeId}', [RoomController::class, 'getCurrentPricing']);

Route::get('/protected', [ApiLoginController::class, 'someProtectedRoute']);
Route::post('/login', [ApiLoginController::class, 'login']);
Route::post('/register', [ApiLoginController::class, 'register']);

Route::get('/menu-categories', [MenuCategoryController::class, 'index']);
Route::post('/menu-categories', [MenuCategoryController::class, 'store']);
Route::put('/menu-categories/{id}', [MenuCategoryController::class, 'update']);
Route::delete('/menu-categories/{id}', [MenuCategoryController::class, 'destroy']);

Route::get('/menus', [MenuController::class, 'index']);
Route::post('/menus', [MenuController::class, 'store']);
Route::put('/menus/{id}', [MenuController::class, 'update']);
Route::delete('/menus/{id}', [MenuController::class, 'destroy']);

Route::prefix('amenities')->group(function () {
    Route::get('/', [AmenityController::class, 'index']);
    Route::post('/', [AmenityController::class, 'store']);
    Route::put('/{amenity_id}', [AmenityController::class, 'update']);
    Route::delete('/{amenity_id}', [AmenityController::class, 'destroy']);
});

Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::post('/', [ServiceController::class, 'store']);
    Route::put('/{service_id}', [ServiceController::class, 'update']);
    Route::delete('/{service_id}', [ServiceController::class, 'destroy']);
});

Route::prefix('room-types')->group(function () {
    Route::get('/', [RoomTypeController::class, 'index']);
    Route::post('/', [RoomTypeController::class, 'store']);
    Route::put('/{id}', [RoomTypeController::class, 'update']);
    Route::delete('/{id}', [RoomTypeController::class, 'destroy']);
});

Route::prefix('pricese')->group(function () {
    Route::get('/', [PriceController::class, 'index']);
    Route::post('/', [PriceController::class, 'store']);
    Route::put('/{id}', [PriceController::class, 'update']);
    Route::delete('/{id}', [PriceController::class, 'destroy']);
});

Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms', [RoomController::class, 'store']);
Route::put('/rooms/{id}', [RoomController::class, 'update']);
Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);

Route::get('/table-types', [TableTypeController::class, 'index']);
Route::post('/table-types', [TableTypeController::class, 'store']);
Route::put('/table-types/{id}', [TableTypeController::class, 'update']);
Route::delete('/table-types/{id}', [TableTypeController::class, 'destroy']);

Route::get('/tables', [TableController::class, 'index']);
Route::post('/tables', [TableController::class, 'store']);
Route::put('/tables/{id}', [TableController::class, 'update']);
Route::delete('/tables/{id}', [TableController::class, 'destroy']);

Route::get('/bookings/form-data', [BookingController::class, 'getFormData']);
Route::get('/bookings', [BookingController::class, 'index']);
Route::get('/bookings/{booking_id}', [BookingController::class, 'show']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::put('/bookings/{booking_id}', [BookingController::class, 'update']);
Route::patch('/bookings/{booking_id}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
Route::delete('/bookings/{booking_id}', [BookingController::class, 'destroy']);
Route::post('/bookings/check-availability', [BookingController::class, 'checkAvailability']);
//
Route::post('/qr-login', [ApiLoginController::class, 'qrLogin']); // Thêm dòng này
Route::get('/menu-items', [MenuItemController::class, 'index']);
Route::post('/menu-items/array/{ids}', [MenuItemController::class, 'getItemArray']);

Route::get('/items-online', [ShopOnlineController::class, 'index']);
Route::get('/items-online/{id}', [ShopOnlineController::class, 'show']);
Route::get('/items-online/50k', [ShopOnlineController::class, 'getItem50k']);
//quên mk gửi mail
Route::post('/send-otp', [ApiLoginController::class, 'sendOtp']);
Route::post('/verify-otp', [ApiLoginController::class, 'verifyOtp']);
Route::post('/reset-password', [ApiLoginController::class, 'resetPassword']);

