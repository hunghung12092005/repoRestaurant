<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $primaryKey = 'room_id';

    public $incrementing = true;

    protected $fillable = [
        'room_name',
        'type_id',
        'capacity',
        'price',
        'status',
        'description',
        'created_at',
        'updated_at',
    ];
    
    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'type_id', 'type_id');
    }
}
