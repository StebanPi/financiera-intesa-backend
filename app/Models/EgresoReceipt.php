<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EgresoProvider;
use App\Models\EgresoConcept;
use App\Models\debe;
use App\Models\haber;
use App\Models\elaborado;

class EgresoReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_recibo',
        'fecha_recibo',
        'proveedor_id',
        'forma',
        'concepto',
        'descripcion',
        'valor',
        'elaborado_por',
        'debe',
        'haber',
        'sede'
    ];

    protected function casts(): array
    {
        return [
            'fecha_recibo' => 'date',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(EgresoProvider::class, 'proveedor_id');
    }

    public function debeObject()
    {
        return $this->belongsTo(debe::class, 'debe');
    }

    public function haberObject()
    {
        return $this->belongsTo(haber::class, 'haber');
    }

    public function elaboradoObject()
    {
        return $this->belongsTo(elaborado::class, 'elaborado_por');
    }

    public function conceptoObject()
    {
        return $this->belongsTo(EgresoConcept::class, 'concepto');
    }
}
