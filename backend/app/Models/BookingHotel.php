<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class BookingHotel extends Model
{
    use Auditable;
    protected $table = 'booking_hotel';
    protected $primaryKey = 'booking_id';
    protected $fillable = [
        'customer_id',
        'user_id',
        'type_id',
        'payment_method',
        'orderCode',
        'booking_type',
        'adult',
        'child',
        'pricing_type',
        'check_in_date',
        'check_out_date',
        'check_in_time',
        'check_out_time',
        'total_rooms',
        'total_price',
        'additional_fee',
        'payment_status',
        'status',
        'idDiscount',
        'note'
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
        return $this->belongsTo(RoomType::class, 'type_id', 'type_id');
    }

    public function historyRecords()
    {
        return $this->hasMany(BookingHistory::class, 'booking_id', 'booking_id');
    }
}