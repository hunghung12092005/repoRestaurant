<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'employee_id';

    protected $fillable = [
        'full_name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'department_id',
        'start_date',
    ];

    protected $dates = ['birth_date', 'start_date'];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
        'start_date' => 'date:Y-m-d',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    
    public $timestamps = false;
}
