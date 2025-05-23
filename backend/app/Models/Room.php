<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Room extends Model
    {
        protected $primaryKey = 'room_id'; // Khóa chính là room_id

        protected $fillable = [
            'room_name',
            'room_type',
            'capacity',
            'price',
            'status',
            'description',
        ];
    }

?>
