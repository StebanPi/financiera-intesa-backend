<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'name',
        'active',
        'sede',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
