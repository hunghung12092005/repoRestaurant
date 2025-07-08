<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Model implements JWTSubject
{
    // Các thuộc tính và phương thức khác của mô hình
protected $primaryKey = 'customer_id'; // Thay thế bằng khóa chính thực tế
 protected $fillable = [
        'customer_email',
        'customer_name',
        'customer_phone',
        'address',
        // Thêm các thuộc tính khác nếu cần
    ];
    // Phương thức cần thiết cho JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Trả về ID của khách hàng
    }

    public function getJWTCustomClaims()
    {
        return []; // Có thể trả về các claims tùy chỉnh nếu cần
    }
}
?>