<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use HasFactory;

    protected $table = 'news_categories';
    protected $primaryKey = 'category_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    public function news()
    {
        return $this->hasMany(News::class, 'category_id');
    }
}