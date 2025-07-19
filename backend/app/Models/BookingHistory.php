<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHistory extends Model
{
    protected $table = 'booking_room_status';
    protected $primaryKey = 'status_id';
    public $timestamps = true; // Nếu bảng có cột created_at, updated_at

    protected $fillable = [
        'room_id',
        'customer_id',
        'check_in_date',
        'check_out_date',
        'actual_check_out_time',
        'pricing_type',
        'status',
        'total_price',
        'surcharge',
        'surcharge_reason'
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
}