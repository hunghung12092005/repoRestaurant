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

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'qr_code',
        'turnstileResponse',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Implement methods from JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'abilities' => $this->getAbilities(),
        ];
    }

    // =================================================================
    // CÁC PHƯƠNG THỨC HỖ TRỢ PHÂN QUYỀN (ĐẢM BẢO CÓ PHẦN NÀY)
    // =================================================================

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Kiểm tra xem người dùng có quyền hạn cụ thể không.
     * @param string $ability
     * @return bool
     */
    public function hasAbility(string $ability): bool
    {
        $permissionsByRole = config('permissions.roles', []);
        $userPermissions = $permissionsByRole[$this->role] ?? [];

        if (in_array('*', $userPermissions, true)) {
            return true;
        }

        return in_array($ability, $userPermissions, true);
    }

    /**
     * Lấy tất cả các quyền của người dùng.
     * @return array
     */
    public function getAbilities(): array
    {
        $permissionsByRole = config('permissions.roles', []);
        $userPermissions = $permissionsByRole[$this->role] ?? [];

        if (in_array('*', $userPermissions, true)) {
            return config('permissions.abilities', []);
        }

        return $userPermissions;
    }

} // <--- ĐẢM BẢO TẤT CẢ CODE NẰM TRÊN DẤU NGOẶC NÀY