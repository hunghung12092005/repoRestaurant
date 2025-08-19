<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
        protected $table = 'hotel_coupons';

protected $fillable = [
        'code', 'description', 'discount_amount',
        'usage_limit', 'used_count', 'is_active',
        'user_usage',
        'expires_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

}
    

