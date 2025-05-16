<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;


// Định nghĩa route cho login trước
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::post('handlelogin', [LoginController::class, 'login'])->name('login.login');
Route::post('handleregister', [LoginController::class, 'register'])->name('login.register');
// Route cho đăng nhập Google
Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');

// Route cho callback từ Google
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');
//Call back fb
Route::post('login/facebook/callback', [LoginController::class, 'handleFacebookCallback']);

//middleware để vào admin
Route::group(['middleware' => AdminMiddleware::class], function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});


// Route wildcard cuối cùng (tương tự 404)
Route::get('/{any}', function () {
    return view('welcome'); // Hoặc view chính của bạn
})->where('any', '.*');