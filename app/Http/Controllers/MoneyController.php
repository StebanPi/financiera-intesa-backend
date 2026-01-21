<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Array_;
use Illuminate\Support\Str;
use App\Http\Controllers\TableChangeController;

class MoneyController extends Controller
{

    public static function datas($Arrayd,$Campos){
        for ($i=0; $i < count($Campos); $i++) { 
            $value = $Campos[$i];
            $Arrayd->$value = self::main($Arrayd->$value);
        }
        return $Arrayd;
    }

    public static function main($value){
        if($value === null || $value === ''){
            return '0';
        }
        return Str::replace(',','.',strval(number_format(floatval($value))));
    }
}
