<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingHotelService extends Model
{
    use HasFactory;

    protected $table = 'booking_hotel_service';

    protected $fillable = [
        'booking_detail_id',
        'service_id',
    ];

    public function bookingDetail()
    {
        return $this->belongsTo(BookingHotelDetail::class, 'booking_detail_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function serviceInfo()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }
    public function room()
{
    return $this->belongsTo(Room::class, 'room_id', 'room_id');
}

}
