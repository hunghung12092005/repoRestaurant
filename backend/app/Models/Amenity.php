<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $primaryKey = 'amenity_id';
    protected $fillable = ['amenity_name', 'description'];
}