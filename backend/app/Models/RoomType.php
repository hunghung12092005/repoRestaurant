<?php 
    

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class RoomType extends Model
{
    use HasFactory, Auditable;

    protected $table = 'room_types';
    protected $primaryKey = 'type_id';
    public $incrementing = true;

    protected $fillable = [
        'type_name',
        'description',
        'bed_count',
        'max_occupancy',
        'max_occupancy_child',
        'images',
        'rate',         // Thêm dòng này
        'm2',
    ];

    /**
     * Mối quan hệ nhiều-nhiều với Amenities
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_type_amenities', 'type_id', 'amenity_id')
                    ->withTimestamps();
    }

    /**
     * Mối quan hệ nhiều-nhiều với Services
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'room_type_services', 'type_id', 'service_id')
                    ->withTimestamps();
    }

    /**
     * Mối quan hệ một-nhiều với Rooms
     */
    public function rooms()
    {
        return $this->hasMany(Room::class, 'type_id', 'type_id');
    }

    /**
     * Mối quan hệ một-nhiều với Prices
     */
    public function prices()
    {
        return $this->hasMany(Price::class, 'type_id', 'type_id');
    }

    public function bookingDetail()
    {
        return $this->belongsTo(BookingHotelDetail::class, 'booking_detail_id', 'booking_detail_id');
    }
}
?>