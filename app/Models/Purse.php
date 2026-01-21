<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purse extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_cost',
        'fecha_pago',
        'estado',
        'cuota',
        'abonado',
        'comentario'
    ];

    protected $connection;

    public function cost()
    {
        return $this->belongsTo(\App\Models\Cost::class, 'id_cost');
    }

    public function construct($con)
    {
        $this->connection = $con;
    }  
}
