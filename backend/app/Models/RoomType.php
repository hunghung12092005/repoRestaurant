<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'room_types';

    protected $primaryKey = 'type_id';

    public $incrementing = true;

    protected $fillable = [
        'type_name',
        'description',
        'created_at',
        'updated_at',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'type_id', 'type_id');
    }
}
