<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'name',
        'document',
        'phone',
        'email',
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
