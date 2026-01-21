<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordPrivileges extends Model
{
    use HasFactory;

    protected $fillable = [
        'password'
    ];
    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }  
}
