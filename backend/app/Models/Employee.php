<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'employee_id'; // Chỉ định khóa chính là employee_id

    protected $fillable = [
        'name', 'gender', 'birth_date', 'email', 'phone', 'address',
        'position', 'salary', 'department_id', 'hire_date', 'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
?>