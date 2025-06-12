<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $primaryKey = 'room_id';
    protected $fillable = [
        'room_name', 'type_id', 'capacity', 'status', 'season_id',
        'amenities', 'description'
    ];

    protected $casts = [
        'amenities' => 'array',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'type_id', 'type_id');
    }

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id', 'season_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_id', 'room_id');
    }
}
