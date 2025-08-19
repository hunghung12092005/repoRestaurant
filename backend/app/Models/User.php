<?php 
// File: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Role;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable ;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     * ĐÃ SỬA LẠI CHO ĐÚNG VỚI DATABASE
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // <-- Sửa từ 'role'
        // Đã xóa 'permissions'
        'qr_code',
        'turnstileResponse',
        'status',
    ];

    /**
     * Các thuộc tính nên được ẩn khi serialize.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Các thuộc tính nên được cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        // Đã xóa 'permissions' => 'array'
    ];

    /**
     * Định nghĩa mối quan hệ: Một User thuộc về một Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Đính kèm thông tin role vào JWT Token.
     */
    public function getJWTCustomClaims()
    {
        $this->load('role');

        return [
            'role' => $this->role,
        ];
    }

    /**
     * Kiểm tra quyền của người dùng.
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        $this->loadMissing('role.permissions');

        if (!$this->role) return false;
        if ($this->role->name === 'admin') return true;

        return $this->role->permissions->contains('name', $permissionName);
    }


    public function notifications()
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class, 'user_id', 'id');
    }

}