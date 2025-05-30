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

    protected $dates = ['birth_date', 'hire_date'];

    // Định dạng ngày tháng khi serialize thành JSON
    protected $casts = [
        'birth_date' => 'date:d/m/Y',
        'hire_date' => 'date:d/m/Y',
    ];
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
?>