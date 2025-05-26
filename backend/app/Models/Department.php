<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $primaryKey = 'department_id'; // Chỉ định khóa chính là department_id

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
?>