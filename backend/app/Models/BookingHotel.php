<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingHotel extends Model
{
    use HasFactory;

    protected $table = 'booking_hotel';
    protected $primaryKey = 'booking_id'; // Sử dụng cột booking_id làm khóa chính
    protected $fillable = [
        'customer_id',
         'room_type',
        'payment_method',
        'booking_type',
        'pricing_type',
        'check_in_date',
        'check_out_date',
        'total_rooms',
        'total_price',
        'additional_fee',
        'payment_status',
        'status',
        'note',
    ];

    public function details()
    {
        return $this->hasMany(BookingHotelDetail::class, 'booking_id');
    }
     public function roomTypeInfo()
    {
        // 'room_type' là tên cột khóa ngoại trong bảng booking_hotel
        // 'id' là tên cột khóa chính trong bảng room_types
        return $this->belongsTo(RoomType::class, 'room_type', 'type_id');
    }
}