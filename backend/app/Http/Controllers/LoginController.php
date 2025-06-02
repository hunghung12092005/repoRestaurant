<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Đảm bảo import model User
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

//use Kreait\Firebase\Auth as FirebaseAuth; // Import đúng lớp
class LoginController extends Controller
{
    // protected $auth;

    // public function __construct(FirebaseAuth $auth)
    // {
    //     $this->auth = $auth;
    // }
    // Hiển thị trang đăng nhập
    public function showLoginForm()
    {
        return view('auth.login'); // Đường dẫn tới view trang đăng nhập
    }

    // Xử lý yêu cầu đăng nhập
    // public function login(Request $request)
    // {
    //     // Xác thực dữ liệu nhập vào
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|min:6',
    //     ]);

    //     // Kiểm tra thông tin xác thực và lấy token
    //     if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
    //         return redirect()->back()->withErrors(['userFind' => 'Thông tin đăng nhập không chính xác.']);
    //     }

    //     // Lấy thông tin người dùng dựa trên email
    //     $user = User::where('email', $request->email)->first();

    //     // Chuyển hướng đến view với token và thông tin người dùng
    //     return redirect('/')->with([
    //         'token' => $token,
    //         'user' => $user,
    //     ]);
    // }


    // Hiển thị trang đăng ký
    public function showRegistrationForm()
    {
        return view('auth.register'); // Đường dẫn tới view trang đăng ký
    }

    // Xử lý yêu cầu đăng ký
    // public function register(Request $request)
    // {
    //     // dd(1);
    //     // Xác thực dữ liệu nhập vào
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6|confirmed',
    //     ]);
    //     $role = 'client';
    //     // Tạo người dùng mới
    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'role' => $role,
    //         'password' => Hash::make($request->password), // Mã hóa mật khẩu
    //     ]);

    //     // Đăng nhập người dùng sau khi đăng ký
    //     Auth::login($user);

    //     return redirect()->route('login')->with('success', 'Đăng ký thành công! Bạn đã được đăng nhập.');
    // }



    // Đăng xuất
    public function logout()
    {
        Auth::logout(); // Đăng xuất người dùng
        // Xóa tất cả dữ liệu trong session
        session()->flush(); // Thay vì session_destroy()
        return redirect('/')->with('success', 'Bạn đã đăng xuất thành công.');
    }
    //login Google
    public function redirectToGoogle()
    {
        //composer require laravel/socialite
        //nhớ cài lại config/services
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
{
    $user = Socialite::driver('google')->user();

    $authUser = User::where('email', $user->email)->first();
    $role = 'client';

    if (!$authUser) {
        // Nếu chưa có người dùng, tạo mới
        $authUser = User::create([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'google_id' => $user->id,
            'password' => bcrypt(rand(16, 20)), // Tạo mật khẩu ngẫu nhiên
        ]);
    }

    // Đăng nhập người dùng
    Auth::login($authUser, true);

    // Tạo token JWT
    $token = JWTAuth::fromUser($authUser);

    // Chuyển hướng về trang chính, kèm theo token và user
    return redirect('http://127.0.0.1:5173/?token=' . $token . '&user=' . urlencode(json_encode($authUser)));
}

    // public function handleFacebookCallback(Request $request)
    // {
    //     $socialTokenId = $request->input('social-login-tokenId');

    //     try {
    //         // Xác thực token
    //         $verifiedIdToken = $this->auth->verifyIdToken($socialTokenId);

    //         // Kiểm tra claims
    //         $claims = $verifiedIdToken->claims();
    //         $email = $claims->get('email');
    //         $userId = $claims->get('sub');

    //         // Kiểm tra người dùng đã tồn tại chưa
    //         $user = User::where('email', $email)->first();

    //         if (!$user) {
    //             // Nếu không tồn tại, tạo mới người dùng
    //             $user = new User();
    //             $user->name = $claims->get('name');
    //             $user->email = $email;
    //             $user->firebase_uid = $userId; // Lưu UID của Firebase
    //             $user->password = bcrypt(Str::random(8));
    //             $user->save();
    //         }

    //         // Đăng nhập người dùng
    //         Auth::login($user);

    //         // Redirect đến trang mà bạn muốn
    //         return redirect()->intended('/');
    //     } catch (\Exception $e) {
    //         return redirect()->route('login')->withErrors(['msg' => 'Error verifying token.']);
    //     }
    // }
    public function showForgotPasswordForm()
    {
        return view('auth.forgotpassword');
    }
    public function showFormAccount()
    {
        return view('auth.formaccount');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        //$email = $request->email;
        if (!$user) {
            return back()->withErrors(['email' => 'Không tìm thấy tài khoản với email này.']);
        }

        // Tạo mã OTP
        $otp = rand(100000, 999999);
        $user->password_reset_token = $otp;
        $user->save();

        // Gửi email chứa mã OTP
        Mail::send('email.otp', ['otp' => $otp], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Mã OTP để đặt lại mật khẩu');
        });

        // return redirect()->route('otp.verify')->with([
        //     'status' => 'Mã OTP đã được gửi đến email của bạn.',
        //     'email' => $request->email 
        // ]);
        // Trong Controller
        $data = [
            'status' => 'Mã OTP đã được gửi đến email của bạn.',
            'email' => $request->email
        ];

        return view('auth.verification_form', $data);
    }

    public function showVerifyOtpForm()
    {
        return view('auth.verification_form');
    }

    public function verifyOtp(Request $request)
    {
        // Xác thực mã OTP
        $request->validate(['otp' => 'required', 'email' => 'required|email']);

        // Kiểm tra người dùng dựa trên email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Nếu không tìm thấy người dùng
            return back()->withErrors(['email' => 'Không tìm thấy tài khoản với email này.']);
        }

        // // Kiểm tra mã OTP
        // if ($request->otp !== $user->password_reset_token) {
        //     return response()->json(['success' => true, 'token' => $token, 'email' => $request->email]);

        //     // return redirect()->route('otp.verify')->withErrors(['failOtp' => 'Mã OTP không chính xác.']);
        //    // return back()->withErrors(['failOtp' => 'Mã OTP không chính xác.']);
        // }
        if ($request->otp !== $user->password_reset_token) {
            //dd(1);
            return response()->json([
                'success' => false,
                'message' => 'Mã OTP không chính xác.'
            ]);
        }

        // Nếu mã OTP đúng
        // Tạo token reset mật khẩu
        $token = $request->otp; // Hoặc sử dụng bất kỳ phương thức nào để tạo token

        // Lưu token vào cơ sở dữ liệu hoặc session
        $user->password_reset_token = $token;
        $user->save();

        // Trả về phản hồi JSON với token và email
        return response()->json(['success' => true, 'token' => $token, 'email' => $request->email]);
    }

    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password')->with(['token' => $token, 'email' => $request->email]);
    }

    public function handleResetPassword(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // Tìm người dùng theo email
        $user = User::where('email', $request->email)->first();

        // Kiểm tra nếu người dùng tồn tại
        if ($user) {
            // Cập nhật mật khẩu mới
            $user->password = bcrypt($request->new_password);
            $user->password_reset_token = null; // Đặt lại token
            $user->save();

            // Trả về phản hồi JSON thành công
            return response()->json(['success' => true, 'message' => 'Đặt lại mật khẩu thành công!']);
        }

        // Trả về lỗi nếu không tìm thấy tài khoản
        return response()->json(['success' => false, 'error' => 'Không tìm thấy tài khoản.'], 400);
    }
}
