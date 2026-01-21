<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\thirdActivity;
use App\Models\ThirdReceipts;

class thirdEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'cedula',
        'nombre', 
        'direccion',
        'telefono',
        'actividad',
        'mas'
    ];

    public function thirdActivity()
    {
        return $this->belongsTo(thirdActivity::class, 'actividad');
    }

    public function thirdEntriesReceipts()
    {
        return $this->hasMany(thirdEntryReceipt::class, 'id');
    }
    public function ThirdReceipts(){
        return $this->hasMany(ThirdReceipts::class, 'id');
    }
    

}
