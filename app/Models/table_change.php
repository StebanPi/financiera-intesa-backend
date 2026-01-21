<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class table_change extends Model
{
    use HasFactory;

    protected $fillable = [
        'table',
        'id_change',
        'add',
        'edit',
        'delete',
        'is_synchronized',
        'latitude',
        'longitude'
    ];

    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }  
}
