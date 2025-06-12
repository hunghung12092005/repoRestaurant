<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'room_types';
    protected $primaryKey = 'type_id';
    protected $fillable = ['type_name', 'description'];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'type_id', 'type_id');
    }
}
