<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
class StaffProfile extends Model
{
    use Auditable;
    protected $table = 'staff_profiles';
    protected $primaryKey = 'id';
    protected $fillable = 
    [
        'user_id', 
        'employee_code',
        'phone', 
        'address', 
        'hire_date', 
        'position', 
        'department', 
        'salary', 
        'level'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}