<?php
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\MenuItemController;
use App\Http\Controllers\api\ShopOnlineController;
use App\Http\Controllers\api\EmployeeController;
use App\Http\Controllers\api\DepartmentController;
use App\Http\Controllers\api\NewsController;
use App\Http\Controllers\api\NewsCategoryController;
use App\Http\Controllers\api\NewsCommentController;
use Illuminate\Support\Facades\Route;

// Resource routes
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('news', NewsController::class);
Route::apiResource('news-categories', NewsCategoryController::class);

// Custom routes
Route::get('/news-comments', [NewsCommentController::class, 'index']); // Lấy tất cả bình luận
Route::get('/news/{newsId}/comments', [NewsCommentController::class, 'commentsByNewsId']); // Lấy bình luận theo news_id
Route::post('/news/{newsId}/comments', [NewsCommentController::class, 'store']);
Route::put('/comments/{id}', [NewsCommentController::class, 'update']);
Route::delete('/comments/{id}', [NewsCommentController::class, 'destroy']);

// Auth routes
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