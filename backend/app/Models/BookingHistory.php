<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHistory extends Model
{
    protected $table = 'booking_room_status';
    protected $primaryKey = 'status_id';
    public $timestamps = true;

    protected $fillable = [
        'room_id',
        'customer_id',
        'booking_id',
        'booking_detail_id',
        'check_in',
        'check_out',
        'room_price',
        'service_price',
        'surcharge',
        'surcharge_reason',
        'total_paid',
    ];

    // Quan hệ với Room
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    // Quan hệ với Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    // Quan hệ với BookingHotel
    public function booking()
    {
        return $this->belongsTo(BookingHotel::class, 'booking_id', 'booking_id');
    }

    // Quan hệ với BookingHotelDetail
    public function bookingDetail()
    {
        return $this->belongsTo(BookingHotelDetail::class, 'booking_detail_id', 'booking_detail_id');
    }
}