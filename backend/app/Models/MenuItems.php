<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItems extends Model
{
    use HasFactory;
    protected $table = 'menuitems';
    protected $fillable = ['name', 'category_id'];

    public function category()
    {
        return $this->belongsTo(CategoryMenu::class);
    }
    public function images()
    {
        return $this->hasMany(Image::class, 'menuitem_id'); // Giả sử bảng hình ảnh có cột item_id
    }
}
