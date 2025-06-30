<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHotel extends Model
{
    protected $table = 'booking_hotel';
    protected $primaryKey = 'booking_id'; // Sử dụng cột booking_id làm khóa chính
    protected $fillable = [
        'customer_id',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function details()
    {
        return $this->hasMany(BookingHotelDetail::class, 'booking_id', 'booking_id');
    }

    public function services()
    {
        return $this->hasManyThrough(
            BookingHotelService::class,
            BookingHotelDetail::class,
            'booking_id',
            'booking_detail_id',
            'booking_id',
            'booking_detail_id'
        );
    }
}
