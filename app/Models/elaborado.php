<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\ThirdReceipts;

class elaborado extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'estado'
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
     * Obtener elaborados únicos por nombre, evitando duplicados
     */
    public static function getUnique()
    {
        $ids = static::select(DB::raw('MIN(id) as id'))
            ->groupBy('nombre')
            ->pluck('id');
        return static::whereIn('id', $ids)
            ->where('estado', '1')
            ->orderBy('id')
            ->get();
    }
    
    /**
     * Obtener todos los elaborados únicos (incluyendo inactivos)
     */
    public static function getAllUnique()
    {
        $ids = static::select(DB::raw('MIN(id) as id'))
            ->groupBy('nombre')
            ->pluck('id');
        return static::whereIn('id', $ids)
            ->orderBy('id')
            ->get();
    }
}
