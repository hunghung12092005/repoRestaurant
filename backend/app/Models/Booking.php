<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $primaryKey = 'booking_id';
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'booking_type',
        'booking_date',
        'check_in_date',
        'check_out_date',
        'booking_time',
        'quantity',
        'room_id',
        'table_id',
        'menu_id',
        'note',
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
