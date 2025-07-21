<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelBooking extends Model
{
    use HasFactory;

    protected $table = 'CancelBooking';
    protected $primaryKey = 'cancel_id'; // 🛠 Bắt buộc khai báo nếu không phải "id"
    protected $fillable = [
    'cancel_id',
    'booking_id', // Xóa khoảng trắng ở đây
    'customer_id',
    'cancellation_reason',
    'cancellation_date',
    'refund_amount',
    'status',
     'refund_bank',
    'refund_account_number',
    'refund_account_name'
];
}
