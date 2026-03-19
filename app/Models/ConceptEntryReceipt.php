<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptEntryReceipt extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'state', 'debe', 'haber', 'sede'];

    /**
     * Relación con debe
     */
    public function debeObject()
    {
        return $this->belongsTo(debe::class, 'debe');
    }

    /**
     * Relación con haber
     */
    public function haberObject()
    {
        return $this->belongsTo(haber::class, 'haber');
    }
}
