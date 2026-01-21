<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EgresoConcept extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'state',
        'debe',
        'haber'
    ];

    /**
     * Relación con debe
     */
    public function debeObject()
    {
        return $this->belongsTo(\App\Models\debe::class, 'debe');
    }

    /**
     * Relación con haber
     */
    public function haberObject()
    {
        return $this->belongsTo(\App\Models\haber::class, 'haber');
    }
}
