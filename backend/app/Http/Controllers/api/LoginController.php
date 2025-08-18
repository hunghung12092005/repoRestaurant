<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Endroid\QrCode\QrCode; // Thêm dòng này để sử dụng thư viện QR Code
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
        public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'turnstileResponse' => 'required|string',
        ]);

        $secretKey = '0x4AAAAAABhcDUvzGTo0A_uUgpVVQMk8wIc';
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $request->turnstileResponse,
        ]);

        if (!$response->json('success')) {
            return response()->json(['error' => 'Turnstile verification failed.'], 400);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Sai tai khoan hoac mat khau'], 401);
        }

        // CÁCH VIẾT LẠI AN TOÀN HƠN
        /** @var \App\Models\User $user */
        $user = auth('api')->user(); // Lấy user đã được xác thực
        
        // Tải các quan hệ cần thiết vào user object hiện tại
        $user->load('role.permissions');

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Đã tồn tại email.'], 409);
        }

        // <-- SỬA ĐỔI: Gán trực tiếp role_id = 2
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => 2, // Gán vai trò 'client' cố định
            'password' => bcrypt($request->password),
            'qr_code' => null,
        ]);
        // <-- KẾT THÚC SỬA ĐỔI

        $token = JWTAuth::fromUser($user);
        
        $user->load('role.permissions');

        return response()->json([
            'message' => 'Đăng ký thành công',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function someProtectedRoute(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $user->load('role.permissions');
            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function qrLogin(Request $request)
    {
        if (!$request->has('token')) {
            return response()->json(['error' => 'Token không được cung cấp.'], 400);
        }
        $token = $request->input('token');
        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if ($user) {
                $user->load('role.permissions');
                $jwtToken = JWTAuth::fromUser($user);
                return response()->json([
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'token' => $jwtToken,
                    'user' => $user, // <-- SỬA ĐỔI: Trả về toàn bộ user object
                ]);
            } else {
                return response()->json(['error' => 'Người dùng không tồn tại.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token không hợp lệ.'], 401);
        }
    }
    
    //quên mk
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản.'], 404);
        }

        // Tạo và lưu mã OTP
        $otp = rand(100000, 999999);
        $user->password_reset_token = $otp;
        $user->save();

        // Gửi email chứa mã OTP
        Mail::send('email.otp', ['otp' => $otp], function ($message) use ($user) {
            $message->to($user->email)->subject('Mã OTP để đặt lại mật khẩu');
        });

        return response()->json(['success' => true, 'message' => 'Mã OTP đã được gửi.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required', 'email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user || $request->otp !== $user->password_reset_token) {
            return response()->json(['success' => false, 'message' => 'Mã OTP không chính xác.'], 400);
        }

        return response()->json(['success' => true, 'message' => 'Mã OTP xác thực thành công.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->password = bcrypt($request->new_password);
            $user->password_reset_token = null; // Đặt lại token
            $user->save();

            return response()->json(['success' => true, 'message' => 'Đặt lại mật khẩu thành công!']);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản.'], 400);
    }
    public function changePassword(Request $request)
    {
        // Lấy ID từ token JWT
        $sub = JWTAuth::parseToken()->getPayload()->get('sub');

        // Tìm người dùng theo ID
        $user = User::find($sub);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Kiểm tra dữ liệu đầu vào
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
    public function updateProfile(Request $request)
    {
        // Lấy ID từ token JWT
        $sub = JWTAuth::parseToken()->getPayload()->get('sub');

        // Tìm người dùng theo ID
        $user = User::find($sub);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Kiểm tra dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            // Thêm các trường cần thiết khác
        ]);

        // Cập nhật thông tin hồ sơ
        $user->name = $request->name;
        $user->email = $request->email;
        // Cập nhật thêm các trường khác nếu cần

        $user->save();

        return response()->json(['message' => 'Profile updated successfully'], 200);
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
