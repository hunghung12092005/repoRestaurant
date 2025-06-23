<?php 
   namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        // Lấy người dùng đã xác thực
        $user = Auth::user();

        // Kiểm tra xem người dùng có phải là admin không
        if (!$user || $user->role !== 'admin') {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập.');
        }
       

        // Nếu có quyền admin, tiếp tục với request
        return $next($request);
    }
} 
?>