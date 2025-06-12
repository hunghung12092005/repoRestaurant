<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsComment extends Model
{
    use HasFactory;

    protected $table = 'news_comments';
    protected $primaryKey = 'comment_id';
    public $timestamps = false;
    
    protected $fillable = [
        'news_id',
        'user_id',
        'comment_text',
        'comment_date',
        'status',
    ];

    protected $casts = [
        'comment_date' => 'datetime',
    ];

    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}