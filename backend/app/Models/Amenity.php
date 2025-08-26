<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Amenity extends Model
{
    use HasFactory, Auditable;

    protected $table = 'amenities';
    protected $primaryKey = 'amenity_id';
    public $incrementing = true;

    protected $fillable = [
        'amenity_name',
        'description',
    ];

    /**
     * Mối quan hệ nhiều-nhiều với RoomTypes
     */
    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'room_type_amenities', 'amenity_id', 'type_id')
                    ->withTimestamps();
    }
}