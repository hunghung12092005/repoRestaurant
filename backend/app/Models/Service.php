<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Service extends Model
{
    use HasFactory, Auditable;

    protected $table = 'services';
    protected $primaryKey = 'service_id';
    public $incrementing = true;

    protected $fillable = [
        'service_name',
        'price',
        'description',
    ];

    /**
     * Mối quan hệ nhiều-nhiều với RoomTypes
     */
    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'room_type_services', 'service_id', 'type_id')
                    ->withTimestamps();
    }

    /**
     * Mối quan hệ một-nhiều với BookingHotelService
     */
    public function bookings()
    {
        return $this->hasMany(BookingHotelService::class, 'service_id', 'service_id');
    }
}