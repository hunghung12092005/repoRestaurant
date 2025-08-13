<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    protected $table = 'staff_schedules';
    protected $primaryKey = 'id';
    protected $fillable = 
    [
        'user_id', 
        'shift_date', 
        'start_time', 
        'end_time', 
        'task'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}