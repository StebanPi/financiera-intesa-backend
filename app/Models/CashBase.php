<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'base_efectivo',
        'base_banco',
        'sede'
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'base_efectivo' => 'decimal:2',
            'base_banco' => 'decimal:2',
        ];
    }
}
