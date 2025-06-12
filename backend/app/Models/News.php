<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    protected $primaryKey = 'news_id';
    public $timestamps = false;

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

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(NewsComment::class, 'news_id');
    }
}