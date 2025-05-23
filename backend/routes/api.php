<?php
use App\Http\Controllers\api\LoginController as ApiLoginController;
use App\Http\Controllers\api\MenuItemController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;

Route::get('/protected', [ApiLoginController::class, 'someProtectedRoute']);

Route::post('/login', [ApiLoginController::class, 'login']);
Route::post('/register', [ApiLoginController::class, 'register']);
Route::get('/menu-items', [MenuItemController::class, 'index']);
