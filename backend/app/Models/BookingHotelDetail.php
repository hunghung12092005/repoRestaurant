<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHotelDetail extends Model
{
    protected $table = 'booking_hotel_detail';
    protected $primaryKey = 'booking_detail_id';
    protected $fillable = [
        'booking_id',
        'room_type',
        'gia_phong',
        'gia_dich_vu',
        'total_price',
        'room_id',
        'note',
        'status',
        'trang_thai',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type', 'type_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function booking()
    {
        return $this->belongsTo(BookingHotel::class, 'booking_id', 'booking_id');
    }

    public function services()
    {
        return $this->hasMany(BookingHotelService::class, 'booking_detail_id', 'booking_detail_id');
    }
}