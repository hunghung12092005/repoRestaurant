<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Đảm bảo import model User
use App\Models\Role; // Import model Role
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
        try {
            $googleUser = Socialite::driver('google')->user();

            // SỬA ĐỔI: Sử dụng firstOrNew để không ghi đè dữ liệu ngay lập tức
            $authUser = User::firstOrNew(
                ['email' => $googleUser->getEmail()]
            );

            // Kiểm tra xem đây có phải là người dùng mới không
            if (!$authUser->exists) {
                // Nếu là người dùng mới, điền các thông tin cần thiết
                $clientRole = Role::where('name', 'client')->firstOrFail();
                $authUser->name = $googleUser->getName();
                $authUser->google_id = $googleUser->getId();
                $authUser->password = Hash::make(Str::random(24));
                $authUser->role_id = $clientRole->id; // Chỉ gán vai trò client cho người dùng MỚI
                $authUser->email_verified_at = now();
            } else {
                // Nếu là người dùng cũ, chỉ cần cập nhật google_id nếu nó chưa có
                if (!$authUser->google_id) {
                    $authUser->google_id = $googleUser->getId();
                }
            }

            // Lưu lại những thay đổi (nếu có)
            $authUser->save();

            // Tải lại thông tin user với vai trò và quyền hạn
            $authUser->load('role.permissions');

            // Đăng nhập người dùng vào hệ thống
            Auth::login($authUser, true);

            // Tạo token JWT
            $token = JWTAuth::fromUser($authUser);

            // Chuyển hướng về trang chính, kèm theo token và user
            $frontendUrl = env('FRONTEND_URL', 'http://127.0.0.1:5173');
            return redirect($frontendUrl . '/?token=' . $token . '&user=' . urlencode(json_encode($authUser)));

        } catch (\Exception $e) {
            // Xử lý lỗi, có thể chuyển hướng về trang login với thông báo lỗi
            return redirect('/login')->with('error', 'Đăng nhập bằng Google thất bại. Vui lòng thử lại.');
        }
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
   
}
