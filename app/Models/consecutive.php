<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class consecutive extends Model
{
    use HasFactory;

    protected $fillable = ['type','num_start','num_current'];

    protected $connection;

    public function construct($con)
    {
        $this->connection = $con;
    }  
}
