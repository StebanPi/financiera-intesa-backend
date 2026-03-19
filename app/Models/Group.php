<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
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
