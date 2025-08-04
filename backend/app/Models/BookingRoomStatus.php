<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRoomStatus extends Model
{
    protected $table = 'booking_room_status';
    protected $primaryKey = 'status_id';
    public $timestamps = true;

    protected $fillable = [
        'customer_id',
        'booking_id',
        'booking_detail_id',
        'room_id',
        'check_in',
        'check_out',
        'room_price',
        'service_price',
        'surcharge',
        'total_paid',
        'surcharge_reason',
    ];

    public function booking()
    {
        return $this->belongsTo(BookingHotel::class, 'booking_id', 'booking_id');
    }

    public function bookingDetail()
    {
        return $this->belongsTo(BookingHotelDetail::class, 'booking_detail_id', 'booking_detail_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}