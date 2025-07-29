<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ PHP
        Carbon::setLocale('vi'); // Nếu muốn Carbon trả về tháng/ngày theo tiếng Việt
    }
}
