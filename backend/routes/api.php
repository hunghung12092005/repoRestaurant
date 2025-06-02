<?php
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\MenuItemController;
use App\Http\Controllers\api\ShopOnlineController;
use App\Http\Controllers\api\EmployeeController;
use App\Http\Controllers\api\DepartmentController;
use App\Http\Controllers\api\LeaveRequestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Models\ShopOnline;

Route::apiResource('employees', EmployeeController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('leave-requests', LeaveRequestController::class); // Add leave-requests resource

// Custom Employee Route for Leave Requests
Route::get('/employees/{id}/leave-requests', [EmployeeController::class, 'getLeaveRequestsByEmployee']);


Route::get('/protected', [ApiLoginController::class, 'someProtectedRoute']);
Route::post('/login', [ApiLoginController::class, 'login']);
Route::post('/register', [ApiLoginController::class, 'register']);
Route::post('/qr-login', [ApiLoginController::class, 'qrLogin']); // Thêm dòng này
Route::get('/menu-items', [MenuItemController::class, 'index']);
//bán hàng online
Route::get('/items-online', [ShopOnlineController::class, 'index']);
Route::get('/items-online/{id}', [ShopOnlineController::class, 'show']); 
Route::get('/menu-items', [MenuItemController::class, 'index']);
Route::get('api/items-online/50k', [ShopOnlineController::class, 'getIteam50k']);
//menu items
Route::post('/menu-itemsarray/{ids}', [MenuItemController::class, 'getItemArray']);
