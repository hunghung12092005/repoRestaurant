<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

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
}