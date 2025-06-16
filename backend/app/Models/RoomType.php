<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class RoomType extends Model
    {
        protected $table = 'room_types';
        protected $primaryKey = 'id';
        protected $fillable = ['type_name', 'description', 'bed_count', 'max_occupancy'];

        public function seasonalPricing()
        {
            return $this->hasMany(SeasonalPricing::class, 'type_id', 'id');
        }
    }