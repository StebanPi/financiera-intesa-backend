<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class historyPurse extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id_purse',
        'fecha_pago',
        'estado',
        'cuota',
        'abonado',
        'comentario'
    ];

    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }  
}
