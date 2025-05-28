<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopOnline extends Model
{
    protected $table = 'itemsshoponline'; 
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'weight',
    ];
}
