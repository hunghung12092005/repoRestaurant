<?php
// File: app/Http/Controllers/api/AuthController.php (Ví dụ)

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Đảm bảo đã import User model

class AuthController extends Controller
{
    // ... các phương thức login, register...

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = Auth::user();
        // THÊM MỚI: Gắn danh sách quyền vào đối tượng user trước khi trả về
        $user->abilities = $user->getAbilities();
        return response()->json($user);
    }

    // ...
}