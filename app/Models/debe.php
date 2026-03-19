<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\ThirdReceipts;

class debe extends Model
{
    use HasFactory;

    protected $fillable = [
        'cuenta',
        'nombre',
        'sede',
    ];
    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }
    public function ThirdReceipts(){
        return $this->hasMany(ThirdReceipts::class, 'id');
    }
    
    /**
     * Obtener cuentas debe únicas por cuenta y nombre, evitando duplicados
     */
    public static function getUnique()
    {
        $ids = static::select(DB::raw('MIN(id) as id'))
            ->groupBy('cuenta', 'nombre')
            ->pluck('id');
        return static::whereIn('id', $ids)
            ->orderBy('id')
            ->get();
    }
}
