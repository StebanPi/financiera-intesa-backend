<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EgresoProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'cedula',
        'nombre',
        'direccion',
        'telefono'
    ];

    public function receipts()
    {
        return $this->hasMany(EgresoReceipt::class, 'proveedor_id');
    }
}
