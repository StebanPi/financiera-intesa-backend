<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_cost',
        'cod_alumno',
        'concepto',
        'descripcion',
        'no_recibo',
        'fecha_recibo',
        'valor',
        'elaborado_por',
        'debe',
        'haber',
        'forma',
        'sede'
    ];

    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }

    public function conceptoObj()
    {
        return $this->belongsTo('App\Models\otrosConcepto', 'concepto');
    }

    public function elaboradoObj()
    {
        return $this->belongsTo('App\Models\elaborado', 'elaborado_por');
    }

    public function cost()
    {
        return $this->belongsTo('App\Models\Cost', 'id_cost');
    }
}
