<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Model implements JWTSubject
{
    protected $table = 'customers'; 
    protected $primaryKey = 'customer_id'; 
    protected $fillable = [     
            'customer_name',
            'customer_phone',
            'customer_email',
            'address',
            'customer_id_number',
            'room_id'
        ];
        public function getJWTIdentifier()
        {
            return $this->getKey(); 
        }

        public function getJWTCustomClaims()
        {
            return []; 
        }
}
?>
