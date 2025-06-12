<?php
    namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'booking_id';
    public $timestamps = true;
    protected $fillable = [
        'booking_type', 'customer_name', 'customer_phone', 'check_in_date', 'check_out_date',
        'booking_date', 'booking_time', 'quantity', 'room_id', 'table_id', 'menu_id', 'status', 'note'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'table_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }
}
?>
