<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class News extends Model
{
    use HasFactory, Auditable;

    protected $table = 'news';
    public $timestamps = false; // Bảng này không có created_at, updated_at (chỉ có publish_date)

    protected $fillable = [
        'title',
        'summary',
        'content',
        'thumbnail',
        'publish_date',
        'author_id',
        'category_id',
        'status',
        'views',
        'tags',
        'is_pinned',
    ];

    protected $casts = [
        'publish_date' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    /**
     * Lấy thông tin tác giả (User).
     */
    public function author()
    {
        // Giả sử bạn có model User
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Lấy thông tin danh mục.
     */
    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    /**
     * Lấy tất cả bình luận của tin tức.
     */
    public function comments()
    {
        return $this->hasMany(NewsComment::class, 'news_id');
    }
}