<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\thirdEntry;
use App\Models\debe;
use App\Models\haber;
use App\Models\elaborado;
use App\Models\ConceptEntryReceipt;

class ThirdReceipts extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_recibo',
        'type',
        'third', 
        'concepto',
        'detalles',
        'valor',
        'debe',
        'haber',
        'elaborado_por',
        'fecha_recibo',
        'forma'
    ];

    public function thirdObject(){
        return $this->belongsTo(thirdEntry::class, 'third');
    }
    public function debeObject(){
        return $this->belongsTo(debe::class, 'debe');
    }
    public function haberObject(){
        return $this->belongsTo(haber::class, 'haber');
    }
    public function elaboradoObject(){
        return $this->belongsTo(elaborado::class, 'elaborado_por');
    }
    
    public function conceptoObject(){
        return $this->belongsTo(ConceptEntryReceipt::class, 'concepto');
    }
}
