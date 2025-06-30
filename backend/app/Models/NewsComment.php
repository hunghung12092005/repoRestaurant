<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsComment extends Model
{
    use HasFactory;
    
    // Đổi tên model để khớp với bảng `comments`
    protected $table = 'comments';
    
    // Laravel sẽ tự quản lý created_at và updated_at nếu có trong bảng. 
    // Bảng của bạn chỉ có created_at, nên ta cần chỉ định.
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'news_id',
        'content',
        'is_visible', // Thêm trường này để quản lý trạng thái hiển thị của bình luận
    ];

    protected $casts = [
        'is_visible' => 'boolean', // Đảm bảo luôn là true/false
    ];

    /**
     * Lấy người dùng đã bình luận.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Lấy tin tức được bình luận.
     */
    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}