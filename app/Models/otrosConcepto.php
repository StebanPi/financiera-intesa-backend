<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class otrosConcepto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'estado',
        'debe',
        'haber'
    ];

    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }  
    
}
