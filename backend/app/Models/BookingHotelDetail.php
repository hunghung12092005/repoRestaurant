<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BookingHotelDetail extends Model
{
    use HasFactory;
    
    protected $table = 'booking_hotel_detail';
    protected $primaryKey = 'booking_detail_id';
    public $incrementing = true;
    public $timestamps = true;

    protected $casts = [
        'booking_id' => 'integer',
        'room_id' => 'integer',
        'gia_phong' => 'decimal:2',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'booking_id',
        'room_id',
        'room_type',
        'gia_dich_vu',
        'gia_phong',
        'total_price',
        'note',
    ];

    public function booking()
    {
        return $this->belongsTo(BookingHotel::class, 'booking_id', 'booking_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function services()
    {
        return $this->hasMany(BookingHotelService::class, 'booking_detail_id', 'booking_detail_id');
    }
}
