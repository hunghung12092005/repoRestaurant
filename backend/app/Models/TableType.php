<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableType extends Model
{
    use HasFactory;

    protected $table = 'table_types';

    protected $primaryKey = 'type_id';

    public $incrementing = true;

    protected $fillable = [
        'type_name',
        'description',
        'created_at',
        'updated_at',
    ];

    public function tables()
    {
        return $this->hasMany(Table::class, 'type_id', 'type_id');
    }
}
