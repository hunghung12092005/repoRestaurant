<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\MenuItemController;
use App\Http\Controllers\UploadController;

Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');

// Route cho callback từ Google
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');
//Call back fb
Route::post('login/facebook/callback', [LoginController::class, 'handleFacebookCallback']);

//middleware để vào admin
Route::group(['middleware' => AdminMiddleware::class], function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});
// gửi ảnh
Route::get('/upload', [UploadController::class, 'create']);
Route::post('/upload', [UploadController::class, 'store']);
// Route cho API
// Route::get('/api/protected', [ApiLoginController::class, 'someProtectedRoute']);
// Route::post('/api/login', [ApiLoginController::class, 'login']);
// Route::post('/api/register', [ApiLoginController::class, 'register']);
// Route::get('/api/menu-items', [MenuItemController::class, 'index']);
// Route wildcard cuối cùng (tương tự 404)
    Route::get('/', function () {
        return view('welcome'); // Hoặc view chính của bạn
    });
    // Route::get('/{any}', function () {
    //     return view('welcome'); // Hoặc view chính của bạn
    // })->where('any', '.*');
