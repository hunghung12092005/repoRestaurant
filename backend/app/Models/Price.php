<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Price extends Model
    {
        protected $table = 'prices';
        protected $primaryKey = 'price_id';
        protected $fillable = [
            'type_id',
            'start_date',
            'end_date',
            'description',
            'price_per_night',
            'hourly_price'
        ];

        public function roomType()
        {
            return $this->belongsTo(RoomType::class, 'type_id', 'type_id');
        }
    }
?>
