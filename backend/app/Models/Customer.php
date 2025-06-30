<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    public $incrementing = true;
    public $timestamps = true;

    protected $casts = [
        'customer_id' => 'integer',
        'room_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_email',
        'address',
        'room_id',
    ];

    public function bookings()
    {
        return $this->hasMany(BookingHotel::class, 'customer_id', 'customer_id');
    }
}