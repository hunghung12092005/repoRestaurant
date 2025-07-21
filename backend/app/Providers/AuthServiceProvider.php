<?php
// File: app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Định nghĩa các quyền có thể có trong hệ thống.
        // Bạn có thể thêm bất kỳ quyền nào bạn muốn ở đây.
        $permissions = [
            'manage_news',       // Quản lý tin tức (viết, sửa, xóa)
            'manage_contacts',   // Quản lý liên hệ
            // Thêm các quyền khác nếu cần, ví dụ:
            // 'manage_bookings',
            // 'view_reports',
        ];

        // Tự động đăng ký Gate cho mỗi quyền
        foreach ($permissions as $permission) {
            Gate::define($permission, function (User $user) use ($permission) {
                return $user->hasPermissionTo($permission);
            });
        }

        // Gate đặc biệt cho admin, luôn trả về true
        Gate::before(function (User $user, $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });
    }
}