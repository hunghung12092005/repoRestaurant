<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $primaryKey = 'room_id';
    protected $fillable = ['room_name', 'type_id', 'floor_number', 'status', 'maintenance_status', 'amenities', 'services', 'description'];
    protected $casts = ['amenities' => 'array', 'services' => 'array'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'type_id', 'type_id');
    }
}