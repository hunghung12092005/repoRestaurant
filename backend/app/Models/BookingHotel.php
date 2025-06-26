<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingHotel extends Model
{
    use HasFactory;

    protected $table = 'booking_hotel';

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

    public function details()
    {
        return $this->hasMany(BookingHotelDetail::class, 'booking_id');
    }
}