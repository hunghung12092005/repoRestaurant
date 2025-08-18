<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'customer_id',
        'room_rating',
        'service_rating',
        'star_rating',
        'staff_rating',
        'comment',
    ];
    public function customer()
{
    return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
}

}

