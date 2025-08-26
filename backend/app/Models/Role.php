<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;


class Role extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ['name', 'display_name'];

    /**
     * HÀM NÀY BẮT BUỘC PHẢI CÓ VÀ ĐÚNG NHƯ THẾ NÀY
     *
     * Định nghĩa mối quan hệ nhiều-nhiều: Một vai trò (Role) có thể có nhiều quyền (Permission).
     * Laravel sẽ tự động tìm bảng trung gian `permission_role`.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Mối quan hệ một-nhiều: Một vai trò có thể thuộc về nhiều người dùng.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
?>