<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $primaryKey = 'leave_id';

    protected $fillable = [
        'employee_id', 'leave_type', 'start_date', 'end_date', 'status', 'reason'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Tắt quản lý timestamp tự động
    public $timestamps = false;
}