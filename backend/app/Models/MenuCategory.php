<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    use HasFactory;

    protected $table = 'menu_categories';

    protected $primaryKey = 'category_id';

    public $incrementing = true;

    protected $fillable = [
        'category_name',
        'description',
        'created_at',
        'updated_at',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'category_id', 'category_id');
    }
}