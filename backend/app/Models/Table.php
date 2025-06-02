<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $primaryKey = 'table_id';

    public $incrementing = true;

    protected $fillable = [
        'table_name',
        'type_id',
        'capacity',
        'status',
        'location',
        'description',
        'created_at',
        'updated_at',
    ];

    public function tableType()
    {
        return $this->belongsTo(TableType::class, 'type_id', 'type_id');
    }
}
