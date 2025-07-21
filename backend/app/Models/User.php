<?php 
// File: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions', // <-- THÊM DÒNG NÀY
        'qr_code',
        'turnstileResponse',
    ];

    /**
     * Các thuộc tính nên được ẩn khi serialize.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Các thuộc tính nên được cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array', // <-- THÊM DÒNG NÀY
    ];

    // Implement methods from JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // Trả về cả role và permissions trong token
        return [
            'role' => $this->role,
            'permissions' => $this->permissions ?? [],
        ];
    }

    /**
     * Kiểm tra xem người dùng có quyền cụ thể không.
     * Admin luôn có tất cả các quyền.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermissionTo(string $permission): bool
    {
        // Admin có tất cả các quyền
        if ($this->role === 'admin') {
            return true;
        }

        // Kiểm tra trong mảng permissions
        // Sử dụng ?? [] để tránh lỗi nếu permissions là null
        return in_array($permission, $this->permissions ?? []);
    }
}