<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingHotelDetail extends Model
{
    use HasFactory;
 
    protected $table = 'booking_hotel_detail';

    protected $fillable = [
        'booking_id',
        'room_id',
        'total_price',
        'note',
    ];

    public function booking()
    {
        return $this->belongsTo(BookingHotel::class, 'booking_id');
    }

    public function services()
    {
        return $this->hasMany(BookingHotelService::class, 'booking_detail_id');
    }
}