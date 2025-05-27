<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $primaryKey = 'menu_id';
    protected $table = 'menus';
    protected $fillable = ['menu_name', 'category', 'price', 'description', 'status'];
}
