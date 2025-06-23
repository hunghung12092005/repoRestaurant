<?php
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\MenuItemController;
use App\Http\Controllers\api\ShopOnlineController;
use App\Http\Controllers\api\UsersController;
use App\Http\Controllers\api\OccupancyController;
use App\Http\Controllers\api\NewsController;
use App\Http\Controllers\api\NewsCategoryController;
use App\Http\Controllers\api\NewsCommentController;
use Illuminate\Support\Facades\Route;

// Quản lý người dùng, tin tức, danh mục tin tức
Route::apiResource('users', UsersController::class);
Route::apiResource('news', NewsController::class);
Route::apiResource('news-categories', NewsCategoryController::class);

// Các route quản lý khác
Route::get('/occupancy/rooms', [OccupancyController::class, 'index']);
Route::get('/news-comments', [NewsCommentController::class, 'index']); // Trang admin lấy tất cả bình luận
Route::put('/comments/{id}', [NewsCommentController::class, 'update']); // Admin cập nhật bình luận
Route::delete('/comments/{id}', [NewsCommentController::class, 'destroy']);

// Route test token
Route::get('/protected', [ApiLoginController::class, 'someProtectedRoute']);
Route::post('/login', [ApiLoginController::class, 'login']);
Route::post('/register', [ApiLoginController::class, 'register']);
Route::post('/qr-login', [ApiLoginController::class, 'qrLogin']);

// Menu and shop routes
Route::get('/menu-items', [MenuItemController::class, 'index']);
Route::get('/items-online', [ShopOnlineController::class, 'index']);
Route::get('/items-online/{id}', [ShopOnlineController::class, 'show']);
Route::get('/items-online/50k', [ShopOnlineController::class, 'getIteam50k']);
Route::post('/menu-itemsarray/{ids}', [MenuItemController::class, 'getItemArray']);