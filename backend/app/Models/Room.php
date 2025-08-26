<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Room extends Model
{
    use Auditable;

    protected $primaryKey = 'room_id';
    public $incrementing = true;

    protected $fillable = [
        'room_name',
        'type_id',
        'floor_number',
        'description',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'type_id', 'type_id');
    }
    public function bookings()
    {
        return $this->hasMany(BookingHotelDetail::class, 'room_id', 'room_id');
    }
}