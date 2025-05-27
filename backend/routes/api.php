<?php
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\MenuItemController;
use App\Http\Controllers\api\ShopOnlineController;
use App\Http\Controllers\api\EmployeeController;
use App\Http\Controllers\api\DepartmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Models\ShopOnline;

Route::apiResource('employees', EmployeeController::class);
Route::apiResource('departments', DepartmentController::class);


Route::get('/protected', [ApiLoginController::class, 'someProtectedRoute']);
Route::post('/login', [ApiLoginController::class, 'login']);
Route::post('/register', [ApiLoginController::class, 'register']);
Route::get('/menu-items', [MenuItemController::class, 'index']);
//bán hàng online
Route::get('/items-online', [ShopOnlineController::class, 'index']);
Route::get('/items-online/{id}', [ShopOnlineController::class, 'show']); 
Route::get('/menu-items', [MenuItemController::class, 'index']);
Route::get('api/items-online/50k', [ShopOnlineController::class, 'getIteam50k']);
