<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHotel extends Model
{
    protected $table = 'booking_hotel';
    protected $primaryKey = 'booking_id';
    protected $fillable = [
        'customer_id',
        'payment_method',
        'booking_type',
        'check_in_date',
        'check_out_date',
        'total_rooms',
        'total_price',
        'payment_status',
        'status',
        'note',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function details()
    {
        return $this->hasMany(BookingHotelDetail::class, 'booking_id', 'booking_id');
    }

     public function roomTypeInfo()
    {
        // 'room_type' là tên cột khóa ngoại trong bảng booking_hotel
        // 'id' là tên cột khóa chính trong bảng room_types
        return $this->belongsTo(RoomType::class, 'room_type', 'type_id');
    }
}