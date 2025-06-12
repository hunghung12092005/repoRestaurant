<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $table = 'seasons';
    protected $primaryKey = 'season_id';
    protected $fillable = [
        'season_name', 'start_date', 'end_date', 'hourly_rate',
        'nightly_rate', 'daily_rate', 'discount', 'description'
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'season_id', 'season_id');
    }
}
