<?php 
   namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
       // Kiểm tra giá trị của session 'admin'
       $admin = session('admin');

       // Nếu session 'admin' không tồn tại hoặc không đúng, chuyển hướng
       if (empty($admin) || $admin !== 'admin') {
           return redirect('/')->with('error', 'Bạn không có quyền truy cập.');
       }

       // Nếu có session 'admin', tiếp tục với request
       return $next($request);

}
} 
?>