<?php
// app/Models/AuditLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $guarded = []; // Cho phép mass assignment

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Quan hệ để lấy thông tin user đã thực hiện hành động
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ đa hình để lấy đối tượng bị tác động (Room, Booking, ...)
    public function auditable()
    {
        return $this->morphTo();
    }
}