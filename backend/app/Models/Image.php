<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $table = 'images';
    //protected $fillable = ['name', 'category_id'];
    protected $fillable = ['url', 'alt_text', 'menuitem_id'];
    public function menuItem()
    {
        return $this->belongsTo(MenuItems::class, 'menuitem_id'); // Khóa ngoại item_id
    }
}
