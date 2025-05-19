<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        //dd(1);
        // Xác thực dữ liệu nhập vào
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Kiểm tra thông tin xác thực và lấy token
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Sai pass rồi bạn ơi'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // Lấy thông tin người dùng
        $user = User::where('email', $request->email)->first();

        return response()->json([
            'message' => 'Login successful',  // Thông báo thành công
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }
    public function register(Request $request)
    {
        // Xác thực dữ liệu nhập vào
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Kiểm tra xem email đã tồn tại hay chưa
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Email already exists.'], 409); // 409 Conflict
        }

        $role = 'client';

        // Tạo người dùng mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
            'password' => bcrypt($request->password), // Mã hóa mật khẩu
        ]);

        // Tạo token cho người dùng vừa đăng ký
        $token = JWTAuth::fromUser($user);

        // Trả về token và thông tin người dùng
        return response()->json([
            'message' => 'Đăng ký thành công',
            'token' => $token,
            'user' => $user,
        ], 201);
    }
    //lấy token về
    public function someProtectedRoute(Request $request)
    {
        try {
            // Xác thực token và lấy thông tin người dùng
            $user = JWTAuth::parseToken()->authenticate();

            // Xử lý yêu cầu với thông tin người dùng
            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            // Token không hợp lệ hoặc hết hạn
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
