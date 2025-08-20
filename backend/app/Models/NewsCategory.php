<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class NewsCategory extends Model
{
    use HasFactory, Auditable;

    protected $table = 'news_categories';
    public $timestamps = false; // Bảng này không có created_at, updated_at

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Lấy tất cả các tin tức thuộc về danh mục này.
     */
    public function news()
    {
        return $this->hasMany(News::class, 'category_id');
    }
}