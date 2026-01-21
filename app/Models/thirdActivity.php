<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\thirdEntry;

class thirdActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre'
    ];

    public function thirdEntries()
    {
        return $this->hasMany(thirdEntry::class, 'id');
    }
}
