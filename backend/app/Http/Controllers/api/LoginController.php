<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Endroid\QrCode\QrCode; // Thêm dòng này để sử dụng thư viện QR Code
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Xác thực dữ liệu nhập vào
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'turnstileResponse' => 'required|string', // Thêm trường Turnstile
        ]);

        // Xác thực Turnstile
        $secretKey = '0x4AAAAAABhcDUvzGTo0A_uUgpVVQMk8wIc';
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secretKey,
            'response' => $request->turnstileResponse,
        ]);

        $result = json_decode($response->body());

        if (!$result->success) {
            return response()->json(['error' => 'Turnstile verification failed.'], 400);
        }

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
            'message' => 'Login successful',
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
            return response()->json(['error' => 'Đã tồn tại email.'], 409); // 409 Conflict
        }

        $role = 'client';

        // Tạo người dùng mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
            'password' => bcrypt($request->password), // Mã hóa mật khẩu
            'qr_code' => null, // Đặt giá trị mặc định là null
        ]);

        // Tạo token cho người dùng vừa đăng ký
        $token = JWTAuth::fromUser($user);
        // Tạo mã QR
        $qrCode = new QrCode('http://127.0.0.1:8000/qr-login?token=' . $token);

        // Tạo writer để xuất mã QR
        $writer = new PngWriter();
        $dataUri = $writer->write($qrCode)->getDataUri();
        // Lưu mã QR vào cơ sở dữ liệu
        $user->qr_code = $dataUri; // Lưu mã QR
        $user->save();
        // Trả về thông tin người dùng và mã QR
        return response()->json([
            'message' => 'Đăng ký thành công',
            'token' => $token,
            'user' => $user,
            'qr_code' => $dataUri, // Trả về mã QR dưới dạng URI
        ], 201);
    }
    //login bằng mã QR
    public function qrLogin(Request $request)
    {
        // Kiểm tra xem có token trong yêu cầu
        if (!$request->has('token')) {
            return response()->json(['error' => 'Token không được cung cấp.'], 400);
        }

        $token = $request->input('token');

        try {
            // Xác thực token và lấy thông tin người dùng
            $user = JWTAuth::setToken($token)->authenticate();

            if ($user) {
                // Token hợp lệ, trả về thông tin người dùng và token mới
                $jwtToken = JWTAuth::fromUser($user);
                return response()->json([
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'token' => $jwtToken,
                    'user' => [
                        'id' => $user->id, // Lấy ID từ đối tượng người dùng
                        'name' => $user->name,
                        'role' => $user->role,
                    ],
                ]);
            } else {
                return response()->json(['error' => 'Người dùng không tồn tại.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token không hợp lệ.'], 401);
        }
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
