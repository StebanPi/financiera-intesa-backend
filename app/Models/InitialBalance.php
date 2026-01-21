<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InitialBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'base_efectivo',
        'base_banco',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'base_efectivo' => 'decimal:2',
            'base_banco' => 'decimal:2',
        ];
    }

    /**
     * Usuario que creó la base inicial
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el único registro activo (singleton)
     */
    public static function getActive(): ?self
    {
        return self::first();
    }

    /**
     * Verifica si existe una base inicial
     */
    public static function hasInitialBalance(): bool
    {
        return self::count() > 0;
    }
}
