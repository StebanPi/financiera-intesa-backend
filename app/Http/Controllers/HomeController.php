<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        // Si el usuario tiene rol "contador", redirigir a contabilidad
        if (Auth::check() && Auth::user()->hasRole('contador')) {
            return redirect()->route('accounting.index');
        }

        return view('home');
    }
}
