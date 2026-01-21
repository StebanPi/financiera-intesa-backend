<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_cost',
        'concepto',
        'descripcion',
        'no_recibo',
        'fecha_recibo',
        'valor',
        'elaborado_por',
        'debe',
        'haber',
        'forma'
    ];

    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }  
}
