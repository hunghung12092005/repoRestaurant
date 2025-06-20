<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $primaryKey = 'room_id';
    public $incrementing = true;

    protected $fillable = [
        'room_name',
        'type_id',
        'floor_number',
        'status',
        'description'
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'type_id', 'type_id');
    }
}