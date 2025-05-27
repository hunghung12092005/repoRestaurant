<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\MenuItemController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TableController;

Route::get('/protected', [ApiLoginController::class, 'someProtectedRoute']);

Route::post('/login', [ApiLoginController::class, 'login']);
Route::post('/register', [ApiLoginController::class, 'register']);
Route::get('/menus', [MenuController::class, 'index']);

Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms', [RoomController::class, 'store']);
Route::put('/rooms/{id}', [RoomController::class, 'update']);
Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);

Route::get('/tables', [TableController::class, 'index']);
Route::post('/tables', [TableController::class, 'store']);
Route::put('/tables/{id}', [TableController::class, 'update']);
Route::delete('/tables/{id}', [TableController::class, 'destroy']);

Route::get('/bookings', [BookingController::class, 'index']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/{id}', [BookingController::class, 'show']);
Route::put('/bookings/{id}', [BookingController::class, 'update']);
Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
