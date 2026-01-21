<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;

    protected $fillable = [
        'cod_alumno',
        'valor_semestre',
        'numero_semestre',
        'valor_total_semestre',
        'descuento',
        'valor_neto',
        'saldo_financiar',
        'periodo',
        'numero_cuotas',
        'valor_cuotas',
        'fecha_pago',
        'detalles'
    ];
    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }  
}
